<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

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

    private function rebootViaAdb(string $deviceIP): array
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
