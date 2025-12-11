<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UtilityController extends Controller
{
    protected $backupDir;

    public function __construct()
    {
        // Configure where your script writes backups.
        // You can override with DB_BACKUP_DIR in .env (absolute path).
        $this->backupDir = env('DB_BACKUP_DIR', storage_path('app/db_backups'));
    }

    public function index(Request $request)
    {
        // attempt to find latest backup to display on page load
        $latest = $this->getLatestBackupFile();

        // If we have a latest file, convert to the shape blade expects (name + url)
        $latestBackup = null;
        if ($latest) {
            $latestBackup = [
                'name' => $latest['name'],
                'url'  => route('utilities.downloadBackup', ['filename' => $latest['name']]),
            ];
        }

        return view('utility.index', [
            'latestBackup' => $latestBackup
        ]);
    }

    /**
     * Backup button: create a persistent DB dump in configured backupDir and show link.
     * Filename format: db_backup_YYYYmmdd_HHMMSS.sql
     */
    public function backup(Request $request)
    {
        try {
            // Ensure backups dir exists and is writable (use configured backupDir)
            $backupDir = rtrim($this->backupDir, DIRECTORY_SEPARATOR);
            if (! File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            // Build filename with today's date/time
            $filename = 'db_backup_' . date('Ymd_His') . '.sql';
            $fullPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

            // Get DB connection info from config (works for mysql/mariadb)
            $connection = config('database.default');
            $dbConfig = config("database.connections.{$connection}");

            $driver = $dbConfig['driver'] ?? null;

            if (in_array($driver, ['mysql', 'mysqli'])) {
                $dbHost = $dbConfig['host'] ?? '127.0.0.1';
                $dbPort = $dbConfig['port'] ?? '3306';
                $dbDatabase = $dbConfig['database'] ?? '';
                $dbUser = $dbConfig['username'] ?? '';
                $dbPass = $dbConfig['password'] ?? '';

                // mysqldump binary (adjust to full path if needed)
                $mysqldumpBin = 'mysqldump';

                $cmdParts = [];
                $cmdParts[] = escapeshellcmd($mysqldumpBin);
                $cmdParts[] = '--single-transaction';
                $cmdParts[] = '--quick';
                $cmdParts[] = '--lock-tables=false';
                if ($dbHost) $cmdParts[] = '--host=' . escapeshellarg($dbHost);
                if ($dbPort) $cmdParts[] = '--port=' . escapeshellarg($dbPort);
                if ($dbUser) $cmdParts[] = '--user=' . escapeshellarg($dbUser);
                if ($dbPass !== null && $dbPass !== '') {
                    $cmdParts[] = '--password=' . escapeshellarg($dbPass);
                }
                $cmdParts[] = escapeshellarg($dbDatabase);

                $cmd = implode(' ', $cmdParts) . ' > ' . escapeshellarg($fullPath) . ' 2>&1';

                try {
                    $process = Process::fromShellCommandline($cmd);
                    // allow up to 5 minutes for large DBs (adjust if necessary)
                    $process->setTimeout(300);
                    $process->run();

                    if ($process->isSuccessful() && File::exists($fullPath)) {
                        // success - provide download url using the download route
                        $downloadUrl = route('utilities.downloadBackup', ['filename' => $filename]);

                        // return the exact session key/shape your blade checks (session('backup_file'))
                        return redirect()->back()->with('backup_file', [
                            'name' => $filename,
                            'url'  => $downloadUrl,
                        ])->with('success', 'Backup created successfully.');
                    } else {
                        // log details and fall through to fallback
                        Log::error('mysqldump failed: ' . $process->getOutput() . ' ' . $process->getErrorOutput());
                    }
                } catch (\Throwable $ex) {
                    Log::error('mysqldump exception: ' . $ex->getMessage());
                }
            }

            // Fallback: attempt to find a recent temp file created by existing script and move it
            $tempDir = sys_get_temp_dir();
            $candidates = [];
            if (File::exists($tempDir) && File::isDirectory($tempDir)) {
                foreach (File::files($tempDir) as $f) {
                    $name = $f->getFilename();
                    if (stripos($name, 'db_backup') === 0 || preg_match('/^db_backup/i', $name)) {
                        $candidates[] = $f;
                    }
                }
            }

            if (!empty($candidates)) {
                usort($candidates, function ($a, $b) {
                    return filemtime($b->getPathname()) <=> filemtime($a->getPathname());
                });
                $found = $candidates[0];
                $src = $found->getPathname();

                try {
                    File::move($src, $fullPath);
                } catch (\Throwable $e) {
                    // move may fail due to permissions; try copy
                    try {
                        File::copy($src, $fullPath);
                    } catch (\Throwable $copyEx) {
                        Log::error('Failed to move or copy temp backup: ' . $copyEx->getMessage());
                    }
                }

                if (File::exists($fullPath)) {
                    $downloadUrl = route('utilities.downloadBackup', ['filename' => $filename]);
                    return redirect()->back()->with('backup_file', [
                        'name' => $filename,
                        'url'  => $downloadUrl,
                    ])->with('success', 'Backup file preserved from temp.');
                }
            }

            // nothing worked
            return redirect()->back()->with('latest_backup_message', 'Unable to create/preserve a backup. Check server logs and ensure mysqldump is installed or that your backup script writes to a persistent folder.');

        } catch (\Throwable $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from uploaded .sql file
     * (unchanged)
     */
    public function restore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sql_file' => 'required|file|mimes:sql,txt,sql',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            $file = $request->file('sql_file');
            $filename = 'restore_'.time().'_'.$file->getClientOriginalName();
            $storageDir = storage_path('app/temp');

            if (! File::exists($storageDir)) {
                File::makeDirectory($storageDir, 0755, true);
            }

            $moved = $file->move($storageDir, $filename);
            $fullPath = $moved->getPathname();

            if (! File::exists($fullPath)) {
                return redirect()->back()->with('error', 'Uploaded file could not be saved.');
            }

            $php = PHP_BINARY;
            $artisan = base_path('artisan');
            $artisanCmd = escapeshellarg($php) . ' ' . escapeshellarg($artisan) . ' db:restore ' . escapeshellarg($fullPath);

            $logPath = storage_path('logs/restore.log');
            $log = function($msg) use ($logPath) {
                File::append($logPath, '['.date('Y-m-d H:i:s').'] '.$msg."\n");
                Log::info($msg);
            };

            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

            if ($isWindows) {
                $bgCmd = 'start /B "" ' . $artisanCmd;
                $log("Attempting Windows background start: {$bgCmd}");
                $process = @popen($bgCmd, 'r');
                if ($process !== false) {
                    @pclose($process);
                    $log('Windows background process started via popen().');
                    return redirect()->back()->with('success', 'Restore has been queued. It will run in background.');
                } else {
                    $log('popen() failed — falling back to synchronous exec for debugging.');
                    exec($artisanCmd . ' 2>&1', $out, $ret);
                    $log("Sync fallback exit code: {$ret}");
                    $log("Sync fallback output: " . implode("\n", $out));
                    if ($ret === 0) {
                        return redirect()->back()->with('success', 'Restore completed (sync fallback).');
                    }
                    return redirect()->back()->with('error', 'Restore failed (check storage/logs/restore.log).');
                }
            } else {
                $bgCmd = 'nohup ' . $artisanCmd . ' > /dev/null 2>&1 &';
                $log("Attempting Linux background start: {$bgCmd}");
                exec($bgCmd, $out, $ret);
                $log("nohup exec return: {$ret}");
                return redirect()->back()->with('success', 'Restore has been queued. It will run in background.');
            }
        } catch (\Exception $e) {
            Log::error('Restore enqueue error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to queue restore: ' . $e->getMessage());
        }
    }

    /**
     * Download the named backup file from the configured backup directory.
     */
    public function downloadBackup(Request $request, $filename)
    {
        // sanitize filename (prevent directory traversal)
        $name = basename($filename);
        $fullPath = rtrim($this->backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;

        if (! File::exists($fullPath) || ! is_file($fullPath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($fullPath, $name, [
            'Content-Type' => 'application/sql'
        ]);
    }

    /**
     * Helper to find the latest file in backupDir.
     * Returns array ['path' => fullpath, 'name' => basename] or null
     */
    protected function getLatestBackupFile()
    {
        try {
            $dir = rtrim($this->backupDir, DIRECTORY_SEPARATOR);

            if (! File::exists($dir) || ! File::isDirectory($dir)) {
                return null;
            }

            $files = File::files($dir);
            if (empty($files)) return null;

            $latest = null;
            $latestTime = 0;
            foreach ($files as $f) {
                if (! $f->isFile()) continue;
                $mtime = $f->getMTime();
                if ($mtime >= $latestTime) {
                    $latestTime = $mtime;
                    $latest = $f;
                }
            }

            if (! $latest) return null;

            return [
                'path' => $latest->getPathname(),
                'name' => $latest->getFilename(),
            ];
        } catch (\Throwable $e) {
            Log::error('getLatestBackupFile error: '.$e->getMessage());
            return null;
        }
    }
}


