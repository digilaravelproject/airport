<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class InventoryActionController extends Controller
{
    private function base(?string $url): ?string
    {
        return $url ? rtrim($url, '/') : null;
    }

    /** GET {mgmt_url}/system (or /system/health); fallback to ICMP on box_ip */
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

    /** POST {mgmt_url}/system/reboot with a few fallback paths */
    public function reboot(Request $request, Inventory $inventory)
    {
        $base = $this->base($inventory->mgmt_url);
        if (!$base) {
            return response()->json([
                'success' => false,
                'message' => 'No management URL configured for reboot.'
            ], 422);
        }

        $endpoint = rtrim($base, '/') . '/system/reboot';

        try {
            $http = Http::timeout(10)->withoutVerifying();
            if (!empty($inventory->mgmt_token)) {
                $http = $http->withToken($inventory->mgmt_token);
            }

            $response = $http->acceptJson()->post($endpoint);

            if ($response->successful() || in_array($response->status(), [200, 201, 202, 204])) {
                // Success: reboot command accepted
                return response()->json([
                    'success' => true,
                    'message' => 'Reboot command sent successfully.',
                    'code'    => $response->status(),
                    'raw'     => $response->json() ?? $response->body(),
                ]);
            }

            // Device responded but not success
            return response()->json([
                'success' => false,
                'message' => 'Device responded with an unexpected status.',
                'code'    => $response->status(),
                'raw'     => $response->body(),
            ], 502);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Laravel HTTP client exceptions
            return response()->json([
                'success' => false,
                'message' => 'HTTP request failed: ' . $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        } catch (\Throwable $e) {
            // Catch-all for any other error
            return response()->json([
                'success' => false,
                'message' => 'Reboot failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function screenshot(Inventory $inventory)
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
