<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
        $search    = trim((string) $request->get('search', ''));
        $perPage   = (int) ($request->get('per_page', 10) ?: 10);
        $page      = max(1, (int) $request->get('page', 1));

        // --- Sorting map (DB columns) ---
        // Note: "Status" (online/offline) is computed at runtime per current page,
        // so we do NOT provide DB-level sorting for it to avoid confusing results.
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

        // Paginate (drives which rows we ping)
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
