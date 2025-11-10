<?php

namespace App\Services;

use Illuminate\Support\Collection;

class InventoryOnlineService
{
    /**
     * Where adb + keys live on your server.
     * Adjust if your paths differ.
     */
    private string $adbPath = '/usr/bin/adb';
    private string $homeDir = '/var/www';
    private string $keyDir  = '/var/www/.android';
    private int    $defaultAdbPort = 5555;

    /**
     * Public API kept for backward compatibility with your controller.
     * Returns ONLY the list of online inventory IDs.
     */
    public function detectOnline(Collection $inventories): array
    {
        $map = $this->detectOnlineWithMessages($inventories);
        return collect($map)
            ->filter(fn ($v) => ($v['online'] ?? false) === true)
            ->keys()
            ->map(fn ($k) => (int)$k)
            ->values()
            ->all();
    }

    /**
     * Extended version that also returns per-device logs you can show in the view.
     * Return shape: [ id => ['online' => bool, 'messages' => string[]] ].
     */
    public function detectOnlineWithMessages(Collection $inventories): array
    {
        $result = [];

        // Basic sanity checks once
        if (!file_exists($this->adbPath)) {
            foreach ($inventories as $inv) {
                $result[$inv->id] = [
                    'online'   => false,
                    'messages' => ["❌ ADB binary not found at: {$this->adbPath}"],
                ];
            }
            return $result;
        }
        if (!is_dir($this->keyDir)) {
            foreach ($inventories as $inv) {
                $result[$inv->id] = [
                    'online'   => false,
                    'messages' => ["❌ ADB key directory not found at: {$this->keyDir}"],
                ];
            }
            return $result;
        }

        $env = [
            'HOME'            => $this->homeDir,
            'ADB_VENDOR_KEYS' => $this->keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        $run = function (string $cmd) use ($env): array {
            $proc = proc_open($cmd . ' 2>&1', [
                1 => ['pipe', 'w'], // stdout+stderr (merged)
                2 => ['pipe', 'w'],
            ], $pipes, null, $env);

            if (!is_resource($proc)) {
                return [1, ['Failed to start process']];
            }
            $out = stream_get_contents($pipes[1]) ?: '';
            // close pipes
            foreach ($pipes as $p) { if (is_resource($p)) fclose($p); }
            $status = proc_close($proc);
            return [$status, preg_split('/\R/', trim($out)) ?: []];
        };

        $adb = escapeshellarg($this->adbPath);
        $isWindows = (strtoupper(PHP_OS_FAMILY) === 'WINDOWS');

        foreach ($inventories as $inv) {
            $id        = (int) $inv->id;
            $ip        = (string) ($inv->box_ip ?? '');
            $port      = (int)   ($inv->adb_port ?? $this->defaultAdbPort);
            $serial    = escapeshellarg("{$ip}:{$port}");
            $messages  = [];
            $online    = false;

            if (empty($ip)) {
                $result[$id] = ['online' => false, 'messages' => ['⚠️ Missing box_ip']];
                continue;
            }

            // 1) Soft ICMP ping (optional but quick)
            $messages[] = "🔍 Pinging $ip ...";
            $pingCmd = $isWindows
                ? "ping -n 1 -w 1000 " . escapeshellarg($ip)
                : "/usr/bin/ping -c 1 " . escapeshellarg($ip);
            [$pStat, $pOut] = $run($pingCmd);
            if ($pStat !== 0) {
                $messages[] = "⚠️ Ping failed (continuing with ADB)";
            } else {
                $messages[] = "✅ Ping responded";
            }
            if (!empty($pOut)) { $messages = array_merge($messages, $pOut); }

            // 2) ADB connect
            $messages[] = "🔗 ADB connect to {$ip}:{$port} ...";
            $run("$adb disconnect $serial"); // ignore result
            [$cStat, $cOut] = $run("$adb connect $serial");
            if ($cStat !== 0) {
                $messages[] = "❌ ADB connect failed";
                if (!empty($cOut)) { $messages = array_merge($messages, $cOut); }
                // still continue to try get-state (sometimes connect returns nonzero but connects)
            } else {
                $messages[] = "✅ ADB connect attempted";
                if (!empty($cOut)) { $messages = array_merge($messages, $cOut); }
            }

            // 3) ADB get-state (reliable check)
            $messages[] = "📟 Checking ADB state ...";
            [$sStat, $sOut] = $run("$adb -s $serial get-state");
            $state = strtolower(trim(implode("\n", $sOut)));
            if ($sStat === 0 && str_contains($state, 'device')) {
                $online = true;
                $messages[] = "✅ ADB state: device";
            } else {
                $messages[] = "❌ ADB state check failed";
                if (!empty($sOut)) { $messages = array_merge($messages, $sOut); }
            }

            $result[$id] = [
                'online'   => $online,
                'messages' => $messages,
            ];
        }

        return $result;
    }
}
