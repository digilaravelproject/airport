<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryActionController extends Controller
{
    private function base(?string $url): ?string
    {
        return $url ? rtrim($url, '/') : null;
    }

    /** GET {mgmt_url}/system or ICMP ping fallback */
    public function ping(Request $request, Inventory $inventory)
    {
        $base = $this->base($inventory->mgmt_url);
        $ip   = $inventory->box_ip;

        if (!$base && !$ip) {
            return response()->json(['success' => false, 'message' => 'No management URL or IP configured.'], 422);
        }

        if ($base) {
            try {
                $http = Http::timeout(3)->withoutVerifying();
                if (!empty($inventory->mgmt_token)) {
                    $http = $http->withToken($inventory->mgmt_token);
                }
                $resp = $http->get($base . '/system');
                if ($resp->successful()) {
                    return response()->json([
                        'success' => true,
                        'method'  => 'http',
                        'status'  => 'online',
                        'raw'     => $resp->json() ?? $resp->body(),
                    ]);
                }

                // HTTP responded but not 2xx — surface info
                return response()->json([
                    'success' => false,
                    'method'  => 'http',
                    'message' => 'HTTP status check failed',
                    'code'    => $resp->status(),
                    'raw'     => $resp->body(),
                ], 200);
            } catch (\Throwable $e) {
                Log::info('HTTP status check failed, trying ICMP: ' . $e->getMessage());
            }
        }

        if ($ip) {
            $isWindows = str_starts_with(PHP_OS_FAMILY, 'Windows');
            $cmd = $isWindows
                ? 'ping -n 1 -w 1000 ' . escapeshellarg($ip)
                : 'ping -c 1 -W 1 '    . escapeshellarg($ip);

            $output = [];
            $status = 1;
            @exec($cmd, $output, $status);

            return response()->json([
                'success' => $status === 0,
                'method'  => 'icmp',
                'status'  => $status === 0 ? 'online' : 'offline',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'HTTP failed and no IP for ICMP.'], 502);
    }

    /** POST {mgmt_url}/system/reboot (with fallbacks) */
    public function reboot(Request $request, Inventory $inventory)
    {
        $base = $this->base($inventory->mgmt_url);
        if (!$base) {
            return response()->json(['success' => false, 'message' => 'No management URL configured for reboot.'], 422);
        }

        // Try common reboot endpoints if exact path is unknown
        $paths = [
            '/system/reboot',
            '/reboot',
            '/device/reboot',
            '/command/reboot',
        ];

        try {
            $http = Http::timeout(6)->withoutVerifying();
            if (!empty($inventory->mgmt_token)) {
                $http = $http->withToken($inventory->mgmt_token);
            }

            $lastError = null;

            foreach ($paths as $p) {
                $url = $base . $p;
                try {
                    $resp = $http->asJson()->post($url, []);
                    if ($resp->successful()) {
                        return response()->json([
                            'success' => true,
                            'message' => "Reboot command sent via {$p}.",
                            'code'    => $resp->status(),
                            'raw'     => $resp->json() ?? $resp->body(),
                        ]);
                    }
                    $lastError = [
                        'code' => $resp->status(),
                        'body' => $resp->body(),
                        'path' => $p
                    ];
                } catch (\Throwable $e) {
                    $lastError = ['exception' => $e->getMessage(), 'path' => $p];
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Device did not accept reboot on any known endpoint.',
                'details' => $lastError,
            ], 502);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to contact device reboot endpoint.',
                'error'   => $e->getMessage(),
            ], 502);
        }
    }
}
