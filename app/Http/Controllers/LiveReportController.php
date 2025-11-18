<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Inventory;
use App\Models\Channel;
use App\Services\InventoryOnlineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PDF;
use Throwable;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LiveReportController extends Controller
{
    public function __construct(private InventoryOnlineService $onlineService) {}

    /**
     * Online/Offline listing (ADB-based), with search/sort/pagination.
     * Populates $inventory->stream_status and $inventory->resolved_channel_name when possible.
     */
    public function index(Request $request)
    {
        $search    = trim((string) $request->get('search', ''));
        $perPage   = (int) ($request->get('per_page', 10) ?: 10);
        $page      = max(1, (int) $request->get('page', 1));

        $map = [
            'box_id'        => 'inventories.box_id',
            'box_model'     => 'inventories.box_model',
            'box_serial_no' => 'inventories.box_serial_no',
            'box_mac'       => 'inventories.box_mac',
            'box_fw'        => 'inventories.box_fw',
            'client_name'   => 'clients.name',
            'id'            => 'inventories.id',
        ];
       
        if ($request->has('sort')) {
            $sort = $request->get('sort', 'id');
            $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        } else {
            $sort = 'box_id';
            $direction = 'asc';
        }

        $sortCol   = $map[$sort] ?? $map['id'];

        // Build base query: include client relationship (for searching by client name)
        $baseQuery = Inventory::query()
            ->with('client');

        // NOTE: removed `channel_source_in` from DB search because the inventories table
        // does not have that column (it was causing SQLSTATE[42S22] errors).
        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('box_model', 'like', "%{$search}%")
                  ->orWhere('box_serial_no', 'like', "%{$search}%")
                  ->orWhere('box_mac', 'like', "%{$search}%")
                  ->orWhere('box_fw', 'like', "%{$search}%")
                  ->orWhere('box_id', 'like', "%{$search}%")
                  ->orWhere('box_ip', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('stream_url', 'like', "%{$search}%")
                  // search client name via relationship
                  ->orWhereHas('client', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($sort === 'client_name') {
            $baseQuery->leftJoin('clients', 'clients.id', '=', 'inventories.client_id')
                      ->select('inventories.*');
        }

        $baseQuery->orderBy($sortCol, $direction);

        $inventories = $baseQuery
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends($request->query());

        $currentPage = collect($inventories->items());

        // mark online using existing service
        $onlineIds = $this->onlineService->detectOnline(
            $currentPage->map(fn ($inv) => (object) [
                'id'       => $inv->id,
                'box_ip'   => $inv->box_ip,
                'adb_port' => $inv->adb_port ?? null,
            ])
        );

        foreach ($currentPage as $inv) {
            $inv->is_online = in_array($inv->id, $onlineIds, true);
        }

        // Attempt best-effort initial discovery to populate stream_status & resolved_channel_name
        foreach ($currentPage as $inv) {
            $inv->stream_status = $inv->stream_status ?? null;
            $inv->resolved_channel_name = null;

            if (!empty($inv->is_online)) {
                try {
                    $res = $this->discoverActiveChannelAdb($inv);
                    if (is_array($res)) {
                        if (!empty($res['stream_status'])) $inv->stream_status = $res['stream_status'];
                        if (!empty($res['name'])) $inv->active_channel_name = $inv->active_channel_name ?? $res['name'];
                        if (!empty($res['channel_name'])) $inv->resolved_channel_name = $res['channel_name'];
                        if (!empty($res['raw_source'])) $inv->channel_source_in = $inv->channel_source_in ?? $res['raw_source'];
                    }
                } catch (Throwable $e) {
                    // ignore
                }
            } else {
                // populate from existing DB fields if present
                if (!empty($inv->channel_source_in)) {
                    // try resolve channel name from channels table
                    $ch = Channel::where('source_in', $inv->channel_source_in)->first();
                    if ($ch) $inv->resolved_channel_name = $ch->name;
                }
            }
        }

        $clients = Client::orderBy('name')->get();

        return view('reports.live.index', [
            'inventories' => $inventories,
            'clients'     => $clients,
            'search'      => $search,
            'sort'        => $sort,
            'direction'   => $direction,
        ]);
    }

    /**
     * AJAX per-row endpoint. Tries ADB, then mgmt API, then stream_url.
     * Upserts channel row when a raw UDP ip:port is found (stores into channels.source_in).
     * Returns JSON: success, url, name, channel_name, stream_status, message.
     */
    public function activeChannel(Request $request, Inventory $inventory)
    {
        try {
            // Attempt ADB discovery first
            $adbRes = $this->discoverActiveChannelAdb($inventory);

            if (is_array($adbRes) && ($adbRes['url'] || $adbRes['name'] || $adbRes['raw_source'])) {
                // If raw_source (ip:port) is present, upsert Channel table and prefer channel.name for display
                $channelName = null;
                $channelId = null;
                if (!empty($adbRes['raw_source'])) {
                    $raw = $adbRes['raw_source']; // ip:port (no scheme)
                    $channel = Channel::firstOrCreate(
                        ['source_in' => $raw],
                        ['name' => null]
                    );
                    $channelName = $channel->name;
                    $channelId = $channel->id;
                }

                return response()->json([
                    'success' => true,
                    'url' => $adbRes['url'] ?? null,
                    'name' => $adbRes['name'] ?? null,
                    'channel_name' => $channelName, // null if no channel.name yet
                    'channel_id' => $channelId,
                    'stream_status' => $adbRes['stream_status'] ?? null,
                ]);
            }

            // Fallback to device mgmt API
            $url = $this->discoverActiveChannel($inventory);
            if ($url) {
                $norm = $this->normalizeCandidateUrl($url);
                // If normalizeCandidateUrl returned ip:port (url=udp://ip:port), check channels table by raw ip:port
                $raw = null;
                if ($norm['url'] && preg_match('#^udp://(.+)$#i', $norm['url'], $mm)) {
                    $raw = $mm[1];
                } elseif ($norm['name'] && preg_match('#^[0-9\.:\[\]]+:[0-9]+$#', $norm['name'])) {
                    $raw = $norm['name'];
                }

                $channelName = null;
                $channelId = null;
                if ($raw) {
                    $channel = Channel::firstOrCreate(['source_in' => $raw], ['name' => null]);
                    $channelName = $channel->name;
                    $channelId = $channel->id;
                }

                return response()->json([
                    'success' => true,
                    'url' => $norm['url'],
                    'name' => $norm['name'] ?? null,
                    'channel_name' => $channelName,
                    'channel_id' => $channelId,
                    'stream_status' => $inventory->stream_status ?? null,
                ]);
            }

            // stream_url fallback
            if (!empty($inventory->stream_url)) {
                $norm = $this->normalizeCandidateUrl($inventory->stream_url);
                $raw = null;
                if ($norm['url'] && preg_match('#^udp://(.+)$#i', $norm['url'], $mm)) {
                    $raw = $mm[1];
                }
                $channelName = null;
                $channelId = null;
                if ($raw) {
                    $channel = Channel::firstOrCreate(['source_in' => $raw], ['name' => null]);
                    $channelName = $channel->name;
                    $channelId = $channel->id;
                }

                return response()->json([
                    'success' => true,
                    'url' => $norm['url'],
                    'name' => $norm['name'] ?? null,
                    'channel_name' => $channelName,
                    'channel_id' => $channelId,
                    'stream_status' => $inventory->stream_status ?? null,
                ]);
            }

            // no result
            return response()->json(['success' => false, 'message' => 'Active channel not found.', 'stream_status' => $inventory->stream_status ?? null], 404);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to discover active channel.',
                'error' => app()->isProduction() ? null : $e->getMessage(),
                'stream_status' => $inventory->stream_status ?? null,
            ], 500);
        }
    }

    /**
     * discoverActiveChannelAdb: detect app & UDP sockets via adb.
     * Returns array: ['url'=>udp://ip:port|null, 'name'=>friendly|null, 'raw_source'=> 'ip:port'|null, 'stream_status'=>app|null]
     */
    protected function discoverActiveChannelAdb(Inventory $inventory): array
    {
        $adbPath = env('ADB_PATH') ?: config('services.adb.path') ?: (str_starts_with(PHP_OS_FAMILY, 'Windows') ? 'C:\\adb\\adb.exe' : '/usr/bin/adb');
        $result = ['url' => null, 'name' => null, 'raw_source' => null, 'stream_status' => null];

        $ip = trim((string) $inventory->box_ip);
        if ($ip === '') return $result;

        $devicesOutput = [];
        @exec(escapeshellcmd($adbPath) . ' devices 2>&1', $devicesOutput);

        $deviceId = null;
        foreach ($devicesOutput as $line) {
            $line = trim($line);
            if ($line === '' || stripos($line, 'List of devices') !== false) continue;
            if (preg_match('/^(' . preg_quote($ip) . ':5555)\s+device$/i', $line, $m)) {
                $deviceId = $m[1];
                break;
            }
            if (preg_match('/^(' . preg_quote($ip) . ':5555)\s*[\t ]+device$/i', $line, $m2)) {
                $deviceId = $m2[1];
                break;
            }
        }

        if (!$deviceId) {
            // try connect
            @exec(escapeshellcmd($adbPath) . ' connect ' . escapeshellarg($ip . ':5555') . ' 2>&1', $connectOut);
            $devicesOutput2 = [];
            @exec(escapeshellcmd($adbPath) . ' devices 2>&1', $devicesOutput2);
            foreach ($devicesOutput2 as $line) {
                $line = trim($line);
                if (preg_match('/^(' . preg_quote($ip) . ':5555)\s+device$/i', $line, $m)) {
                    $deviceId = $m[1];
                    break;
                }
            }
        }

        if (!$deviceId) return $result;

        // dumpsys window -> detect app
        $dumpsysOut = [];
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            @exec(escapeshellcmd($adbPath) . ' -s ' . escapeshellarg($deviceId) . ' shell dumpsys window 2>&1 | findstr /C:mCurrentFocus', $dumpsysOut);
        } else {
            @exec(escapeshellcmd($adbPath) . ' -s ' . escapeshellarg($deviceId) . ' shell dumpsys window 2>&1 | grep mCurrentFocus', $dumpsysOut);
        }

        $app = null;
        if (!empty($dumpsysOut)) {
            $joined = implode("\n", $dumpsysOut);
            if (preg_match('/ ([a-zA-Z0-9._]+)\/[A-Za-z0-9._\$\/]+/',$joined,$mapp)) {
                $pkg = $mapp[1];
                if ($pkg === 'com.aminocom.browser') $app = 'Amino Zapper';
                elseif ($pkg === 'com.aminocom.settingsmenu') $app = 'Amino Settings';
                elseif ($pkg === 'com.aminocom.aosplauncher') $app = 'Booting';
                else $app = $pkg;
            } else {
                $app = 'Unknown';
            }
        }

        // netstat -anu -> find ip:port
        $udpOut = [];
        @exec(escapeshellcmd($adbPath) . ' -s ' . escapeshellarg($deviceId) . ' shell netstat -anu 2>&1', $udpOut);
        $found = [];
        foreach ($udpOut as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (preg_match('/\b(\d{1,3}(?:\.\d{1,3}){3}:\d+)\b/', $line, $mu)) {
                $found[] = $mu[1];
            } elseif (preg_match('/\b([0-9A-Za-z\.\-]+:\d+)\b/', $line, $mu2)) {
                $found[] = $mu2[1];
            }
        }

        if (!empty($found)) {
            $multicast = array_values(array_filter($found, fn($v) => strpos($v, '239.') === 0));
            $candidate = !empty($multicast) ? $multicast[0] : (array_values(array_unique($found))[0] ?? null);

            if (!empty($candidate)) {
                // store raw ip:port in channels.source_in (controller will upsert)
                $result['raw_source'] = $candidate; // e.g. "239.1.2.3:5000"
                $result['url'] = 'udp://' . $candidate;
                $result['name'] = $candidate;
                $result['stream_status'] = $app ?? null;

                // set instance props for blade convenience (not persisted)
                $inventory->channel_source_in = $candidate;
                $inventory->active_channel_name = $candidate;
                $inventory->stream_status = $app ?? null;

                // also resolve channel name from channels table if exists
                $channel = Channel::where('source_in', $candidate)->first();
                if ($channel) {
                    $result['channel_name'] = $channel->name;
                }

                return $result;
            }
        }

        // no UDP found - still surface app (Amino Settings / Booting / Unknown)
        if ($app !== null) {
            $result['stream_status'] = $app;
            $inventory->stream_status = $app;
            return $result;
        }

        return $result;
    }

    /**
     * discoverActiveChannel: mgmt API fallback (unchanged).
     */
    protected function discoverActiveChannel(Inventory $inventory): ?string
    {
        if (!empty($inventory->stream_url)) {
            return $inventory->stream_url;
        }

        if (empty($inventory->mgmt_url)) {
            return null;
        }

        $base = rtrim($inventory->mgmt_url, '/');
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
                    $body = trim((string) $resp->body());

                    if (Str::startsWith($body, '{') || Str::startsWith($body, '[')) {
                        $json = $resp->json();
                        if (is_array($json)) {
                            if (!empty($json['url']))        return (string) $json['url'];
                            if (!empty($json['stream_url'])) return (string) $json['stream_url'];
                            if (!empty($json['channel']))    return (string) $json['channel'];
                            if (!empty($json['data']['stream'])) return (string) $json['data']['stream'];
                        }
                    } else {
                        if (preg_match('#^(https?|udp|rtp|rtsp)://.+#i', $body)) {
                            return $body;
                        }
                        if (preg_match('#^[\w\.\:\-\/]+$#', $body)) {
                            return $body;
                        }
                    }
                }
            } catch (Throwable $e) {
                // try next
            }
        }

        return null;
    }

    /**
     * normalizeCandidateUrl helper
     */
    protected function normalizeCandidateUrl(string $candidate): array
    {
        $candidate = trim($candidate);
        if ($candidate === '') return ['url' => null, 'name' => null];

        if (preg_match('#^(https?|udp|rtp|rtsp)://#i', $candidate)) {
            return ['url' => $candidate, 'name' => $this->nameFromUrl($candidate)];
        }

        if (preg_match('#^[0-9\.:\[\]]+:[0-9]+$#', $candidate) || preg_match('#^[\w\.-]+:[0-9]+$#', $candidate)) {
            return ['url' => 'udp://' . $candidate, 'name' => $candidate];
        }

        return ['url' => null, 'name' => $candidate];
    }

    protected function nameFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $name = $path ? basename($path) : $url;
        return (string) $name;
    }

    // preview/download and collectSelectedForPdf as before
    public function preview(Request $request)
    {
        [$inventories, $title] = $this->collectSelectedForPdf($request);
        $pdf = PDF::loadView('reports.live.pdf', compact('inventories', 'title'))
                 ->setPaper('a4', 'landscape');

        return $pdf->stream('online_boxes_selected.pdf');
    }

    public function download(Request $request)
    {
        [$inventories, $title] = $this->collectSelectedForPdf($request);
        $pdf = PDF::loadView('reports.live.pdf', compact('inventories', 'title'))
                 ->setPaper('a4', 'landscape');

        return $pdf->download('online_boxes_selected.pdf');
    }

    private function collectSelectedForPdf(Request $request): array
    {
        $selected = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        if ($selected->isEmpty()) {
            throw ValidationException::withMessages([
                'selected_ids' => 'Please select at least one record before viewing or downloading.',
            ]);
        }

        $inventories = Inventory::with(['client', 'packages'])
            ->whereIn('id', $selected)
            ->orderByDesc('id')
            ->get();

        $onlineIds = $this->onlineService->detectOnline(
            $inventories->map(fn ($inv) => (object) [
                'id'       => $inv->id,
                'box_ip'   => $inv->box_ip,
                'adb_port' => $inv->adb_port ?? null,
            ])
        );

        $inventories->each(function ($inv) use ($onlineIds) {
            $inv->is_online = in_array($inv->id, $onlineIds, true);
            // try to resolve channel name for PDF if channel_source_in present
            if (!empty($inv->channel_source_in)) {
                $ch = Channel::where('source_in', $inv->channel_source_in)->first();
                if ($ch) $inv->resolved_channel_name = $ch->name;
            }
        });

        $title = 'Inventories (Online Status via ADB)';

        return [$inventories, $title];
    }
    public function playVlc(Request $request, Inventory $inventory)
    {
        try {
            $payloadUrl = $request->input('url');
            $url = $payloadUrl ?: ($inventory->channel_source_in ?? $inventory->stream_url ?? null);

            if (empty($url)) {
                return response()->json(['success' => false, 'message' => 'No stream URL provided.'], 422);
            }

            // Allow only safe schemes
            if (!preg_match('#^(udp|rtp|rtsp|https?|http)://#i', $url)) {
                // also allow plain ip:port -> convert to udp://ip:port
                if (preg_match('#^[0-9\.:\[\]]+:[0-9]+$#', $url)) {
                    $url = 'udp://' . $url;
                } else {
                    return response()->json(['success' => false, 'message' => 'Unsupported URL scheme.'], 422);
                }
            }

            // Resolve vlc binary path (use environment or default)
            $vlcPath = env('VLC_PATH') ?: '/usr/bin/vlc';
            if (!file_exists($vlcPath)) {
                // try `which vlc`
                $which = null;
                @exec('which vlc 2>/dev/null', $which, $code);
                if (!empty($which) && is_array($which) && trim($which[0]) !== '') {
                    $vlcPath = trim($which[0]);
                }
            }

            if (!file_exists($vlcPath)) {
                return response()->json(['success' => false, 'message' => 'VLC not found on server. Set VLC_PATH in .env.'], 500);
            }

            // Build shell command:
            // - Use DISPLAY (attempt to open on server desktop). Default DISPLAY=:0 (adjust in .env if needed)
            // - Use nohup and background & so the request returns immediately.
            // - Use --play-and-exit to auto-exit when done; remove if you want persistent player.
            $display = env('SERVER_DISPLAY') ?: ':0';
            $xauthority = env('XAUTHORITY') ?: null; // optional: '/home/youruser/.Xauthority'

            // Escape URL for shell
            $escapedUrl = escapeshellarg($url);
            $vlcCmd = escapeshellcmd($vlcPath) . " --intf qt --play-and-exit $escapedUrl";

            // Prepare environment and full command (nohup + redirect)
            $envPrefix = '';
            if ($xauthority) {
                // include XAUTHORITY if provided
                $envPrefix = "DISPLAY={$display} XAUTHORITY={$xauthority} ";
            } else {
                $envPrefix = "DISPLAY={$display} ";
            }

            // full shell line
            $shellCmd = "nohup {$envPrefix}{$vlcCmd} > /dev/null 2>&1 &";

            // Run via Symfony Process (shell)
            $process = Process::fromShellCommandline($shellCmd);
            // Important: don't set timeout to allow backgrounding; let the shell return quickly.
            $process->setTimeout(10);

            $process->run();

            // Note: when using &, process likely returns exit code 0 quickly; but check for failure.
            if (!$process->isSuccessful()) {
                // capture output for debugging (only in non-production)
                $out = $process->getErrorOutput() ?: $process->getOutput();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to start VLC process.',
                    'error' => app()->isProduction() ? null : $out,
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'VLC launched on server.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception while launching VLC.',
                'error' => app()->isProduction() ? null : $e->getMessage(),
            ], 500);
        }
    }
}


