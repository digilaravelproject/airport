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

    public function screenshot(Inventory $inventory): JsonResponse
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

    public function screenshot_old(Inventory $inventory)
    {
        if (!$inventory->mgmt_url) {
            return response()->json([
                'success' => false,
                'message' => 'Management URL not set for this inventory.',
            ], 422);
        }

        $base = rtrim($inventory->mgmt_url, '/');
        $client = new Client([
            'timeout' => 25,
            // If your management server has self-signed certs, you can set verify=>false.
            // Prefer proper certs in production.
            'verify'  => false,
        ]);

        $headersJson = ['Accept' => 'application/json'];
        $headersImg  = [];
        if ($inventory->mgmt_token) {
            $headersJson['Authorization'] = 'Bearer '.$inventory->mgmt_token;
            $headersImg['Authorization']  = 'Bearer '.$inventory->mgmt_token;
        }

        try {
            // 1) Ask the STB to capture a screenshot
            //    (per your swagger: POST /system/screenshot/capture)
            $resp = $client->post($base.'/system/screenshot/capture', [
                'headers' => $headersJson,
            ]);
            $payload = json_decode((string) $resp->getBody(), true) ?: [];

            // Figure out the image URL. Swagger examples show fields like "uri" or an ID.
            $imageUrl = null;

            if (!empty($payload['uri'])) {
                // Could be absolute or relative
                $imageUrl = Str::startsWith($payload['uri'], ['http://','https://'])
                    ? $payload['uri']
                    : $this->joinUrl($base, $payload['uri']);
            } else {
                // common fallback: /system/screenshot/{id}/image
                $id = $payload['id'] ?? $payload['uid'] ?? null;
                if ($id) {
                    $imageUrl = $base.'/system/screenshot/'.$id.'/image';
                }
            }

            if (!$imageUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Screenshot captured, but image URL was not returned.',
                    'raw'     => $payload,
                ], 500);
            }

            // 2) Download the raw image
            $imgResp = $client->get($imageUrl, ['headers' => $headersImg]);
            $mime = $imgResp->getHeaderLine('Content-Type') ?: 'image/jpeg';
            $ext  = $mime === 'image/png' ? 'png' : 'jpg';

            // 3) Save to public storage
            $dir = 'inventories/screenshots';
            $filename = $inventory->id.'-'.now()->format('YmdHis').'.'.$ext;
            $path = $dir.'/'.$filename;

            Storage::disk('public')->put($path, (string) $imgResp->getBody());

            // 4) Update inventory->photo (so your Photo preview shows it)
            $inventory->photo = $path;
            $inventory->save();

            return response()->json([
                'success' => true,
                'message' => 'Screenshot captured.',
                'path'    => asset('storage/'.$path),
                'method'  => 'management-api',
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $code = optional($e->getResponse())->getStatusCode();
            $body = optional($e->getResponse())->getBody();
            return response()->json([
                'success' => false,
                'message' => 'Screenshot failed.',
                'code'    => $code,
                'error'   => (string) $body,
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function joinUrl(string $base, string $uri): string
    {
        if (Str::startsWith($uri, '/')) {
            return $base.$uri;
        }
        return $base.'/'.$uri;
    }

}
