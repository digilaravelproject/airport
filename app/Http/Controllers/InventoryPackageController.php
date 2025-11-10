<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class InventoryPackageController extends Controller
{
    public function index(Request $request)
    {
        // Base query + eager loads for the view
        $query = Inventory::query()
            ->with(['client', 'packages'])
            ->withCount('packages'); // for sorting by allocated packages count

        // Sorting map (column => db column)
        $map = [
            'id'            => 'inventories.id',
            'box_id'        => 'inventories.box_id',
            'box_model'     => 'inventories.box_model',
            'box_serial_no' => 'inventories.box_serial_no',
            'box_mac'       => 'inventories.box_mac',
            'client_id'     => 'clients.id',
            'client_name'   => 'clients.name',
            'packages'      => 'packages_count',   // number of allocated packages
            'created_at'    => 'inventories.created_at',
        ];

        $sort = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortColumn = $map[$sort] ?? $map['id'];

        // Join clients only if needed for sorting by client columns
        if (in_array($sort, ['client_id', 'client_name'], true)) {
            $query->leftJoin('clients', 'clients.id', '=', 'inventories.client_id')
                  ->select('inventories.*'); // keep Inventory model hydration correct
        }

        $inventories = $query->orderBy($sortColumn, $direction)
            ->paginate(10)
            ->withQueryString();

        $packages = Package::all();

        return view('inventory_package_allocation.index', compact('inventories', 'packages', 'sort', 'direction'));
    }

 public function assign(Request $request, Inventory $inventory)
{
    try {
        // Validation (same logic)
        $request->validate([
            'package_ids'   => 'required|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        // Sync packages (same)
        $inventory->packages()->sync($request->package_ids);
        $packages = $inventory->packages()->with('channels')->get();

        // Build JSON data (same)
        $data = [];
        foreach ($packages as $package) {
            $channels = [];
            $counter  = 1;
            foreach ($package->channels as $k => $channel) {
                $item = [
                    "name" => (string) ($k + 1),
                    "desc" => $channel->channel_name,
                    "url"  => (str_starts_with($channel->channel_source_in, 'udp://'))
                        ? $channel->channel_source_in
                        : 'udp://' . $channel->channel_source_in,
                ];
                if ($counter === 1) {
                    $item["starting"] = true;
                }
                $channels[] = $item;
                $counter++;
            }
            $data['DTV'] = $channels;
        }

        // Write file (same path/behavior, but no PHP warnings leak)
        $filename = $inventory->box_id . ".json";
        $path = base_path($filename);
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException("Failed to create directory: {$dir}");
            }
        }

        if (file_exists($path) && !@unlink($path)) {
            throw new \RuntimeException("Failed to remove existing file: {$path}");
        }

        $bytes = @file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        if ($bytes === false) {
            throw new \RuntimeException("Failed to write file: {$path}");
        }

        // ADB reboot (same)
        $ip = $inventory->box_ip ?? null;
        if ($ip) {
            $messages = $this->rebootViaAdb($ip);
            return response()->json(['success' => true, 'messages' => $messages]);
        }

        return response()->json([
            'success'  => true,
            'messages' => ['Packages assigned, reboot skipped (no device IP).'],
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);
    } catch (\Throwable $e) {
        // Always JSON; never emit PHP warnings/notices that break JSON
        return response()->json([
            'success' => false,
            'message' => 'Server error',
            'detail'  => $e->getMessage(), // or null in production if you prefer
        ], 500);
    }
}
    public function assign_old(Request $request, Inventory $inventory)
    {
        $request->validate([
            'package_ids'   => 'required|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        $inventory->packages()->sync($request->package_ids);
        $packages = $inventory->packages()->with('channels')->get();

        $data = [];
        foreach ($packages as $package) {
            $channels = [];
            $counter = 1;
            foreach ($package->channels as $k => $channel) {
                $item = [
                    "name" => (string) ($k + 1),
                    "desc" => $channel->channel_name,
                    "url"  => (str_starts_with($channel->channel_source_in, 'udp://'))
                        ? $channel->channel_source_in
                        : 'udp://' . $channel->channel_source_in,
                ];
                if ($counter === 1) {
                    $item["starting"] = true;
                }
                $channels[] = $item;
                $counter++;
            }
            $data['DTV'] = $channels;
        }

        $filename = $inventory->box_id . ".json";
        $path = base_path($filename);
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $ip = $inventory->box_ip ?? null;
        if ($ip) {
            $messages = $this->rebootViaAdb($ip);
            return response()->json(['success' => true, 'messages' => $messages]);
        }

        return response()->json(['success' => true, 'messages' => ['Packages assigned, reboot skipped (no device IP).']]);
    }

private function rebootViaAdb(string $deviceIP): array
    {
        $port     = 5555;
        $messages = [];
        $adbPath  = '/usr/bin/adb';
        $homeDir  = '/var/www';
        $keyDir   = '/var/www/.android';

        if (!file_exists($adbPath)) {
            return ["❌ ADB binary not found at: $adbPath"];
        }
        if (!is_dir($keyDir)) {
            return ["❌ ADB key directory not found at: $keyDir"];
        }

        // Build a clean env for the child processes
        $env = [
            'HOME'            => $homeDir,
            'ADB_VENDOR_KEYS' => $keyDir,
            // Optional: PATH in case adb calls other utils
            'PATH'            => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        $adb    = escapeshellarg($adbPath);
        $serial = escapeshellarg("{$deviceIP}:{$port}");

        // helper to run and capture stderr, too
        $run = function (string $cmd) use ($env): array {
            $descriptorspec = [
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];
            $process = proc_open($cmd . ' 2>&1', $descriptorspec, $pipes, null, $env);
            if (!is_resource($process)) {
                return [1, ['Failed to start process']];
            }
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $status = proc_close($process);
            return [$status, explode("\n", trim($output))];
        };

        // 1) Ping (soft check)
        $messages[] = "🔍 Pinging device at $deviceIP...";
        $pingCmd = (stripos(PHP_OS, 'WIN') === 0)
            ? "ping -n 1 $deviceIP"
            : "/usr/bin/ping -c 1 $deviceIP";
        [$pingStatus, $pingOut] = $run($pingCmd);
        if ($pingStatus !== 0) {
            $messages[] = "⚠️ Ping failed (continuing anyway).";
            if (!empty($pingOut)) { $messages = array_merge($messages, $pingOut); }
        } else {
            $messages[] = "✅ Device responded to ping";
        }

        // 2) ADB connect
        $messages[] = "🔗 Connecting via ADB...";
        $run("$adb disconnect $serial"); // ignore status
        [$connStatus, $connOut] = $run("$adb connect $serial");
        if ($connStatus !== 0 || (isset($connOut[0]) && stripos(implode("\n", $connOut), 'unable') !== false)) {
            $messages[] = "❌ Failed to connect via ADB";
            if (!empty($connOut)) { $messages = array_merge($messages, $connOut); }
            return $messages;
        }
        $messages[] = "✅ ADB connected";

        // 3) Reboot
        $messages[] = "🔁 Sending reboot command...";
        [$rebootStatus, $rebootOut] = $run("$adb -s $serial reboot");
        if ($rebootStatus !== 0) {
            $messages[] = "❌ Failed to send reboot command";
            if (!empty($rebootOut)) { $messages = array_merge($messages, $rebootOut); }
            return $messages;
        }
        $messages[] = "✅ Reboot command sent successfully";

        // 4) Wait for device
        $messages[] = "⏳ Waiting for device to come back online...";
        // Give it a few seconds before wait-for-device
        sleep(5);
        [$waitStatus, $waitOut] = $run("$adb -s $serial wait-for-device");
        if ($waitStatus === 0) {
            $messages[] = "✅ Device rebooted and is back online";
        } else {
            $messages[] = "⚠️ Device did not come back online automatically";
            if (!empty($waitOut)) { $messages = array_merge($messages, $waitOut); }
        }

        return $messages;
    }
    private function rebootViaAdb_old(string $deviceIP): array
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
}
