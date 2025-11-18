<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
class InventoryActionController extends Controller
{
    private function base(?string $url): ?string
    {
        return $url ? rtrim($url, '/') : null;
    }

    public function ping(Request $request, Inventory $inventory)
    {
        $base = $this->base($inventory->mgmt_url);
        $ip   = trim((string) $inventory->box_ip);

        if (!$base && !$ip) {
            return response()->json([
                'success' => false,
                'message' => 'No management URL or IP configured.'
            ], 422);
        }

        // ------------------ 1) HTTP health check (your original logic) ------------------
        if ($base) {
            try {
                $http = Http::timeout(4)->withoutVerifying();
                if (!empty($inventory->mgmt_token)) {
                    $http = $http->withToken($inventory->mgmt_token);
                }

                $paths = ['/system', '/system/health'];
                foreach ($paths as $p) {
                    $resp = $http->get($base . $p);
                    if ($resp->successful()) {
                        $body = $resp->json();
                        return response()->json([
                            'success' => true,
                            'method'  => 'http',
                            'status'  => data_get($body, 'status', 'online'),
                            'code'    => $resp->status(),
                            'raw'     => $body ?? $resp->body(),
                        ]);
                    }
                }

                // HTTP responded but not 2xx
                return response()->json([
                    'success' => false,
                    'method'  => 'http',
                    'message' => 'HTTP status check failed',
                    'code'    => isset($resp) ? $resp->status() : 0,
                    'raw'     => isset($resp) ? $resp->body() : null,
                ], 200);
            } catch (\Throwable $e) {
                Log::info('HTTP status check failed, trying ADB/ICMP: ' . $e->getMessage());
            }
        }

        // ------------------ 2) ADB reachability (TCP 5555) ------------------
        if ($ip) {
            $adbResult = $this->adbPing($ip);
            if ($adbResult['success']) {
                return response()->json($adbResult);
            }
            // Not fatal—log and continue to ICMP fallback
            Log::info('ADB ping failed', $adbResult);
        }

        // ------------------ 3) ICMP ping fallback ------------------
        if ($ip) {
            $isWindows = strtoupper(PHP_OS_FAMILY) === 'WINDOWS';
            $cmd = $isWindows
                ? 'ping -n 1 -w 1000 ' . escapeshellarg($ip)
                : '/usr/bin/ping -c 1 -W 1 ' . escapeshellarg($ip);

            [$status, $outLines] = $this->runCmd($cmd); // capture stderr too

            return response()->json([
                'success' => $status === 0,
                'method'  => 'icmp',
                'status'  => $status === 0 ? 'online' : 'offline',
                'raw'     => $outLines,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'HTTP failed and no IP for ICMP.'
        ], 502);
    }

    /**
     * Try to "ping" a device over ADB:
     *  - checks adb & key dirs
     *  - optional OS ping first (not required)
     *  - adb connect ip:5555
     *  - adb shell: try `getprop sys.boot_completed || echo ok`
     *  - adb disconnect
     */
    private function adbPing(string $deviceIP): array
    {
        $port     = 5555;
        $adbPath  = '/usr/bin/adb';
        $homeDir  = '/var/www';
        $keyDir   = '/var/www/.android';

        $messages = [];
        if (!file_exists($adbPath)) {
            return [
                'success' => false,
                'method'  => 'adb',
                'status'  => 'offline',
                'message' => "ADB binary not found at: $adbPath",
                'raw'     => $messages,
            ];
        }
        if (!is_dir($keyDir)) {
            return [
                'success' => false,
                'method'  => 'adb',
                'status'  => 'offline',
                'message' => "ADB key directory not found at: $keyDir",
                'raw'     => $messages,
            ];
        }

        // Build a clean env for child processes
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        $adb    = escapeshellarg($adbPath);
        $serial = escapeshellarg("{$deviceIP}:{$port}");

        // Soft ICMP ping (optional)
        $pingCmd = (stripos(PHP_OS, 'WIN') === 0)
            ? "ping -n 1 $deviceIP"
            : "/usr/bin/ping -c 1 $deviceIP";

        [$pStatus, $pOut] = $this->runCmd($pingCmd, $env);
        if ($pStatus !== 0) {
            $messages[] = 'ICMP ping failed (continuing with ADB).';
            $messages   = array_merge($messages, $pOut);
        } else {
            $messages[] = 'ICMP ping ok.';
        }

        // ADB connect
        $this->runCmd("$adb disconnect $serial", $env); // ignore status
        [$cStatus, $cOut] = $this->runCmd("$adb connect $serial", $env);
        $messages = array_merge($messages, $cOut);
        $connText = strtolower(implode("\n", $cOut));
        if ($cStatus !== 0 || str_contains($connText, 'unable') || str_contains($connText, 'failed')) {
            return [
                'success' => false,
                'method'  => 'adb',
                'status'  => 'offline',
                'message' => 'Failed to connect via ADB.',
                'raw'     => $messages,
            ];
        }

        // ADB shell check – prefer boot_completed, fallback to echo
        [$sStatus, $sOut] = $this->runCmd("$adb -s $serial shell getprop sys.boot_completed || $adb -s $serial shell echo ok", $env);
        $messages = array_merge($messages, $sOut);

        // Always disconnect (best-effort)
        $this->runCmd("$adb disconnect $serial", $env);

        $joined = strtolower(implode("\n", $sOut));
        $ok = ($sStatus === 0) && (str_contains($joined, '1') || str_contains($joined, 'ok'));

        return [
            'success' => $ok,
            'method'  => 'adb',
            'status'  => $ok ? 'online' : 'offline',
            'raw'     => $messages,
        ];
    }

    /**
     * Run a shell command and capture exit code + stdout+stderr lines.
     * Works on shared hosts where exec/proc_open are allowed.
     */
    private function runCmd(string $cmd, ?array $env = null): array
    {
        $desc = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $proc = @proc_open($cmd . ' 2>&1', $desc, $pipes, null, $env ?? null);
        if (!is_resource($proc)) {
            return [1, ['Failed to start process']];
        }

        $out = stream_get_contents($pipes[1]);
        @fclose($pipes[1]);

        $code = @proc_close($proc);
        $lines = $out === '' ? [] : explode("\n", trim($out));

        return [$code, $lines];
    }

    /** GET {mgmt_url}/system (or /system/health); fallback to ICMP on box_ip */
    public function ping_old(Request $request, Inventory $inventory)
    {
        $base = $this->base($inventory->mgmt_url);
        $ip   = trim((string) $inventory->box_ip);

        if (!$base && !$ip) {
            return response()->json([
                'success' => false,
                'message' => 'No management URL or IP configured.'
            ], 422);
        }

        // ---- Try HTTP first -------------------------------------------------
        if ($base) {
            try {
                $http = Http::timeout(4)->withoutVerifying();
                if (!empty($inventory->mgmt_token)) {
                    $http = $http->withToken($inventory->mgmt_token);
                }

                // Common health/status endpoints
                $paths = ['/system', '/system/health'];
                foreach ($paths as $p) {
                    $resp = $http->get($base . $p);
                    if ($resp->successful()) {
                        // If body has a status field, surface it; otherwise just say "online"
                        $body = $resp->json();
                        return response()->json([
                            'success' => true,
                            'method'  => 'http',
                            'status'  => data_get($body, 'status', 'online'),
                            'code'    => $resp->status(),
                            'raw'     => $body ?? $resp->body(),
                        ]);
                    }
                }

                // HTTP responded but not 2xx
                return response()->json([
                    'success' => false,
                    'method'  => 'http',
                    'message' => 'HTTP status check failed',
                    'code'    => isset($resp) ? $resp->status() : 0,
                    'raw'     => isset($resp) ? $resp->body() : null,
                ], 200);
            } catch (\Throwable $e) {
                Log::info('HTTP status check failed, trying ICMP: ' . $e->getMessage());
            }
        }

        // ---- Fallback: ICMP ping to box_ip ---------------------------------
        if ($ip) {
            $isWindows = strtoupper(PHP_OS_FAMILY) === 'WINDOWS';
            $cmd = $isWindows
                ? 'ping -n 1 -w 1000 ' . escapeshellarg($ip)   // 1 packet, 1s timeout
                : 'ping -c 1 -W 1 '    . escapeshellarg($ip);

            $output = [];
            $status = 1;
            // exec can be disabled on some hosts; suppress warnings
            @exec($cmd, $output, $status);

            return response()->json([
                'success' => $status === 0,
                'method'  => 'icmp',
                'status'  => $status === 0 ? 'online' : 'offline',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'HTTP failed and no IP for ICMP.'
        ], 502);
    }

    /** POST /inventories/{inventory}/reboot — ADB-based reboot */
    public function reboot(Request $request, Inventory $inventory)
    {
        $ip = trim((string) $inventory->box_ip);

        if ($ip === '') {
            return response()->json([
                'success' => false,
                'message' => 'Reboot skipped: no device IP configured.',
            ], 422);
        }

        try {
            $messages = $this->rebootViaAdb($ip);

            // If the last line looks successful, mark success=true, else false
            $ok = collect($messages)->contains(fn ($m) =>
                str_starts_with($m, '✅ Reboot command sent successfully') ||
                str_starts_with($m, '✅ Device rebooted and is back online')
            );

            return response()->json([
                'success'  => $ok,
                'message'  => $ok ? 'Reboot command processed via ADB.' : 'Not rebooted.',
                'messages' => $messages,
            ], $ok ? 200 : 502);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'ADB reboot failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reboot an Android device over ADB (TCP/IP) and wait for it to come back.
     * - Uses a clean environment (HOME + ADB_VENDOR_KEYS)
     * - Captures stdout/stderr from every command
     * - Soft ICMP ping first, then ADB-level connectivity checks
     */
    private function rebootViaAdb(string $deviceIP): array
    {
        $port     = 5555;
        $messages = [];

        // ---- paths / env ----
        $adbPath  = '/usr/bin/adb';
        $homeDir  = '/var/www';
        $keyDir   = '/var/www/.android';

        if (!file_exists($adbPath)) {
            return ["❌ ADB binary not found at: $adbPath"];
        }
        if (!is_dir($keyDir)) {
            return ["❌ ADB key directory not found at: $keyDir"];
        }

        // Environment for child processes
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
            // You can add LANG/LC_ALL if your shell outputs non-ASCII text
        ];

        $adb    = escapeshellarg($adbPath);
        $serial = escapeshellarg("{$deviceIP}:{$port}");

        // ---- helper to run a command and capture stdout+stderr ----
        $run = function (string $cmd, ?string $cwd = null) use ($env): array {
            // Merge stderr into stdout so we get a single combined stream
            $cmd .= ' 2>&1';

            $descriptors = [
                1 => ['pipe', 'w'], // stdout (merged)
            ];
            $proc = proc_open($cmd, $descriptors, $pipes, $cwd, $env);
            if (!is_resource($proc)) {
                return [1, ['Failed to start process']];
            }
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $status = proc_close($proc);

            // Split lines nicely (preserve messages even if empty)
            $lines = strlen(trim((string)$out)) ? preg_split('/\R/u', trim($out)) : [];
            return [$status, $lines];
        };

        // ---- 1) Soft network ping (optional but helpful) ----
        $messages[] = "🔍 Pinging device at $deviceIP...";
        $pingCmd = stripos(PHP_OS, 'WIN') === 0
            ? "ping -n 1 $deviceIP"
            : "/usr/bin/ping -c 1 $deviceIP";
        [$pingStatus, $pingOut] = $run($pingCmd);
        if ($pingStatus !== 0) {
            $messages[] = "⚠️ ICMP ping failed (continuing anyway).";
            if ($pingOut) { $messages = array_merge($messages, $pingOut); }
        } else {
            $messages[] = "✅ Device responded to ICMP ping";
        }

        // ---- 2) ADB connect ----
        $messages[] = "🔗 Connecting via ADB ($deviceIP:$port)...";
        $run("$adb disconnect $serial"); // ignore status
        [$connStatus, $connOut] = $run("$adb connect $serial");
        if ($connStatus !== 0 || stripos(implode("\n", $connOut), 'unable') !== false) {
            $messages[] = "❌ Failed to connect via ADB";
            if ($connOut) { $messages = array_merge($messages, $connOut); }
            return $messages;
        }
        $messages[] = "✅ ADB connected";
        // quick ADB “ping” (get-state should print 'device' on success)
        [$stateStatus, $stateOut] = $run("$adb -s $serial get-state");
        if ($stateStatus !== 0 || stripos(implode("\n", $stateOut), 'device') === false) {
            $messages[] = "⚠️ ADB get-state did not confirm 'device'";
            if ($stateOut) { $messages = array_merge($messages, $stateOut); }
        } else {
            $messages[] = "✅ ADB state: device";
        }

        // ---- 3) Reboot ----
        $messages[] = "🔁 Sending reboot command...";
        [$rebootStatus, $rebootOut] = $run("$adb -s $serial reboot");
        if ($rebootStatus !== 0) {
            $messages[] = "❌ Failed to send reboot command";
            if ($rebootOut) { $messages = array_merge($messages, $rebootOut); }
            return $messages;
        }
        $messages[] = "✅ Reboot command sent";

        // ---- 4) Wait for device to come back ----
        $messages[] = "⏳ Waiting for device to come back online...";
        // small grace period so the adb server doesn’t race the reboot
        sleep(5);

        // Try wait-for-device with a manual timeout loop to avoid hanging forever
        $deadline   = time() + 120; // 2 minutes
        $cameBack   = false;
        while (time() < $deadline) {
            // reconnect (some stacks drop the TCP session after reboot)
            $run("$adb disconnect $serial");
            $run("$adb connect $serial");
            [$wStatus, $wOut] = $run("$adb -s $serial get-state");
            if ($wStatus === 0 && stripos(implode("\n", $wOut), 'device') !== false) {
                $cameBack = true;
                break;
            }
            sleep(3);
        }

        if ($cameBack) {
            // extra: wait until Android reports boot completed (best-effort)
            [$bootStatus, $bootOut] = $run("$adb -s $serial shell getprop sys.boot_completed");
            if ($bootStatus === 0 && trim(implode('', $bootOut)) === '1') {
                $messages[] = "✅ Device rebooted and fully booted (sys.boot_completed=1)";
            } else {
                $messages[] = "✅ Device is back (ADB ready), boot completion not confirmed yet";
                if ($bootOut) { $messages = array_merge($messages, $bootOut); }
            }
        } else {
            $messages[] = "⚠️ Device did not come back online within timeout";
        }

        return $messages;
    }

    private function rebootViaAdb_old(string $deviceIP): array
    {
        $port = 5555;
        $messages = [];
        $adbPath = '/usr/bin/adb';

        if (!file_exists($adbPath)) {
            return ["❌ ADB binary not found at: $adbPath"];
        }

        putenv('ADB_VENDOR_KEYS=/var/www/.android');

        $adb    = escapeshellarg($adbPath);
        $serial = escapeshellarg("{$deviceIP}:{$port}");

        // 1) Ping
        $messages[] = "🔍 Pinging device at $deviceIP...";
        $pingCmd = (stripos(PHP_OS, 'WIN') === 0) ? "ping -n 1 $deviceIP" : "ping -c 1 $deviceIP";
        exec($pingCmd, $pingOut, $pingStatus);
        if ($pingStatus !== 0) {
            $messages[] = "❌ Device not reachable (ping failed)";
            return $messages;
        }
        $messages[] = "✅ Device is online";
        sleep(1);

        // 2) ADB connect
        $messages[] = "🔗 Connecting via ADB...";
        exec("$adb disconnect $serial", $discOut, $discStatus);
        exec("$adb connect $serial", $connOut, $connStatus);
        if ($connStatus !== 0) {
            $messages[] = "❌ Failed to connect via ADB";
            if (!empty($connOut)) { $messages = array_merge($messages, $connOut); }
            return $messages;
        }
        $messages[] = "✅ ADB connected";
        sleep(1);

        // 3) Reboot
        $messages[] = "🔁 Sending reboot command...";
        exec("$adb -s $serial reboot", $rebootOut, $rebootStatus);
        if ($rebootStatus === 0) {
            $messages[] = "✅ Reboot command sent successfully";
        } else {
            $messages[] = "❌ Failed to send reboot command";
            if (!empty($rebootOut)) { $messages = array_merge($messages, $rebootOut); }
            return $messages;
        }

        // 4) Wait for device
        $messages[] = "⏳ Waiting for device to come back online...";
        exec("$adb -s $serial wait-for-device", $waitOut, $waitStatus);
        if ($waitStatus === 0) {
            $messages[] = "✅ Device rebooted and is back online";
        } else {
            $messages[] = "⚠️ Device did not come back online automatically";
            if (!empty($waitOut)) { $messages = array_merge($messages, $waitOut); }
        }

        return $messages;
    }

    public function screenshot_old(Inventory $inventory): JsonResponse
    {
        // ─────────────────────────────────────────────────────────────
        // CONFIG (adjust paths as needed)
        // ─────────────────────────────────────────────────────────────
        $adbPath = '/usr/bin/adb';
        $homeDir = '/var/www';
        $keyDir  = '/var/www/.android';
        $port    = 5555;

        // ─────────────────────────────────────────────────────────────
        // Resolve device IP (prefer explicit field, else parse from mgmt_url)
        // ─────────────────────────────────────────────────────────────

        $deviceIP = $inventory->box_ip;

        if (!$deviceIP) {
            return response()->json([
                'success' => false,
                'message' => 'Box IP not found (set box_ip).',
            ], 422);
        }

        // ─────────────────────────────────────────────────────────────
        // Basic pre-checks
        // ─────────────────────────────────────────────────────────────
        if (!file_exists($adbPath)) {
            return response()->json([
                'success' => false,
                'message' => "ADB binary not found at: {$adbPath}",
            ], 500);
        }

        if (!is_dir($keyDir)) {
            return response()->json([
                'success' => false,
                'message' => "ADB key directory not found at: {$keyDir}",
            ], 500);
        }

        // Build a clean env for child processes
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        $adb    = escapeshellarg($adbPath);
        $serial = escapeshellarg("{$deviceIP}:{$port}");

        // ─────────────────────────────────────────────────────────────
        // Small helpers
        // ─────────────────────────────────────────────────────────────

        // Run command and capture stdout+stderr (text)
        $run = function (string $cmd) use ($env): array {
            $descriptors = [
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];
            $proc = proc_open($cmd . ' 2>&1', $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) {
                return [1, "Failed to start process: {$cmd}"];
            }
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $exit = proc_close($proc);
            return [$exit, trim($out)];
        };

        // Run command and capture **binary** stdout (do NOT merge stderr)
        $runBinary = function (string $cmd) use ($env): array {
            $descriptors = [
                1 => ['pipe', 'w'], // stdout (binary)
                2 => ['pipe', 'w'], // stderr
            ];
            $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) {
                return [1, null, 'Failed to start process'];
            }
            $stdout = stream_get_contents($pipes[1]); // binary data
            $stderr = stream_get_contents($pipes[2]); // text
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($proc);
            return [$exit, $stdout, trim($stderr)];
        };

        $messages = [];

        // ─────────────────────────────────────────────────────────────
        // 1) Ping (soft check, optional)
        // ─────────────────────────────────────────────────────────────
        $messages[] = "🔍 Pinging {$deviceIP}…";
        $pingCmd = (stripos(PHP_OS, 'WIN') === 0)
            ? "ping -n 1 {$deviceIP}"
            : "/usr/bin/ping -c 1 {$deviceIP}";
        [$pingStatus, $pingOut] = $run($pingCmd);
        if ($pingStatus !== 0) {
            $messages[] = "⚠️ Ping failed (continuing anyway)";
            if ($pingOut) $messages[] = $pingOut;
        } else {
            $messages[] = "✅ Ping OK";
        }

        // ─────────────────────────────────────────────────────────────
        // 2) ADB connect
        // ─────────────────────────────────────────────────────────────
        $messages[] = "🔗 ADB connect to {$deviceIP}:{$port}";
        $run("$adb disconnect $serial"); // ignore result
        [$connStatus, $connOut] = $run("$adb connect $serial");
        if ($connStatus !== 0 || stripos($connOut, 'unable') !== false) {
            $messages[] = "❌ ADB connect failed";
            if ($connOut) $messages[] = $connOut;
            return response()->json([
                'success'  => false,
                'message'  => 'ADB connect failed.',
                'messages' => $messages,
            ], 500);
        }
        $messages[] = "✅ ADB connected";

        // ─────────────────────────────────────────────────────────────
        // 3) Capture screenshot as PNG (binary to stdout)
        // ─────────────────────────────────────────────────────────────
        // Prefer exec-out to stream PNG directly
        $cmd = "$adb -s $serial exec-out screencap -p";
        [$capStatus, $png, $capErr] = $runBinary($cmd);

        // Some devices return CRLF in PNG stream; normalize (safe no-op on good PNGs)
        if ($png !== null) {
            $png = str_replace("\r\n", "\n", $png);
        }

        // Fallback: capture to file then pull as binary
        if ($capStatus !== 0 || !$png) {
            $messages[] = "ℹ️ exec-out failed, trying fallback (shell + pull)…";
            $tmpRemote = '/sdcard/__tmp_screen.png';
            [$shStatus, $shOut] = $run("$adb -s $serial shell screencap -p $tmpRemote");
            if ($shStatus !== 0) {
                $messages[] = "❌ screencap shell failed";
                if ($shOut) $messages[] = $shOut;
                if ($capErr) $messages[] = $capErr;
                return response()->json([
                    'success'  => false,
                    'message'  => 'Screenshot capture failed.',
                    'messages' => $messages,
                ], 500);
            }
            // Pull the file to stdout (-)
            [$pullStatus, $png, $pullErr] = $runBinary("$adb -s $serial pull $tmpRemote -");
            // Best-effort clean-up
            $run("$adb -s $serial shell rm -f $tmpRemote");
            if ($pullStatus !== 0 || !$png) {
                $messages[] = "❌ adb pull failed";
                if ($pullErr) $messages[] = $pullErr;
                return response()->json([
                    'success'  => false,
                    'message'  => 'Screenshot pull failed.',
                    'messages' => $messages,
                ], 500);
            }
        }

        // ─────────────────────────────────────────────────────────────
        // 4) Save PNG into public storage
        // ─────────────────────────────────────────────────────────────
        $dir      = 'inventories/screenshots';
        $filename = $inventory->id . '-' . now()->format('YmdHis') . '.png';
        $path     = $dir . '/' . $filename;

        try {
            Storage::disk('public')->put($path, $png);
        } catch (\Throwable $e) {
            $messages[] = "❌ Failed to write PNG: " . $e->getMessage();
            return response()->json([
                'success'  => false,
                'message'  => 'Failed to save screenshot.',
                'messages' => $messages,
            ], 500);
        }

        // ─────────────────────────────────────────────────────────────
        // 5) Update inventory photo and disconnect
        // ─────────────────────────────────────────────────────────────
        $inventory->photo = $path;
        $inventory->save();

        $run("$adb disconnect $serial");

        $messages[] = "✅ Screenshot saved: storage/{$path}";

        return response()->json([
            'success'  => true,
            'message'  => 'Screenshot captured via ADB.',
            'path'     => asset('storage/' . $path),
            'method'   => 'adb',
            'messages' => $messages,
        ]);
    }

    // Add these imports at top of your controller file if not present:
    // use Illuminate\Http\JsonResponse;
    // use Illuminate\Support\Facades\Storage;

    public function screenshot1(Inventory $inventory): JsonResponse
    {
        // CONFIG
        $adbPath = '/usr/bin/adb';
        $homeDir = '/var/www';
        $keyDir  = '/var/www/.android';
        $port    = 5555;
        $tmpDir  = sys_get_temp_dir();

        $deviceIP = $inventory->box_ip;
        if (!$deviceIP) {
            return response()->json(['success'=>false,'message'=>'Box IP not found (set box_ip).'], 422);
        }

        // basic checks
        if (!file_exists($adbPath)) {
            return response()->json(['success'=>false,'message'=>"ADB binary not found at: {$adbPath}"], 500);
        }
        if (!is_dir($keyDir)) {
            return response()->json(['success'=>false,'message'=>"ADB key directory not found at: {$keyDir}"], 500);
        }

        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];
        $adb    = escapeshellarg($adbPath);
        $serial = escapeshellarg("{$deviceIP}:{$port}");

        // helpers
        $run = function(string $cmd) use ($env) {
            $descriptors = [1=>['pipe','w'], 2=>['pipe','w']];
            $proc = proc_open($cmd . ' 2>&1', $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) return [1, "Failed to start process: {$cmd}"];
            $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
            $exit = proc_close($proc);
            return [$exit, trim($out)];
        };

        $runBinary = function(string $cmd) use ($env) {
            $descriptors = [1=>['pipe','w'], 2=>['pipe','w']];
            $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) return [1, null, 'Failed to start process'];
            $stdout = stream_get_contents($pipes[1]); $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $exit = proc_close($proc);
            return [$exit, $stdout, trim($stderr)];
        };

        $isMostlyBlack = function (?string $pngBinary, $sampleStep = 10, $threshold = 12) {
            if (!$pngBinary) return true;
            $img = @imagecreatefromstring($pngBinary);
            if (!$img) return true;
            $w = imagesx($img); $h = imagesy($img);
            if ($w === 0 || $h === 0) { imagedestroy($img); return true; }
            $total = 0; $count = 0;
            for ($x=0; $x<$w; $x += max(1, intval($w/$sampleStep))) {
                for ($y=0; $y<$h; $y += max(1, intval($h/$sampleStep))) {
                    $rgb = imagecolorat($img, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    // perceived brightness
                    $lum = (0.299*$r + 0.587*$g + 0.114*$b);
                    $total += $lum; $count++;
                }
            }
            imagedestroy($img);
            if ($count===0) return true;
            $avg = $total / $count;
            return ($avg < $threshold);
        };

        $messages = [];

        // 1) quick ping (non-fatal)
        $messages[] = "Pinging {$deviceIP}…";
        [$pingStatus, $pingOut] = $run((stripos(PHP_OS, 'WIN')===0) ? "ping -n 1 {$deviceIP}" : "/usr/bin/ping -c 1 {$deviceIP}");
        if ($pingStatus !== 0) {
            $messages[] = "Ping failed (continuing): " . ($pingOut ?: '');
        } else {
            $messages[] = "Ping OK";
        }

        // ensure adb disconnected then connect
        $run("$adb disconnect $serial");
        [$connStatus, $connOut] = $run("$adb connect $serial");
        if ($connStatus !== 0 || stripos($connOut, 'unable') !== false) {
            $messages[] = "ADB connect failed: " . ($connOut ?: '');
            return response()->json(['success'=>false,'message'=>'ADB connect failed.','messages'=>$messages], 500);
        }
        $messages[] = "ADB connected";

        // 2) Try exec-out screencap (fast)
        $messages[] = "Trying exec-out screencap (screencap -p via exec-out)…";
        $cmd = "$adb -s $serial exec-out screencap -p";
        [$capStatus, $png, $capErr] = $runBinary($cmd);
        // normalize CRLF
        if ($png !== null) $png = str_replace("\r\n", "\n", $png);

        if ($capStatus === 0 && $png) {
            $messages[] = "screencap returned " . strlen($png) . " bytes";
            if (!($isMostlyBlack($png))) {
                // good screenshot
                $messages[] = "Screenshot appears valid (not mostly black). Saving…";
                $dir = 'inventories/screenshots';
                $filename = $inventory->id . '-' . now()->format('YmdHis') . '.png';
                $path = $dir . '/' . $filename;
                try {
                    Storage::disk('public')->put($path, $png);
                    $inventory->photo = $path; $inventory->save();
                    $run("$adb disconnect $serial");
                    $messages[] = "Saved to storage/{$path}";
                    return response()->json([
                        'success'=>true,
                        'message'=>'Screenshot captured via exec-out screencap.',
                        'path'=>asset('storage/'.$path),
                        'method'=>'adb-screencap',
                        'messages'=>$messages,
                    ]);
                } catch (\Throwable $e) {
                    $messages[] = "Failed saving PNG: " . $e->getMessage();
                    // continue to fallback
                }
            } else {
                $messages[] = "Captured image looks mostly black — falling back to other methods.";
            }
        } else {
            $messages[] = "exec-out screencap failed: " . ($capErr ?: $capStatus);
        }

        // 3) Fallback: screenrecord (1s) -> pull MP4 -> extract frame using ffmpeg
        $messages[] = "Trying screenrecord fallback (record 1s + extract frame with ffmpeg)…";
        $remoteMp4 = '/sdcard/__tmp_screen.mp4';
        // remove any previous remote file
        $run("$adb -s $serial shell rm -f $remoteMp4");
        [$recStatus, $recOut] = $run("$adb -s $serial shell screenrecord --time-limit 1 {$remoteMp4}");
        if ($recStatus !== 0) {
            $messages[] = "screenrecord command failed: " . ($recOut ?: $recStatus);
        } else {
            // pull file to local temp
            $localMp4 = tempnam($tmpDir, 'scr_') . '.mp4';
            [$pullStatus, $pullOut] = $run("$adb -s $serial pull {$remoteMp4} " . escapeshellarg($localMp4));
            // clean remote
            $run("$adb -s $serial shell rm -f {$remoteMp4}");
            if ($pullStatus !== 0) {
                $messages[] = "adb pull mp4 failed: " . ($pullOut ?: $pullStatus);
            } else {
                $messages[] = "Pulled MP4 to {$localMp4}";
                // check for ffmpeg
                [$whichFfmpegStatus, $whichFfmpegOut] = $run("which ffmpeg");
                if ($whichFfmpegStatus !== 0) {
                    $messages[] = "ffmpeg not found on server — cannot extract frame from mp4.";
                } else {
                    $localPng = tempnam($tmpDir, 'scr_') . '.png';
                    $ffmpegCmd = "ffmpeg -y -i " . escapeshellarg($localMp4) . " -vframes 1 -f image2 " . escapeshellarg($localPng) . " 2>&1";
                    [$ffStatus, $ffOut] = $run($ffmpegCmd);
                    if ($ffStatus !== 0) {
                        $messages[] = "ffmpeg failed: " . ($ffOut ?: $ffStatus);
                    } else {
                        $messages[] = "ffmpeg produced PNG {$localPng}";
                        $png2 = @file_get_contents($localPng);
                        @unlink($localMp4);
                        @unlink($localPng);
                        if ($png2 && !($isMostlyBlack($png2))) {
                            // save and return
                            $dir = 'inventories/screenshots';
                            $filename = $inventory->id . '-' . now()->format('YmdHis') . '.png';
                            $path = $dir . '/' . $filename;
                            try {
                                Storage::disk('public')->put($path, $png2);
                                $inventory->photo = $path; $inventory->save();
                                $run("$adb disconnect $serial");
                                $messages[] = "Saved to storage/{$path}";
                                return response()->json([
                                    'success'=>true,
                                    'message'=>'Screenshot captured via screenrecord+ffmpeg fallback.',
                                    'path'=>asset('storage/'.$path),
                                    'method'=>'screenrecord+ffmpeg',
                                    'messages'=>$messages,
                                ]);
                            } catch (\Throwable $e) {
                                $messages[] = "Failed to save fallback PNG: " . $e->getMessage();
                            }
                        } else {
                            $messages[] = "Fallback frame is missing or mostly black.";
                        }
                    }
                }
            }
        }

        // 4) Minicap fallback (if installed on device)
        $messages[] = "Trying minicap (if present on device) …";
        // check if /data/local/tmp/minicap exists
        [$lsStatus, $lsOut] = $run("$adb -s $serial shell ls /data/local/tmp/minicap");
        if ($lsStatus === 0) {
            $messages[] = "minicap binary found on device, attempting to run it.";
            // query device size
            [$sizeStatus, $sizeOut] = $run("$adb -s $serial shell wm size");
            if ($sizeStatus === 0 && preg_match('/Physical size:\s*(\d+)x(\d+)/i', $sizeOut, $m)) {
                $dw = $m[1]; $dh = $m[2];
            } else {
                // fallback to 1280x720
                $dw = 1280; $dh = 720;
                $messages[] = "Unable to detect screen size via 'wm size', using {$dw}x{$dh}.";
            }
            // run minicap -s (it outputs jpeg stream to stdout). Use -P widthxheight@virtual/rotation
            $minicapCmd = "$adb -s $serial shell LD_LIBRARY_PATH=/data/local/tmp /data/local/tmp/minicap -P {$dw}x{$dh}@{$dw}x{$dh}/0 -s";
            [$minStatus, $minOut, $minErr] = $runBinary($minicapCmd);
            if ($minStatus === 0 && $minOut) {
                // minicap outputs a stream that may contain JPEG frames; try to extract first JPEG from stream
                $stream = $minOut;
                // find JPEG start/end
                $start = strpos($stream, "\xFF\xD8");
                $end   = $start !== false ? strpos($stream, "\xFF\xD9", $start) : false;
                if ($start !== false && $end !== false) {
                    $jpeg = substr($stream, $start, $end - $start + 2);
                    // convert jpeg to png (optional) or just save jpeg as png path with correct content
                    $dir = 'inventories/screenshots';
                    $filename = $inventory->id . '-' . now()->format('YmdHis') . '.jpg';
                    $path = $dir . '/' . $filename;
                    try {
                        Storage::disk('public')->put($path, $jpeg);
                        // update record and return
                        $inventory->photo = $path; $inventory->save();
                        $run("$adb disconnect $serial");
                        $messages[] = "Saved minicap image to storage/{$path}";
                        return response()->json([
                            'success'=>true,
                            'message'=>'Screenshot captured via minicap.',
                            'path'=>asset('storage/'.$path),
                            'method'=>'minicap',
                            'messages'=>$messages,
                        ]);
                    } catch (\Throwable $e) {
                        $messages[] = "Failed to save minicap jpeg: " . $e->getMessage();
                    }
                } else {
                    $messages[] = "minicap produced output but no JPEG frame found.";
                }
            } else {
                $messages[] = "minicap run failed: " . ($minErr ?: $minOut ?: $minStatus);
            }
        } else {
            $messages[] = "minicap not found on device (ls returned: " . ($lsOut ?: '') . ")";
        }

        // final: nothing worked
        $run("$adb disconnect $serial");
        $messages[] = "All capture methods failed or produced black images.";

        // Add helpful recommendations
        $messages[] = "Recommendations: 1) Install a small MediaProjection-based service/app on the device to expose a snapshot HTTP endpoint (best reliable approach).";
        $messages[] = "2) Install the correct minicap binary for the device ABI (openstf/minicap) and try again.";
        $messages[] = "3) Ensure device isn't using a protected DRM surface (protected video / HDMI) — those cannot be captured without system privileges or a special app.";

        return response()->json([
            'success'=>false,
            'message'=>'Screenshot failed (all attempts). See messages for details.',
            'messages'=>$messages,
        ], 500);
    }

    public function screenshot(Inventory $inventory): JsonResponse
    {
        // -------------------------
        // CONFIG - adjust if needed
        // -------------------------
        $adbPath    = '/usr/bin/adb';        // path to adb
        $ffmpegPath = '/usr/bin/ffmpeg';     // path to ffmpeg (used to extract frame from mp4)
        $scrcpyPath = '/usr/bin/scrcpy';     // optional (if scrcpy installed)
        $homeDir    = '/var/www';            // HOME for adb keys
        $keyDir     = '/var/www/.android';   // ADB keys dir (if needed)
        $port       = 5555;                  // adb tcp port used by your devices
        $tmpDir     = sys_get_temp_dir();

        // -------------------------
        // Resolve device IP
        // -------------------------
        $deviceIP = $inventory->box_ip;
        if (!$deviceIP) {
            return response()->json([
                'success' => false,
                'message' => 'Box IP not found (set box_ip).',
            ], 422);
        }

        // -------------------------
        // Basic binary checks
        // -------------------------
        if (!file_exists($adbPath)) {
            return response()->json([
                'success' => false,
                'message' => "ADB binary not found at: {$adbPath}",
            ], 500);
        }

        // Build a minimal env for child processes
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        // -------------------------
        // Helpers to run commands
        // -------------------------
        $run = function (string $cmd) use ($env) {
            $descriptors = [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $proc = proc_open($cmd . ' 2>&1', $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) {
                return [1, "Failed to start: {$cmd}"];
            }
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $exit = proc_close($proc);
            return [$exit, trim($out)];
        };

        $runBinary = function (string $cmd) use ($env) {
            $descriptors = [
                1 => ['pipe', 'w'], // stdout (binary)
                2 => ['pipe', 'w'], // stderr
            ];
            $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) {
                return [1, null, "Failed to start process: {$cmd}"];
            }
            $stdout = stream_get_contents($pipes[1]); // binary
            $stderr = stream_get_contents($pipes[2]); // text
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($proc);
            return [$exit, $stdout, trim($stderr)];
        };

        $adb = escapeshellarg($adbPath);
        $serial = escapeshellarg("{$deviceIP}:{$port}");

        $messages = [];

        // -------------------------
        // Ping (soft check)
        // -------------------------
        $messages[] = "🔍 Pinging {$deviceIP}…";
        $pingCmd = (stripos(PHP_OS, 'WIN') === 0) ? "ping -n 1 {$deviceIP}" : "/usr/bin/ping -c 1 {$deviceIP}";
        [$pingStatus, $pingOut] = $run($pingCmd);
        if ($pingStatus !== 0) {
            $messages[] = "⚠️ Ping failed (continuing) — {$pingOut}";
        } else {
            $messages[] = "✅ Ping OK";
        }

        // -------------------------
        // ADB connect
        // -------------------------
        $messages[] = "🔗 ADB connect to {$deviceIP}:{$port}";
        $run("$adb disconnect $serial"); // best-effort
        [$connStatus, $connOut] = $run("$adb connect $serial");
        if ($connStatus !== 0 || stripos($connOut, 'unable') !== false) {
            $messages[] = "❌ ADB connect failed: {$connOut}";
            return response()->json([
                'success' => false,
                'message' => 'ADB connect failed.',
                'messages' => $messages,
            ], 500);
        }
        $messages[] = "✅ ADB connected: {$connOut}";

        // -------------------------
        // 1) Try exec-out screencap (fast)
        // -------------------------
        $messages[] = "📸 Trying exec-out screencap (screencap -p via adb exec-out)…";
        $cmd = "$adb -s $serial exec-out screencap -p";
        [$capStatus, $png, $capErr] = $runBinary($cmd);

        // Normalize CRLF -> LF
        if ($png !== null) {
            $png = str_replace("\r\n", "\n", $png);
        }

        // If exec-out succeeded and produced data, try save
        if ($capStatus === 0 && !empty($png)) {
            try {
                $dir      = 'inventories/screenshots';
                $filename = $inventory->id . '-' . now()->format('YmdHis') . '.png';
                $path     = $dir . '/' . $filename;
                Storage::disk('public')->put($path, $png);
                // update model
                $inventory->photo = $path;
                $inventory->save();
                $run("$adb disconnect $serial");
                $messages[] = "✅ exec-out screencap saved to storage/{$path}";
                return response()->json([
                    'success' => true,
                    'message' => 'Screenshot captured via adb exec-out.',
                    'path'    => asset('storage/' . $path),
                    'method'  => 'adb_exec_out',
                    'messages' => $messages,
                ]);
            } catch (\Throwable $e) {
                $messages[] = "❌ Failed to write PNG from exec-out: " . $e->getMessage();
                // continue to fallback
            }
        } else {
            $messages[] = "⚠️ exec-out screencap failed or returned empty. stderr: {$capErr}";
        }

        // -------------------------
        // 2) Fallback: screenrecord -> pull -> ffmpeg extract frame
        // -------------------------
        $messages[] = "🔁 Trying fallback: screenrecord (short) -> pull -> ffmpeg extract a frame";

        // Confirm adb shell screenrecord exists (we'll attempt)
        $remoteMp4 = '/sdcard/__tmp_screenrec.mp4';
        [$shRecStatus, $shRecOut] = $run("$adb -s $serial shell \"ls /system/bin/screenrecord || ls /system/xbin/screenrecord || true\"");

        // Use time-limited screenrecord (3 seconds)
        $messages[] = "⏱ Recording 3 seconds on device: {$remoteMp4}";
        [$recStatus, $recOut] = $run("$adb -s $serial shell screenrecord --time-limit 3 {$remoteMp4}");
        if ($recStatus !== 0) {
            $messages[] = "❌ screenrecord command failed: {$recOut}";
        } else {
            // Pull the MP4 to a temp local file
            $localMp4   = $tmpDir . DIRECTORY_SEPARATOR . 'inv_' . $inventory->id . '_' . time() . '.mp4';
            $messages[] = "📤 Pulling recorded mp4 to local: {$localMp4}";
            [$pullStatus, $mp4binary, $pullErr] = $runBinary("$adb -s $serial pull {$remoteMp4} -");

            // clean remote file best-effort
            $run("$adb -s $serial shell rm -f {$remoteMp4}");

            if ($pullStatus === 0 && !empty($mp4binary)) {
                file_put_contents($localMp4, $mp4binary);
                $messages[] = "✅ Pulled mp4, size: " . filesize($localMp4);

                // Ensure ffmpeg exists
                if (!file_exists($ffmpegPath)) {
                    $messages[] = "⚠️ ffmpeg not found at {$ffmpegPath} — cannot extract frame. Install ffmpeg or try scrcpy.";
                } else {
                    $localPng = $tmpDir . DIRECTORY_SEPARATOR . 'inv_' . $inventory->id . '_' . time() . '.png';
                    $ffmpegCmd = escapeshellcmd($ffmpegPath) . ' -y -i ' . escapeshellarg($localMp4) . ' -frames:v 1 -q:v 2 ' . escapeshellarg($localPng) . ' 2>&1';
                    [$ffExit, $ffOut] = $run($ffmpegCmd);
                    if ($ffExit === 0 && file_exists($localPng) && filesize($localPng) > 200) {
                        // read png and store
                        $pngData = file_get_contents($localPng);
                        try {
                            $dir      = 'inventories/screenshots';
                            $filename = $inventory->id . '-' . now()->format('YmdHis') . '.png';
                            $path     = $dir . '/' . $filename;
                            Storage::disk('public')->put($path, $pngData);
                            $inventory->photo = $path;
                            $inventory->save();
                            // cleanup temp files
                            @unlink($localMp4);
                            @unlink($localPng);
                            $run("$adb disconnect $serial");
                            $messages[] = "✅ Extracted frame saved to storage/{$path}";
                            return response()->json([
                                'success' => true,
                                'message' => 'Screenshot captured via screenrecord + ffmpeg fallback.',
                                'path'    => asset('storage/' . $path),
                                'method'  => 'screenrecord+ffmpeg',
                                'messages' => $messages,
                            ]);
                        } catch (\Throwable $e) {
                            $messages[] = "❌ Failed to store extracted PNG: " . $e->getMessage();
                        }
                    } else {
                        $messages[] = "❌ ffmpeg frame extraction failed or created tiny file. ffmpeg output: {$ffOut}";
                        @unlink($localPng);
                    }
                }
                @unlink($localMp4);
            } else {
                $messages[] = "❌ adb pull failed or produced empty mp4. stderr: {$pullErr}";
            }
        }

        // -------------------------
        // 3) Optional: scrcpy record (if available) -> extract frame
        // -------------------------
        if (file_exists($scrcpyPath)) {
            $messages[] = "🔁 Trying scrcpy headless record -> extract frame (requires scrcpy installed)";

            $localRec = $tmpDir . DIRECTORY_SEPARATOR . 'inv_scrcpy_' . $inventory->id . '_' . time() . '.mp4';
            // -s target; --no-display runs headless; --record records to file
            $scrcpyCmd = escapeshellarg($scrcpyPath) . ' -s ' . escapeshellarg("{$deviceIP}:{$port}") . ' --no-display --record ' . escapeshellarg($localRec) . ' --max-size 1024 2>&1';
            [$scExit, $scOut] = $run($scrcpyCmd);
            if ($scExit === 0 && file_exists($localRec) && filesize($localRec) > 1000) {
                // extract frame with ffmpeg if available
                if (!file_exists($ffmpegPath)) {
                    $messages[] = "⚠️ ffmpeg required to extract frame from scrcpy recording but not found.";
                } else {
                    $localPng = $tmpDir . DIRECTORY_SEPARATOR . 'inv_scrcpy_' . $inventory->id . '_' . time() . '.png';
                    $ffmpegCmd = escapeshellcmd($ffmpegPath) . ' -y -i ' . escapeshellarg($localRec) . ' -frames:v 1 -q:v 2 ' . escapeshellarg($localPng) . ' 2>&1';
                    [$ffExit, $ffOut] = $run($ffmpegCmd);
                    if ($ffExit === 0 && file_exists($localPng) && filesize($localPng) > 200) {
                        $pngData = file_get_contents($localPng);
                        try {
                            $dir      = 'inventories/screenshots';
                            $filename = $inventory->id . '-' . now()->format('YmdHis') . '.png';
                            $path     = $dir . '/' . $filename;
                            Storage::disk('public')->put($path, $pngData);
                            $inventory->photo = $path;
                            $inventory->save();
                            @unlink($localRec);
                            @unlink($localPng);
                            $run("$adb disconnect $serial");
                            $messages[] = "✅ scrcpy -> ffmpeg frame saved to storage/{$path}";
                            return response()->json([
                                'success' => true,
                                'message' => 'Screenshot captured via scrcpy+ffmpeg fallback.',
                                'path'    => asset('storage/' . $path),
                                'method'  => 'scrcpy+ffmpeg',
                                'messages' => $messages,
                            ]);
                        } catch (\Throwable $e) {
                            $messages[] = "❌ Failed to store PNG from scrcpy: " . $e->getMessage();
                        }
                    } else {
                        $messages[] = "❌ scrcpy recording -> ffmpeg extraction failed. ffmpeg output: {$ffOut}";
                    }
                }
                @unlink($localRec);
            } else {
                $messages[] = "❌ scrcpy failed or produced nothing. output: {$scOut}";
            }
        } else {
            $messages[] = "ℹ️ scrcpy not installed at {$scrcpyPath} (skipping).";
        }

        // -------------------------
        // If we reach here, everything failed: probably hardware/DRM/secure overlay.
        // -------------------------
        $messages[] = "❌ All software capture attempts failed. This commonly happens when the device uses hardware overlays, DRM or FLAG_SECURE, or renders video directly to HDMI (framebuffer isn't readable).";

        // Disconnect and return detailed diagnostics to help you decide next steps
        $run("$adb disconnect $serial");

        $diagnosis = [
            'possibilities' => [
                'Device uses hardware/composer overlay or HDMI output -> screencap/screenrecord will produce black images.',
                'Content is DRM protected or flagged secure -> system prevents screenshots.',
                'ADB connection or binary mismatch problems (check adb version, keys, permissions).',
            ],
            'recommended_actions' => [
                'If you control the device firmware/app: avoid FLAG_SECURE or render into an accessible surface.',
                'Try a hardware HDMI capture device (reliable for HDMI output).',
                'Use vendor/SoC APIs (some boxes expose a capture API), or use minicap/scrcpy with matching native binaries for the device architecture.',
                'Ensure ffmpeg and scrcpy are installed on the server if you want to use the fallbacks.',
            ],
        ];

        return response()->json([
            'success' => false,
            'message' => 'Screenshot capture failed (see messages & diagnosis).',
            'messages' => $messages,
            'diagnosis' => $diagnosis,
        ], 500);
    }

    public function screenshot2(Inventory $inventory): JsonResponse
    {
        // ---------- CONFIG ----------
        $adbPath = '/usr/bin/adb';
        $homeDir = '/var/www';
        $keyDir  = '/var/www/.android';
        $port    = 5555;
        $ffmpeg  = '/usr/bin/ffmpeg';         // ffmpeg binary for HDMI capture fallback
        $v4lDevice = '/dev/video0';           // video device for capture card (adjust)
        $messages = [];

        $deviceIP = $inventory->box_ip;
        if (!$deviceIP) {
            return response()->json(['success' => false, 'message' => 'Box IP not found (set box_ip).'], 422);
        }

        // env for child processes
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        $run = function (string $cmd) use ($env) {
            $descriptors = [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $proc = proc_open($cmd . ' 2>&1', $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) {
                return [1, "Failed to start: {$cmd}"];
            }
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $exit = proc_close($proc);
            return [$exit, trim($out)];
        };

        $runBinary = function (string $cmd) use ($env) {
            $descriptors = [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) {
                return [1, null, "Failed to start: {$cmd}"];
            }
            $stdout = stream_get_contents($pipes[1]); // binary
            $stderr = stream_get_contents($pipes[2]); // text
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($proc);
            return [$exit, $stdout, trim($stderr)];
        };

        // ---------- 1) Try adb screencap (fast) ----------
        $messages[] = "Attempting ADB screenshot via {$deviceIP}:{$port}";

        if (!file_exists($adbPath)) {
            $messages[] = "ADB binary not found at {$adbPath}";
        } else {
            $adb = escapeshellarg($adbPath);
            $serial = escapeshellarg("{$deviceIP}:{$port}");

            // ensure disconnect (ignore errors)
            $run("{$adb} disconnect {$serial}");

            [$cStatus, $cOut] = $run("{$adb} connect {$serial}");
            if ($cStatus !== 0 || stripos($cOut, 'unable') !== false) {
                $messages[] = "ADB connect failed: {$cOut}";
            } else {
                $messages[] = "ADB connected: {$cOut}";

                // exec-out screencap binary to stdout
                $cmd = "{$adb} -s {$serial} exec-out screencap -p";
                [$capStatus, $png, $capErr] = $runBinary($cmd);

                if ($capStatus === 0 && $png) {
                    // normalize CRLF -> LF
                    $png = str_replace("\r\n", "\n", $png);

                    // Quick content check: many black screenshots are mostly zero bytes or very small entropy;
                    // we can't perfectly detect 'black', but we can still save and return it for debugging.
                    $dir = 'inventories/screenshots';
                    $filename = $inventory->id . '-adb-' . now()->format('YmdHis') . '.png';
                    $path = $dir . '/' . $filename;

                    try {
                        Storage::disk('public')->put($path, $png);
                        $inventory->photo = $path;
                        $inventory->save();

                        $messages[] = "Saved adb screenshot to storage/{$path}";
                        $run("{$adb} disconnect {$serial}");
                        return response()->json([
                            'success' => true,
                            'message' => 'Screenshot captured (adb).',
                            'path' => asset('storage/' . $path),
                            'method' => 'adb',
                            'messages' => $messages,
                        ]);
                    } catch (\Throwable $e) {
                        $messages[] = "Failed to save PNG: " . $e->getMessage();
                    }
                } else {
                    $messages[] = "ADB screencap exec-out failed. stderr/capErr: {$capErr}";
                    // Fallback attempt: use shell screencap to file and pull
                    $messages[] = "Trying fallback (screencap -> /sdcard -> pull)";

                    $tmpRemote = '/sdcard/__tmp_screen.png';
                    [$shStatus, $shOut] = $run("{$adb} -s {$serial} shell screencap -p {$tmpRemote}");
                    if ($shStatus === 0) {
                        [$pullStatus, $png, $pullErr] = $runBinary("{$adb} -s {$serial} pull {$tmpRemote} -");
                        $run("{$adb} -s {$serial} shell rm -f {$tmpRemote}");
                        if ($pullStatus === 0 && $png) {
                            $png = str_replace("\r\n", "\n", $png);
                            $dir = 'inventories/screenshots';
                            $filename = $inventory->id . '-adb-fallback-' . now()->format('YmdHis') . '.png';
                            $path = $dir . '/' . $filename;
                            try {
                                Storage::disk('public')->put($path, $png);
                                $inventory->photo = $path;
                                $inventory->save();
                                $messages[] = "Saved adb fallback screenshot to storage/{$path}";
                                $run("{$adb} disconnect {$serial}");
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Screenshot captured (adb fallback).',
                                    'path' => asset('storage/' . $path),
                                    'method' => 'adb-fallback',
                                    'messages' => $messages,
                                ]);
                            } catch (\Throwable $e) {
                                $messages[] = "Save failed: " . $e->getMessage();
                            }
                        } else {
                            $messages[] = "adb pull failed: {$pullErr}";
                        }
                    } else {
                        $messages[] = "screencap shell failed: {$shOut}";
                    }
                    $run("{$adb} disconnect {$serial}");
                }
            }
        }

        // If we reach here, ADB methods failed or produced unusable result.
        $messages[] = "ADB approach didn't produce a usable screenshot.";

        // ---------- 2) Try HDMI capture (ffmpeg) if a capture card is present ----------
        if (file_exists($ffmpeg) && file_exists($v4lDevice)) {
            $messages[] = "Attempting HDMI capture from {$v4lDevice} using ffmpeg";

            $dir = 'inventories/screenshots';
            $filename = $inventory->id . '-hdmi-' . now()->format('YmdHis') . '.png';
            $publicPath = storage_path('app/public/' . $dir);
            if (!is_dir($publicPath)) mkdir($publicPath, 0755, true);
            $tmpFile = $publicPath . '/' . $filename;

            // Build ffmpeg command:
            // - capture one frame (-vframes 1) from device
            // - adjust -video_size if your capture card reports a different default
            // You may need to set -video_size 1920x1080 or other sizes for your card.
            $cmd = escapeshellcmd($ffmpeg) . " -y -f video4linux2 -i " . escapeshellarg($v4lDevice) .
                " -vframes 1 " . escapeshellarg($tmpFile) . " 2>&1";

            [$exit, $out] = $run($cmd);
            if ($exit === 0 && file_exists($tmpFile)) {
                // Save to storage disk (public)
                $relPath = $dir . '/' . $filename;
                try {
                    Storage::disk('public')->put($relPath, file_get_contents($tmpFile));
                    // optional: set inventory photo
                    $inventory->photo = $relPath;
                    $inventory->save();
                    // cleanup temp file
                    @unlink($tmpFile);
                    $messages[] = "Saved HDMI screenshot to storage/{$relPath}";
                    return response()->json([
                        'success' => true,
                        'message' => 'Screenshot captured (hdmi/ffmpeg).',
                        'path' => asset('storage/' . $relPath),
                        'method' => 'hdmi',
                        'messages' => $messages,
                    ]);
                } catch (\Throwable $e) {
                    $messages[] = "Failed to save HDMI capture: " . $e->getMessage();
                }
            } else {
                $messages[] = "ffmpeg capture failed (exit={$exit}) output: {$out}";
            }
        } else {
            $messages[] = "FFmpeg or capture device not available (ffmpeg: " . (file_exists($ffmpeg) ? 'yes' : 'no') . ", device: " . (file_exists($v4lDevice) ? 'yes' : 'no') . ")";
        }

        // ---------- 3) Give up, return diagnostics ----------
        $messages[] = "No reliable screenshot could be produced. Likely reasons: protected/hardware-overlay content, device blocks surface capture, or no capture card / vendor API available.";

        return response()->json([
            'success' => false,
            'message' => 'Screenshot failed. See messages for diagnostics.',
            'messages' => $messages,
        ], 500);
    }

    public function screenshot3(Inventory $inventory): JsonResponse
    {
        // ─────────────────────────────────────────────────────────────
        // CONFIG (adjust to your server/device)
        // ─────────────────────────────────────────────────────────────
        $adbPath  = '/usr/bin/adb';
        $ffmpeg   = '/usr/bin/ffmpeg'; // optional (used for screenrecord fallback)
        $homeDir  = '/var/www';
        $keyDir   = '/var/www/.android';
        $port     = 5555;
        $useFfmpegFallback = file_exists($ffmpeg);

        // Quick sanity
        $deviceIP = $inventory->box_ip;
        if (!$deviceIP) {
            return response()->json(['success' => false, 'message' => 'Box IP not found (set box_ip).'], 422);
        }
        if (!file_exists($adbPath)) {
            return response()->json(['success' => false, 'message' => "ADB binary not found at: {$adbPath}"], 500);
        }

        // Build environment for child processes
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        $adb    = escapeshellarg($adbPath);
        $serial = escapeshellarg("{$deviceIP}:{$port}");

        // run (text) and runBinary (binary stdout) helpers
        $run = function(string $cmd) use ($env) : array {
            $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $proc = proc_open($cmd . ' 2>&1', $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) return [1, "Failed to start process: {$cmd}"];
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $exit = proc_close($proc);
            return [$exit, trim($out)];
        };

        $runBinary = function(string $cmd) use ($env) : array {
            $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
            if (!is_resource($proc)) return [1, null, 'Failed to start process'];
            $stdout = stream_get_contents($pipes[1]); // binary
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $exit = proc_close($proc);
            return [$exit, $stdout, trim($stderr)];
        };

        $messages = [];

        // Helper: check if PNG looks "mostly black" (sample pixels)
        $isMostlyBlack = function($pngData) {
            if (!$pngData) return true;
            $im = @imagecreatefromstring($pngData);
            if ($im === false) return true;
            $w = imagesx($im); $h = imagesy($im);
            // sample up to 500 points
            $samples = 500;
            $stepX = max(1, intval($w / sqrt($samples)));
            $stepY = max(1, intval($h / sqrt($samples)));
            $total = 0; $count = 0;
            for ($x = 0; $x < $w; $x += $stepX) {
                for ($y = 0; $y < $h; $y += $stepY) {
                    $rgb = imagecolorat($im, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    // perceived brightness
                    $brightness = ($r*299 + $g*587 + $b*114) / 1000;
                    $total += $brightness;
                    $count++;
                }
            }
            imagedestroy($im);
            $avg = $count ? ($total / $count) : 0;
            // threshold (0..255) - 15 is quite dark
            return ($avg < 15);
        };

        // 1) soft ping
        $messages[] = "🔍 Pinging {$deviceIP}…";
        $pingCmd = (stripos(PHP_OS, 'WIN') === 0) ? "ping -n 1 {$deviceIP}" : "/usr/bin/ping -c 1 {$deviceIP}";
        [$pingStatus, $pingOut] = $run($pingCmd);
        if ($pingStatus !== 0) {
            $messages[] = "⚠️ Ping failed (continuing anyway)";
            if ($pingOut) $messages[] = $pingOut;
        } else {
            $messages[] = "✅ Ping OK";
        }

        // 2) ADB connect
        $messages[] = "🔗 ADB connect to {$deviceIP}:{$port}";
        $run("$adb disconnect $serial"); // best-effort clear
        [$connStatus, $connOut] = $run("$adb connect $serial");
        if ($connStatus !== 0 || stripos($connOut, 'unable') !== false) {
            $messages[] = "❌ ADB connect failed";
            if ($connOut) $messages[] = $connOut;
            return response()->json(['success'=>false,'message'=>'ADB connect failed.','messages'=>$messages], 500);
        }
        $messages[] = "✅ ADB connected";

        // 2.1 Try to wake & unlock (best-effort)
        $messages[] = "🔁 Wake/unlock attempt";
        $run("$adb -s $serial shell input keyevent 224"); // WAKEUP
        $run("$adb -s $serial shell input keyevent 82");  // MENU (may dismiss lock)
        // Try dismiss-keyguard (may not be supported on older devices)
        $run("$adb -s $serial shell wm dismiss-keyguard || true");
        // short pause to let UI settle
        usleep(300000); // 300ms

        // 3) exec-out screencap
        $messages[] = "📸 screencap exec-out";
        $cmd = "$adb -s $serial exec-out screencap -p";
        [$capStatus, $png, $capErr] = $runBinary($cmd);

        // Normalize CRLF in PNG streams
        if ($png !== null) $png = str_replace("\r\n", "\n", $png);

        // If screencap failed or PNG appears empty -> fallback
        $needFallback = false;
        if ($capStatus !== 0 || !$png) {
            $messages[] = "ℹ️ exec-out screencap failed or returned no data: status={$capStatus}";
            if ($capErr) $messages[] = $capErr;
            $needFallback = true;
        } else {
            // quick check whether the PNG is mostly black
            if ($isMostlyBlack($png)) {
                $messages[] = "⚠️ Captured image appears mostly black — trying fallback methods";
                $needFallback = true;
            } else {
                $messages[] = "✅ screencap captured (looks OK)";
            }
        }

        // fallback: shell screencap -> pull OR screenrecord -> extract frame (if available)
        if ($needFallback) {
            // Try normal shell screencap -> /sdcard/__tmp_screen.png -> pull -
            $messages[] = "🔁 Trying shell screencap -> pull fallback";
            $tmpRemote = '/sdcard/__tmp_screen.png';
            [$shStatus, $shOut] = $run("$adb -s $serial shell screencap -p {$tmpRemote}");
            if ($shStatus === 0) {
                [$pullStatus, $png, $pullErr] = $runBinary("$adb -s $serial pull {$tmpRemote} -");
                // cleanup remote
                $run("$adb -s $serial shell rm -f {$tmpRemote}");
                if ($pullStatus === 0 && $png) {
                    $png = str_replace("\r\n", "\n", $png);
                    if (!$isMostlyBlack($png)) {
                        $messages[] = "✅ Pulled screenshot from device filesystem (looks OK)";
                        $needFallback = false;
                    } else {
                        $messages[] = "⚠️ Pulled screenshot still mostly black";
                    }
                } else {
                    $messages[] = "❌ adb pull failed";
                    if ($pullErr) $messages[] = $pullErr;
                }
            } else {
                $messages[] = "❌ shell screencap on device failed";
                if ($shOut) $messages[] = $shOut;
            }
        }

        // further fallback: screenrecord 1s -> pull -> ffmpeg extract frame (requires ffmpeg)
        if ($needFallback && $useFfmpegFallback) {
            $messages[] = "🔁 Trying screenrecord fallback (requires ffmpeg on server)";
            $tmpRemoteMp4 = '/sdcard/__tmp_rec.mp4';
            // record 1 second (some devices don't support --time-limit older android may require no param)
            [$recStatus, $recOut] = $run("$adb -s $serial shell screenrecord --time-limit 1 {$tmpRemoteMp4}");
            if ($recStatus === 0) {
                [$pullStatus, $mp4Data, $pullErr] = $runBinary("$adb -s $serial pull {$tmpRemoteMp4} -");
                // cleanup remote
                $run("$adb -s $serial shell rm -f {$tmpRemoteMp4}");
                if ($pullStatus === 0 && $mp4Data) {
                    // write to temp file, run ffmpeg to extract a frame
                    $tmpLocal = tempnam(sys_get_temp_dir(), 'inv_mp4_') . '.mp4';
                    file_put_contents($tmpLocal, $mp4Data);
                    $tmpOutPng = tempnam(sys_get_temp_dir(), 'inv_png_') . '.png';
                    // ffmpeg -y -i input -vframes 1 -q:v 2 out.png
                    $ffCmd = escapeshellarg($ffmpeg) . " -y -i " . escapeshellarg($tmpLocal) . " -vframes 1 -q:v 2 " . escapeshellarg($tmpOutPng) . " 2>&1";
                    [$ffStatus, $ffOut] = $run($ffCmd);
                    if ($ffStatus === 0 && file_exists($tmpOutPng)) {
                        $png = file_get_contents($tmpOutPng);
                        // cleanup locals
                        @unlink($tmpLocal); @unlink($tmpOutPng);
                        if (!$isMostlyBlack($png)) {
                            $messages[] = "✅ Extracted frame from screenrecord (looks OK)";
                            $needFallback = false;
                        } else {
                            $messages[] = "⚠️ Extracted frame still mostly black";
                        }
                    } else {
                        $messages[] = "❌ ffmpeg extraction failed";
                        if ($ffOut) $messages[] = $ffOut;
                        @unlink($tmpLocal);
                    }
                } else {
                    $messages[] = "❌ adb pull of mp4 failed";
                    if ($pullErr) $messages[] = $pullErr;
                }
            } else {
                $messages[] = "❌ screenrecord on device failed";
                if ($recOut) $messages[] = $recOut;
            }
        }

        if ($needFallback) {
            // disconnect and return debug info
            $run("$adb disconnect $serial");
            $messages[] = "❌ All capture attempts failed or returned black images. Possible reasons: device locked/off, or video surface is hardware-protected (DRM).";
            return response()->json([
                'success' => false,
                'message' => 'Screenshot capture failed or returned black image.',
                'messages' => $messages,
            ], 500);
        }

        // Save PNG to storage
        $dir      = 'inventories/screenshots';
        $filename = $inventory->id . '-' . now()->format('YmdHis') . '.png';
        $path     = $dir . '/' . $filename;
        try {
            Storage::disk('public')->put($path, $png);
        } catch (\Throwable $e) {
            $messages[] = "❌ Failed to write PNG: " . $e->getMessage();
            return response()->json(['success'=>false,'message'=>'Failed to save screenshot.','messages'=>$messages],500);
        }

        // Update inventory photo and disconnect
        $inventory->photo = $path;
        $inventory->save();
        $run("$adb disconnect $serial");
        $messages[] = "✅ Screenshot saved: storage/{$path}";

        return response()->json([
            'success' => true,
            'message' => 'Screenshot captured.',
            'path'    => asset('storage/' . $path),
            'method'  => 'adb',
            'messages'=> $messages,
        ]);
    }

    /**
     * Try to extract an IP (or host) from a management URL like
     * http(s)://10.0.0.5:8000 or 10.0.0.5, or return null.
     */
    private function extractIpFromUrl(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') return null;

        // If it already looks like plain IP/host[:port]
        if (!Str::startsWith($value, ['http://', 'https://'])) {
            // Strip any :port
            $host = preg_replace('~:\d+$~', '', $value);
            return $host ?: null;
        }

        // Parse from URL
        $parts = parse_url($value);
        if (!$parts || empty($parts['host'])) return null;
        return $parts['host'];
    }

    private function joinUrl(string $base, string $uri): string
    {
        if (Str::startsWith($uri, '/')) {
            return $base.$uri;
        }
        return $base.'/'.$uri;
    }

}
