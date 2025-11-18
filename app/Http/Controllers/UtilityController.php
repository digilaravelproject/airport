<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
        return view('utility.index');
    }

    /**
     * Run the backup shell script via sudo.
     * Make sure apache user is allowed to run the script via sudoers (see notes).
     */
    public function backup(Request $request)
    {
        try {
            // path to your script - ensure this is correct
            $script = '/usr/local/bin/sql_autobackup.sh';

            if (!File::exists($script)) {
                return redirect()->back()->with('error', "Backup script not found at: {$script}");
            }

            // run script asynchronously so the web request doesn't hang
            // use & to send to background
            // Ensure apache user can run this with sudo without password.
            $cmd = 'sudo ' . escapeshellcmd($script) . ' > /dev/null 2>&1 &';
            shell_exec($cmd);

            return redirect()->back()->with('success', 'Backup script triggered.');
        } catch (\Exception $e) {
            Log::error('Backup failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to trigger backup: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from uploaded .sql file
     * - Validates uploaded file
     * - Drops all existing tables (disables FK checks)
     * - Imports the uploaded SQL into the DB using mysql CLI
     */
    /**
 * Restore database from uploaded .sql file
 * - Validates uploaded file
 * - Drops all existing tables (disables FK checks)
 * - Imports the uploaded SQL into the DB using mysql CLI
 *
 * Note: This method temporarily switches Laravel's session driver to 'array'
 * so no DB session reads/writes occur while tables are dropped and imported.
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

          // prepare command: use same PHP binary and artisan
          $php = PHP_BINARY; // ensures same PHP executable
          $artisan = base_path('artisan');

          // full artisan command (quoted)
          $artisanCmd = escapeshellarg($php) . ' ' . escapeshellarg($artisan) . ' db:restore ' . escapeshellarg($fullPath);

          // log helper
          $logPath = storage_path('logs/restore.log');
          $log = function($msg) use ($logPath) {
              File::append($logPath, '['.date('Y-m-d H:i:s').'] '.$msg."\n");
              Log::info($msg);
          };

          $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

          if ($isWindows) {
              // Windows: use start /B to detach
              // start syntax: start "windowTitle" /B "full command"
              // we wrap command in cmd /c so < and other things work inside DbRestore (DbRestore itself uses mysql, not redirection here)
              $bgCmd = 'start /B "" ' . $artisanCmd;
              // Start detached using pclose(popen()) for better detach on Windows
              $log("Attempting Windows background start: {$bgCmd}");
              // Use popen to detach
              $process = @popen($bgCmd, 'r');
              if ($process !== false) {
                  @pclose($process);
                  $log('Windows background process started via popen().');
                  return redirect()->back()->with('success', 'Restore has been queued. It will run in background.');
              } else {
                  $log('popen() failed — falling back to synchronous exec for debugging.');
                  // fallback to synchronous for debug
                  exec($artisanCmd . ' 2>&1', $out, $ret);
                  $log("Sync fallback exit code: {$ret}");
                  $log("Sync fallback output: " . implode("\n", $out));
                  if ($ret === 0) {
                      return redirect()->back()->with('success', 'Restore completed (sync fallback).');
                  }
                  return redirect()->back()->with('error', 'Restore failed (check storage/logs/restore.log).');
              }
          } else {
              // Linux / Unix: use nohup and & to detach
              $bgCmd = 'nohup ' . $artisanCmd . ' > /dev/null 2>&1 &';
              $log("Attempting Linux background start: {$bgCmd}");
              exec($bgCmd, $out, $ret);
              $log("nohup exec return: {$ret}");
              // exec for nohup normally returns quickly; can't rely on $ret
              return redirect()->back()->with('success', 'Restore has been queued. It will run in background.');
          }
      } catch (\Exception $e) {
          Log::error('Restore enqueue error: '.$e->getMessage());
          return redirect()->back()->with('error', 'Failed to queue restore: ' . $e->getMessage());
      }
  }
}
