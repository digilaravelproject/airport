<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class InventoryOnlineService
{
    /**
     * Accepts a collection of Inventory models (with mgmt_url, mgmt_token, box_ip).
     * Returns an array of "online" inventory IDs.
     */
    public function detectOnline(Collection $inventories): array
    {
        // Normalize /system endpoints
        $httpTargets = [];
        foreach ($inventories as $inv) {
            if (!empty($inv->mgmt_url)) {
                $httpTargets[$inv->id] = rtrim($inv->mgmt_url, '/') . '/system';
            }
        }

        $httpResults = []; // id => true/false

        // Phase 1: concurrent GET to /system
        if (!empty($httpTargets)) {
            $responses = Http::timeout(3)
                ->withoutVerifying()
                ->pool(function (Pool $pool) use ($inventories, $httpTargets) {
                    $reqs = [];
                    foreach ($inventories as $inv) {
                        if (!isset($httpTargets[$inv->id])) {
                            continue;
                        }
                        $req = $pool->as((string)$inv->id);
                        if (!empty($inv->mgmt_token)) {
                            $req = $req->withToken($inv->mgmt_token);
                        }
                        $reqs[] = $req->get($httpTargets[$inv->id]);
                    }
                    return $reqs;
                });

            foreach ($responses as $alias => $resp) {
                $id = (int)$alias;
                $httpResults[$id] = (method_exists($resp, 'successful') && $resp->successful());
            }
        }

        // Phase 2: retry /system/health for failures
        if (!empty($httpTargets)) {
            foreach ($inventories as $inv) {
                if (!isset($httpTargets[$inv->id])) {
                    continue;
                }
                if (!isset($httpResults[$inv->id]) || $httpResults[$inv->id] === false) {
                    $healthUrl = rtrim($inv->mgmt_url, '/') . '/system/health';
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
                        // ignore, will try ICMP
                    }
                }
            }
        }

        // Phase 3: ICMP fallback per box_ip
        $isWindows = (strtoupper(PHP_OS_FAMILY) === 'WINDOWS');
        foreach ($inventories as $inv) {
            $knownHttp = array_key_exists($inv->id, $httpResults);
            $isOnline  = $knownHttp ? ($httpResults[$inv->id] === true) : false;

            if (!$isOnline && !empty($inv->box_ip)) {
                $cmd = $isWindows
                    ? 'ping -n 1 -w 1000 ' . escapeshellarg($inv->box_ip)
                    : 'ping -c 1 -W 1 '    . escapeshellarg($inv->box_ip);
                $out = [];
                $status = 1;
                @exec($cmd, $out, $status);
                $isOnline = ($status === 0);
            }

            $httpResults[$inv->id] = $isOnline;
        }

        // Return IDs that are online
        return collect($httpResults)
            ->filter(fn ($ok) => $ok === true)
            ->keys()
            ->map(fn ($k) => (int)$k)
            ->values()
            ->all();
    }
}
