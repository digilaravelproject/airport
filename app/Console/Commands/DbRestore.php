<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DbRestore extends Command
{
    protected $signature = 'db:restore {path}';
    protected $description = 'Restore database from a SQL file (path). Drops all tables then imports file.';

    protected $logPath;

    public function __construct()
    {
        parent::__construct();
        $this->logPath = storage_path('logs/restore.log');
    }

    protected function writeLog($level, $msg)
    {
        // write to Laravel log and the dedicated restore.log
        Log::{$level}($msg);
        File::append($this->logPath, '['.date('Y-m-d H:i:s')."] {$level}: {$msg}\n");
    }

    public function handle()
    {
        $path = $this->argument('path');
        $this->writeLog('info', "DbRestore started for path: {$path}");

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");
            $this->writeLog('error', "File not found: {$path}");
            return 1;
        }

        try {
            // --- DROP TABLES (no explicit transaction) ---
            $this->writeLog('info', 'Dropping all tables (disabling FK checks) - no transaction used...');
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            $rows = DB::select('SHOW TABLES');
            foreach ($rows as $row) {
                $table = array_values((array)$row)[0] ?? null;
                if ($table) {
                    DB::statement("DROP TABLE IF EXISTS `{$table}`;");
                    $this->writeLog('info', "Dropped table: {$table}");
                    $this->info("Dropped table: {$table}");
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            $this->writeLog('info', 'All tables dropped.');

            // --- Prepare DB credentials ---
            $dbConfig = config('database.connections.' . config('database.default'));
            $dbName = $dbConfig['database'] ?? null;
            $dbUser = $dbConfig['username'] ?? null;
            $dbPass = $dbConfig['password'] ?? '';

            if (empty($dbName) || empty($dbUser)) {
                $this->writeLog('error', 'Database configuration missing (database/username).');
                $this->error('Database configuration missing.');
                return 1;
            }

            // --- Locate mysql binary ---
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            if ($isWindows) {
                $possible = [
                    'C:\\xampp\\mysql\\bin\\mysql.exe',
                    'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
                    'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysql.exe',
                ];
                $mysqlBin = null;
                foreach ($possible as $p) {
                    if (file_exists($p)) { $mysqlBin = $p; break; }
                }
                if (!$mysqlBin) {
                    $mysqlBin = 'mysql';
                }
            } else {
                $mysqlBin = '/usr/bin/mysql';
                if (!file_exists($mysqlBin)) {
                    $mysqlBin = 'mysql';
                }
            }

            $this->writeLog('info', "Using mysql binary: {$mysqlBin}");

            // === IMPORT using proc_open (pass SQL via STDIN) ===
            $cmd = [$mysqlBin, '-u', $dbUser];
            if ($dbPass !== null && $dbPass !== '') {
                $cmd[] = '-p' . $dbPass;
            }
            $cmd[] = $dbName;

            $this->writeLog('info', 'Preparing to run mysql import with proc_open.');

            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            $process = proc_open($cmd, $descriptors, $pipes, null, null);

            if (!is_resource($process)) {
                $this->writeLog('error', 'proc_open failed to start mysql process.');
                $this->error('Failed to start mysql process (proc_open). See logs.');
                return 1;
            }

            // feed sql to stdin
            $sqlHandle = fopen($path, 'rb');
            if ($sqlHandle === false) {
                $this->writeLog('error', "Failed to open SQL file for reading: {$path}");
                fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]);
                proc_close($process);
                return 1;
            }

            while (!feof($sqlHandle)) {
                $chunk = fread($sqlHandle, 8192);
                if ($chunk === false) break;
                fwrite($pipes[0], $chunk);
            }
            fclose($sqlHandle);
            fclose($pipes[0]);

            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $returnCode = proc_close($process);

            $this->writeLog('info', "mysql process exited with code: {$returnCode}");
            if (trim($stdout) !== '') {
                $this->writeLog('info', "mysql stdout: " . substr($stdout, 0, 10000));
            }
            if (trim($stderr) !== '') {
                $this->writeLog('error', "mysql stderr: " . substr($stderr, 0, 10000));
            }

            if ($returnCode !== 0) {
                $this->writeLog('error', "Import failed: exit code {$returnCode}. See stderr above.");
                $this->error('Import failed. See storage/logs/restore.log for details.');
                return 1;
            }

            $this->writeLog('info', "Restore completed successfully.");
            $this->info("Restore completed successfully.");
            return 0;

        } catch (\Throwable $e) {
            $this->writeLog('error', "Unhandled exception: " . $e->getMessage());
            $this->writeLog('error', $e->getTraceAsString());
            $this->error("Exception: " . $e->getMessage());
            return 1;
        }
    }
}
