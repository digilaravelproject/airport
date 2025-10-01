<?php
// app/Http/Controllers/UtilityController.php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string)$request->get('search', ''));

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

        // 2) Concurrent HTTP status checks (for boxes with mgmt_url)
        $httpTargets = [];
        foreach ($all as $inv) {
            if ($inv->mgmt_url) {
                $httpTargets[$inv->id] = rtrim($inv->mgmt_url, '/').'/system';
            }
        }

        $httpResults = [];
        if (!empty($httpTargets)) {
            // Build concurrent requests
            $responses = Http::timeout(3)
                ->withoutVerifying()
                ->pool(function ($pool) use ($all, $httpTargets) {
                    $reqs = [];
                    foreach ($all as $inv) {
                        if (!isset($httpTargets[$inv->id])) continue;
                        $req = Http::timeout(3)->withoutVerifying();
                        if (!empty($inv->mgmt_token)) {
                            $req = $req->withToken($inv->mgmt_token);
                        }
                        $reqs[$inv->id] = $pool->as((string)$inv->id)->get($httpTargets[$inv->id]);
                    }
                    return $reqs;
                });

            // Map back by inventory id
            foreach ($responses as $key => $resp) {
                // $key is the alias we set (string of id)
                if (method_exists($resp, 'successful') && $resp->successful()) {
                    $httpResults[(int)$key] = true; // online by HTTP
                } else {
                    $httpResults[(int)$key] = false; // not proven online by HTTP
                }
            }
        }

        // 3) Build final online list (HTTP success OR ICMP reachable)
        $online = collect();

        foreach ($all as $inv) {
            $isOnline = false;

            // If HTTP check exists for this ID, trust it
            if (array_key_exists($inv->id, $httpResults)) {
                $isOnline = $httpResults[$inv->id] === true;
            }

            // If HTTP didn’t prove online, try ICMP as fallback (only if we have IP)
            if (!$isOnline && $inv->box_ip) {
                $isWindows = str_starts_with(PHP_OS_FAMILY, 'Windows');
                $cmd = $isWindows
                    ? 'ping -n 1 -w 1000 ' . escapeshellarg($inv->box_ip)
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

        // 4) Optional: paginate *online* results in-memory (simpleLengthAwarePaginator)
        $perPage = 10;
        $page = max(1, (int)$request->get('page', 1));
        $slice = $online->slice(($page - 1) * $perPage, $perPage)->values();
        $inventories = new \Illuminate\Pagination\LengthAwarePaginator(
            $slice,
            $online->count(),
            $perPage,
            $page,
            ['path' => route('utility.online'), 'query' => $request->query()]
        );

        // 5) Selected inventory (if user clicked a row)
        $selectedInventory = null;
        if ($request->has('inventory_id')) {
            $selectedInventory = $online->firstWhere('id', (int)$request->get('inventory_id'));
        }

        $clients = Client::all();

        return view('utility.index', compact('inventories', 'selectedInventory', 'clients', 'search'));
    }
}
