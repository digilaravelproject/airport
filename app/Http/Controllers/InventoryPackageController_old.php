<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class InventoryPackageController extends Controller
{
    public function index()
    {
        $inventories = Inventory::with(['client', 'packages'])->paginate(10);
        $packages = Package::all();

        return view('inventory_package_allocation.index', compact('inventories', 'packages'));
    }

    public function assign(Request $request, Inventory $inventory)
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

        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $ip = $inventory->box_ip ?? null;
        $rebootMsg = ' (reboot skipped: no device IP)';

        if ($ip) {
            // We’ll collect messages and send them back as JSON for frontend
            $messages = $this->rebootViaAdb($ip);
            return response()->json(['success' => true, 'messages' => $messages]);
        }

        return response()->json(['success' => true, 'messages' => ['Packages assigned, reboot skipped (no device IP).']]);
    }

    private function rebootViaAdb(string $deviceIP): array
    {
        $port = 5555;
        $messages = [];

        $adbPath = '/usr/bin/adb';

        if (!file_exists($adbPath)) {
            return ["❌ ADB binary not found at: $adbPath"];
        }

        // 👇 Add this line
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

        // 2) (Optional) disconnect then connect (avoids sticky state)
        $messages[] = "🔗 Connecting via ADB...";
        exec("$adb disconnect $serial", $discOut, $discStatus); // ignore status
        exec("$adb connect $serial", $connOut, $connStatus);

        // Some ADBs return 0 even if "already connected"; treat non-zero as failure
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

        // 4) Wait for this specific device to return
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
