<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Str;

class UtilityController extends Controller
{
    
    public function index(Request $request)
    {
        $search    = trim((string) $request->get('search', ''));
        $perPage   = (int) ($request->get('per_page', 10) ?: 10);
        $page      = max(1, (int) $request->get('page', 1));

        // --- Sorting map (DB columns) ---
        $map = [
            'box_id'        => 'inventories.box_id',
            'box_model'     => 'inventories.box_model',
            'box_serial_no' => 'inventories.box_serial_no',
            'box_mac'       => 'inventories.box_mac',
            'box_fw'        => 'inventories.box_fw',
            'client_name'   => 'clients.name',
            'id'            => 'inventories.id', // fallback
        ];
        $sort = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortColumn = $map[$sort] ?? $map['id'];

        // Base query (+search)
        $baseQuery = Inventory::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('box_model', 'like', "%{$search}%")
                        ->orWhere('box_serial_no', 'like', "%{$search}%")
                        ->orWhere('box_mac', 'like', "%{$search}%")
                        ->orWhere('box_fw', 'like', "%{$search}%")
                        ->orWhere('box_id', 'like', "%{$search}%");
                });
            })
            ->with('client');

        // If sorting by client_name, LEFT JOIN clients for ordering
        if ($sort === 'client_name') {
            $baseQuery->leftJoin('clients', 'clients.id', '=', 'inventories.client_id')
                      ->select('inventories.*');
        }

        // Apply sorting
        $baseQuery->orderBy($sortColumn, $direction);

        // Paginate
        $inventories = $baseQuery->paginate($perPage, ['*'], 'page', $page)->appends($request->query());

        // Current page items
        $currentPageItems = collect($inventories->items());

        /**
         * -------- ADB CONFIG & HELPERS (no curl/guzzle) --------
         */
        $adbPath = '/usr/bin/adb';
        $homeDir = '/var/www';
        $keyDir  = '/var/www/.android';
        $port    = 5555;

        // Build a clean env for the child processes
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        // Minimal runner that captures stdout+stderr
        $run = function (string $cmd) use ($env): array {
            $descriptorspec = [
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];
            $process = @proc_open($cmd . ' 2>&1', $descriptorspec, $pipes, null, $env);
            if (!is_resource($process)) {
                return [1, ['Failed to start process']];
            }
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $status = proc_close($process);
            return [$status, explode("\n", trim((string) $output))];
        };

        $adbAvailable = file_exists($adbPath) && is_executable($adbPath);
        $keysOk       = is_dir($keyDir);

        // Helper: check if a device is online via ADB (no file writes)
        $adbCheckOnline = function (?string $ip) use ($adbPath, $port, $run): bool {
            if (empty($ip)) {
                return false;
            }
            $adb    = escapeshellarg($adbPath);
            $serial = escapeshellarg("{$ip}:{$port}");

            // clean connect
            $run("$adb disconnect $serial");
            [$cStatus, $cOut] = $run("$adb connect $serial");
            $joined = strtolower(implode("\n", $cOut));
            if ($cStatus !== 0 || str_contains($joined, 'unable') || str_contains($joined, 'failed')) {
                return false;
            }

            // quick lightweight shell check
            // 1) getprop sys.boot_completed (returns 1 when boot finished)
            [$sStatus, $sOut] = $run("$adb -s $serial shell getprop sys.boot_completed");
            if ($sStatus === 0 && trim(implode("\n", $sOut)) !== '') {
                $val = trim($sOut[0] ?? '');
                if ($val === '1' || $val === 'true' || $val === '1\r' || $val === '1\n') {
                    return true;
                }
            }
            // 2) fallback tiny echo
            [$eStatus, $eOut] = $run("$adb -s $serial shell echo ok");
            return ($eStatus === 0 && str_contains(strtolower(implode("\n", $eOut)), 'ok'));
        };

        /**
         * Mark is_online using ONLY ADB (no HTTP pool, no cURL/Guzzle).
         * If ADB binary or keys are missing, everything will be false (safe).
         */
        foreach ($currentPageItems as $inv) {
            $isOnline = false;

            if ($adbAvailable && $keysOk) {
                // Prefer explicit box_ip; if not present, try to derive from mgmt_url host
                $ip = null;
                if (!empty($inv->box_ip)) {
                    $ip = $inv->box_ip;
                } elseif (!empty($inv->mgmt_url)) {
                    $host = parse_url($inv->mgmt_url, PHP_URL_HOST);
                    if (filter_var($host, FILTER_VALIDATE_IP)) {
                        $ip = $host;
                    }
                }

                try {
                    $isOnline = $adbCheckOnline($ip);
                } catch (\Throwable $e) {
                    $isOnline = false; // swallow, don't break the page
                }
            }

            $inv->is_online = (bool) $isOnline;
        }

        // Selected inventory (unchanged)
        $selectedInventory = null;
        if ($request->filled('inventory_id')) {
            $selectedInventory = $currentPageItems->firstWhere('id', (int) $request->get('inventory_id'))
                ?: Inventory::with('client')->find((int) $request->get('inventory_id'));
        }

        // (Optional) pre-fill active channel if a single row is selected (unchanged)
        $selectedActiveChannel = null;
        if ($selectedInventory) {
            try {
                $selectedActiveChannel = $this->discoverActiveChannel($selectedInventory);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $clients = Client::all();

        return view('utility.index', [
            'inventories'           => $inventories,
            'selectedInventory'     => $selectedInventory,
            'clients'               => $clients,
            'search'                => $search,
            'sort'                  => $sort,
            'direction'             => $direction,
            'selectedActiveChannel' => $selectedActiveChannel,
        ]);
    }
    public function index_old(Request $request)
    {
        $search    = trim((string) $request->get('search', ''));
        $perPage   = (int) ($request->get('per_page', 10) ?: 10);
        $page      = max(1, (int) $request->get('page', 1));

        // --- Sorting map (DB columns) ---
        $map = [
            'box_id'        => 'inventories.box_id',
            'box_model'     => 'inventories.box_model',
            'box_serial_no' => 'inventories.box_serial_no',
            'box_mac'       => 'inventories.box_mac',
            'box_fw'        => 'inventories.box_fw',
            'client_name'   => 'clients.name',
            'id'            => 'inventories.id', // fallback
        ];
        $sort = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortColumn = $map[$sort] ?? $map['id'];

        // Base query (+search)
        $baseQuery = Inventory::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('box_model', 'like', "%{$search}%")
                      ->orWhere('box_serial_no', 'like', "%{$search}%")
                      ->orWhere('box_mac', 'like', "%{$search}%")
                      ->orWhere('box_fw', 'like', "%{$search}%")
                      ->orWhere('box_id', 'like', "%{$search}%");
                });
            })
            ->with('client');

        // If sorting by client_name, LEFT JOIN clients for ordering
        if ($sort === 'client_name') {
            $baseQuery->leftJoin('clients', 'clients.id', '=', 'inventories.client_id')
                      ->select('inventories.*');
        }

        // Apply sorting
        $baseQuery->orderBy($sortColumn, $direction);

        // Paginate
        $inventories = $baseQuery->paginate($perPage, ['*'], 'page', $page)->appends($request->query());

        // Current page items
        $currentPageItems = collect($inventories->items());

        // Normalize mgmt_url => '/system'
        $httpTargets = [];
        foreach ($currentPageItems as $inv) {
            if (!empty($inv->mgmt_url)) {
                $httpTargets[$inv->id] = rtrim($inv->mgmt_url, '/') . '/system';
            }
        }

        // Concurrent HTTP /system checks
        $httpResults = []; // id => bool
        if (!empty($httpTargets)) {
            $responses = Http::timeout(3)
                ->withoutVerifying()
                ->pool(function (Pool $pool) use ($currentPageItems, $httpTargets) {
                    $reqs = [];
                    foreach ($currentPageItems as $inv) {
                        if (!isset($httpTargets[$inv->id])) continue;
                        $req = $pool->as((string) $inv->id);
                        if (!empty($inv->mgmt_token)) $req = $req->withToken($inv->mgmt_token);
                        $reqs[] = $req->get($httpTargets[$inv->id]);
                    }
                    return $reqs;
                });

            foreach ($responses as $alias => $resp) {
                $id = (int) $alias;
                $httpResults[$id] = (method_exists($resp, 'successful') && $resp->successful());
            }
        }

        // Quick retry /system/health if /system failed
        if (!empty($httpTargets)) {
            foreach ($currentPageItems as $inv) {
                if (!isset($httpTargets[$inv->id])) continue;
                if (!isset($httpResults[$inv->id]) || $httpResults[$inv->id] === false) {
                    $healthUrl = rtrim($inv->mgmt_url, '/') . '/system/health';
                    $req = Http::timeout(2)->withoutVerifying();
                    if (!empty($inv->mgmt_token)) $req = $req->withToken($inv->mgmt_token);
                    try {
                        $resp = $req->get($healthUrl);
                        if ($resp->successful()) $httpResults[$inv->id] = true;
                    } catch (\Throwable $e) { /* ignore */ }
                }
            }
        }

        // ICMP fallback for current page
        $isWindows = (strtoupper(PHP_OS_FAMILY) === 'WINDOWS');
        foreach ($currentPageItems as $inv) {
            $isOnline = $httpResults[$inv->id] ?? false;
            if (!$isOnline && !empty($inv->box_ip)) {
                $cmd = $isWindows
                    ? 'ping -n 1 -w 1000 ' . escapeshellarg($inv->box_ip)
                    : 'ping -c 1 -W 1 '    . escapeshellarg($inv->box_ip);
                $out = []; $status = 1;
                @exec($cmd, $out, $status);
                $isOnline = ($status === 0);
            }
            $inv->is_online = $isOnline;
        }

        // Selected inventory (unchanged)
        $selectedInventory = null;
        if ($request->filled('inventory_id')) {
            $selectedInventory = $currentPageItems->firstWhere('id', (int) $request->get('inventory_id'))
                ?: Inventory::with('client')->find((int) $request->get('inventory_id'));
        }

        // (Optional) pre-fill active channel if a single row is selected (we won't block/slow page render)
        $selectedActiveChannel = null;
        if ($selectedInventory) {
            try {
                $selectedActiveChannel = $this->discoverActiveChannel($selectedInventory);
            } catch (\Throwable $e) {
                // ignore – right panel will stay empty and user can click Play to fetch on-demand
            }
        }

        $clients = Client::all();

        return view('utility.index', [
            'inventories'           => $inventories,
            'selectedInventory'     => $selectedInventory,
            'clients'               => $clients,
            'search'                => $search,
            'sort'                  => $sort,
            'direction'             => $direction,
            'selectedActiveChannel' => $selectedActiveChannel, // new
        ]);
    }

    /**
     * NEW: JSON endpoint to get the currently active channel URL for an inventory.
     */
    public function activeChannel(Request $request, Inventory $inventory)
    {
        try {
            $url = $this->discoverActiveChannel($inventory);

            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active channel not found for this device.',
                ], 404);
            }

            // (Optional) basic sanitation: allow only http(s), udp, rtp, rtsp
            if (!preg_match('#^(https?|udp|rtp|rtsp)://#i', $url)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The device returned an unsupported URL scheme.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'url'     => $url,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to discover active channel.',
                'error'   => app()->isProduction() ? null : $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Try to discover the active channel URL from the device mgmt API.
     * Adjust endpoints as per your device firmware:
     *   - /system/active-channel
     *   - /system/channel
     *   - /current-channel
     * Or fall back to $inventory->stream_url if you store it in DB.
     */
    protected function discoverActiveChannel(Inventory $inventory): ?string
    {
        // If you already store a channel URL on the inventory record:
        if (!empty($inventory->stream_url)) {
            return $inventory->stream_url;
        }

        if (empty($inventory->mgmt_url)) {
            return null;
        }

        $base = rtrim($inventory->mgmt_url, '/');

        // Common candidates to try
        $paths = [
            '/system/active-channel',
            '/system/channel',
            '/current-channel',
            '/api/v1/channel/active',
        ];

        foreach ($paths as $p) {
            try {
                $req = Http::timeout(3)->withoutVerifying();
                if (!empty($inventory->mgmt_token)) {
                    $req = $req->withToken($inventory->mgmt_token);
                }
                $resp = $req->get($base . $p);

                if ($resp->successful()) {
                    // Accept either plain string or JSON {url: "..."} or {stream_url: "..."}
                    $json = null;
                    $body = trim((string) $resp->body());

                    // JSON?
                    if (Str::startsWith($body, '{') || Str::startsWith($body, '[')) {
                        $json = $resp->json();
                        if (is_array($json)) {
                            if (!empty($json['url']))        return (string) $json['url'];
                            if (!empty($json['stream_url'])) return (string) $json['stream_url'];
                            if (!empty($json['channel']))    return (string) $json['channel'];
                        }
                    } else {
                        // Plain text URL
                        if (preg_match('#^(https?|udp|rtp|rtsp)://.+#i', $body)) {
                            return $body;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // try next
            }
        }

        return null;
    }
}
