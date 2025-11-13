<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
        $search    = trim((string) $request->get('search', ''));
        $field     = $request->get('field', 'all'); // NEW: selected search field
        $perPage   = (int) ($request->get('per_page', 10) ?: 10);
        $page      = max(1, (int) $request->get('page', 1));

        $map = [
            'box_id'        => 'inventories.box_id',
            'box_model'     => 'inventories.box_model',
            'box_serial_no' => 'inventories.box_serial_no',
            'box_mac'       => 'inventories.box_mac',
            'box_ip'        => 'inventories.box_ip',
            'box_fw'        => 'inventories.box_fw',
            'client_name'   => 'clients.name',
            'id'            => 'inventories.id',
        ];
        $sort = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortColumn = $map[$sort] ?? $map['id'];

        $baseQuery = Inventory::query()
            ->with('client');

        // -------- Filter (Search with selectable field) --------
        if ($search !== '') {
            $allowed = ['box_id','box_model','box_serial_no','box_mac','box_ip','box_fw','client_name','all'];

            $field = in_array($field, $allowed, true) ? $field : 'all';

            if ($field === 'all') {
                $baseQuery->where(function ($q) use ($search) {
                    $q->where('box_model', 'like', "%{$search}%")
                      ->orWhere('box_serial_no', 'like', "%{$search}%")
                      ->orWhere('box_mac', 'like', "%{$search}%")
                      ->orWhere('box_fw', 'like', "%{$search}%")
                      ->orWhere('box_id', 'like', "%{$search}%")
                      ->orWhere('box_ip', 'like', "%{$search}%");
                })->orWhereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            } elseif ($field === 'client_name') {
                // Search by related client name
                $baseQuery->whereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            } else {
                // Direct inventory column search
                $baseQuery->where($field, 'like', "%{$search}%");
            }
        }

        // If sorting by client name, join clients (keeps existing behavior)
        if ($sort === 'client_name') {
            $baseQuery->leftJoin('clients', 'clients.id', '=', 'inventories.client_id')
                      ->select('inventories.*');
        }

        $baseQuery->orderBy($sortColumn, $direction);

        $inventories = $baseQuery
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends($request->query());

        $currentPageItems = collect($inventories->items());

        // -------- ADB CONFIG --------
        $adbPath = '/usr/bin/adb';
        $homeDir = '/var/www';
        $keyDir  = '/var/www/.android';
        $port    = 5555;
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];
        $run = function (string $cmd) use ($env): array {
            $descriptorspec = [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
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

        $adbCheckOnline = function (?string $ip) use ($adbPath, $port, $run): bool {
            if (empty($ip)) {
                return false;
            }
            $adb    = escapeshellarg($adbPath);
            $serial = escapeshellarg("{$ip}:{$port}");
            $run("$adb disconnect $serial");
            [$cStatus, $cOut] = $run("$adb connect $serial");
            $joined = strtolower(implode("\n", $cOut));
            if ($cStatus !== 0 || str_contains($joined, 'unable') || str_contains($joined, 'failed')) {
                return false;
            }
            [$sStatus, $sOut] = $run("$adb -s $serial shell getprop sys.boot_completed");
            if ($sStatus === 0 && trim(implode("\n", $sOut)) !== '') {
                $val = trim($sOut[0] ?? '');
                if ($val === '1' || $val === 'true' || $val === '1\r' || $val === '1\n') {
                    return true;
                }
            }
            [$eStatus, $eOut] = $run("$adb -s $serial shell echo ok");
            return ($eStatus === 0 && str_contains(strtolower(implode("\n", $eOut)), 'ok'));
        };

        foreach ($currentPageItems as $inv) {
            $isOnline = false;
            if ($adbAvailable && $keysOk) {
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
                    $isOnline = false;
                }
            }
            $inv->is_online = (bool) $isOnline;
        }

        $selectedInventory = null;
        if ($request->filled('inventory_id')) {
            $selectedInventory = $currentPageItems->firstWhere('id', (int) $request->get('inventory_id'))
                ?: Inventory::with('client')->find((int) $request->get('inventory_id'));
        }

        $clients = Client::all();

        return view('utility.index', [
            'inventories'       => $inventories,
            'selectedInventory' => $selectedInventory,
            'clients'           => $clients,
            'search'            => $search,
            'sort'              => $sort,
            'direction'         => $direction,
        ]);
    }
}
