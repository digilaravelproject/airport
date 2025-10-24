<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Client\Pool;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        // 1) Load all boxes (optionally filter by search)
        $all = Inventory::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('box_model', 'like', "%{$search}%")
                      ->orWhere('box_serial_no', 'like', "%{$search}%")
                      ->orWhere('box_mac', 'like', "%{$search}%")
                      ->orWhere('box_fw', 'like', "%{$search}%");
                });
            })
            ->with('client')
            ->orderBy('id')
            ->get();

        // Normalize mgmt_url => '/system'
        $httpTargets = [];
        foreach ($all as $inv) {
            if (!empty($inv->mgmt_url)) {
                $httpTargets[$inv->id] = rtrim($inv->mgmt_url, '/').'/system';
            }
        }

        // 2) Concurrent HTTP status checks for /system
        $httpResults = []; // id => true/false
        $responses = [];

        if (!empty($httpTargets)) {
            $responses = Http::timeout(3)
                ->withoutVerifying()
                ->pool(function (Pool $pool) use ($all, $httpTargets) {
                    $reqs = [];
                    foreach ($all as $inv) {
                        if (!isset($httpTargets[$inv->id])) {
                            continue;
                        }
                        $req = $pool->as((string) $inv->id);
                        if (!empty($inv->mgmt_token)) {
                            $req = $req->withToken($inv->mgmt_token);
                        }
                        $reqs[] = $req->get($httpTargets[$inv->id]);
                    }
                    return $reqs;
                });

            // Map back by alias (inventory id)
            foreach ($responses as $alias => $resp) {
                // $alias is the string alias set via ->as((string)$inv->id)
                $id = (int) $alias;
                $httpResults[$id] = (method_exists($resp, 'successful') && $resp->successful());
            }
        }

        // 2b) Fast sequential retry for /system/health if /system failed
        if (!empty($httpTargets)) {
            foreach ($all as $inv) {
                if (!isset($httpTargets[$inv->id])) {
                    continue;
                }
                if (!isset($httpResults[$inv->id]) || $httpResults[$inv->id] === false) {
                    // Try /system/health
                    $healthUrl = rtrim($inv->mgmt_url, '/').'/system/health';
                    $req = Http::timeout(2)->withoutVerifying();
                    if (!empty($inv->mgmt_token)) {
                        $req = $req->withToken($inv->mgmt_token);
                    }
                    try {
                        $resp = $req->get($healthUrl);
                        if ($resp->successful()) {
                            $httpResults[$inv->id] = true;
                        }
                    } catch (\Throwable $e) {
                        // ignore; will fall back to ICMP
                    }
                }
            }
        }

        // 3) Build final online list (HTTP success OR ICMP reachable)
        $online = collect();
        $isWindows = (strtoupper(PHP_OS_FAMILY) === 'WINDOWS');

        foreach ($all as $inv) {
            $isOnline = false;

            // If HTTP check exists for this ID, trust it
            if (array_key_exists($inv->id, $httpResults)) {
                $isOnline = ($httpResults[$inv->id] === true);
            }

            // If HTTP didn’t prove online, try ICMP as fallback (only if we have IP)
            if (!$isOnline && !empty($inv->box_ip)) {
                $cmd = $isWindows
                    ? 'ping -n 1 -w 1000 ' . escapeshellarg($inv->box_ip)   // 1 packet, 1s timeout
                    : 'ping -c 1 -W 1 '    . escapeshellarg($inv->box_ip);

                $out = [];
                $status = 1;
                @exec($cmd, $out, $status);
                $isOnline = ($status === 0);
            }

            if ($isOnline) {
                $online->push($inv);
            }
        }

        // 4) Paginate *online* results in-memory
        $perPage = (int) ($request->get('per_page', 10) ?: 10);
        $page    = max(1, (int) $request->get('page', 1));
        $slice   = $online->slice(($page - 1) * $perPage, $perPage)->values();

        $inventories = new LengthAwarePaginator(
            $slice,
            $online->count(),
            $perPage,
            $page,
            [
                'path'  => route('utility.online'), // <- make sure this route name exists
                'query' => $request->query(),
            ]
        );

        // 5) Selected inventory (if user clicked a row)
        $selectedInventory = null;
        if ($request->filled('inventory_id')) {
            $selectedInventory = $online->firstWhere('id', (int) $request->get('inventory_id'));
        }

        $clients = Client::all();

        return view('utility.index', compact('inventories', 'selectedInventory', 'clients', 'search'));
    }
}
