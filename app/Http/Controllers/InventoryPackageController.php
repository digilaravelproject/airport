<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;               // <-- add this
use Symfony\Component\Process\Process;           // <-- and this

class InventoryPackageController extends Controller
{
    public function index()
    {
        // Load inventories with related client and packages
        $inventories = Inventory::with(['client', 'packages']);
        $inventories = $inventories->paginate(10); // or paginate(15), as you need
        // ->get();
        $packages = Package::all();

        return view('inventory_package_allocation.index', compact('inventories', 'packages'));
    }

    public function assign(Request $request, Inventory $inventory)
    {
        $request->validate([
            'package_ids'   => 'required|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        // Sync packages
        $inventory->packages()->sync($request->package_ids);

        // Fetch packages with channels
        $packages = $inventory->packages()->with('channels')->get();

        $data = [];

        foreach ($packages as $package) {
            $channels = [];
            $counter = 1;

            foreach ($package->channels as $channel) {
                $item = [
                    "name" => $channel->id,
                    "desc" => $channel->channel_name,
                    "url"  => $channel->channel_url,
                ];

                // Add "starting": true only for the first channel
                if ($counter === 1) {
                    $item["starting"] = true;
                }

                $channels[] = $item;
                $counter++;
            }

            // Dynamic key = package name (like "DTV")
            $data['DTV'] = $channels;
        }

        // Filename = Box Serial No (e.g., 101.json)
        $filename = $inventory->box_id . ".json";
        $path = storage_path("app/public/json/" . $filename);

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        if (file_exists($path)) {
            unlink($path);
        }

        // Write JSON with pretty print & unescaped slashes
        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        // === Reboot device via ADB over IP (after JSON is created) ===
        $ip = $inventory->box_ip ?? null; // ensure your Inventory has this column

        $rebootMsg = ' (reboot skipped: no device IP)';
        if ($ip) {
            $ok = $this->rebootViaAdb($ip);
            $rebootMsg = $ok ? ' (reboot command sent)' : ' (reboot failed; see logs)';
        } else {
            Log::warning('ADB reboot skipped: Inventory missing device_ip', ['inventory_id' => $inventory->id]);
        }

        return redirect()->route('inventory-packages.index')
            ->with('success', 'Packages assigned successfully.');
    }

    /**
     * Reboot an Android/TV box via ADB over TCP/IP.
     * Requires ADB_PATH and optional ADB_PORT in .env.
     */
    private function rebootViaAdb(string $ip): bool
    {
        $adbPath = env('ADB_PATH');                 // absolute path to adb.exe
        $port    = (int) env('ADB_PORT', 5555);
        $serial  = $ip . ':' . $port;

        if (!$adbPath || !file_exists($adbPath)) {
            Log::error('ADB reboot failed: invalid ADB_PATH', ['ADB_PATH' => $adbPath]);
            return false;
        }

        // Build processes as arrays to avoid quoting issues on Windows paths
        $steps = [
            ['label' => 'disconnect', 'proc' => new Process([$adbPath, 'disconnect', $serial])],
            ['label' => 'connect',    'proc' => new Process([$adbPath, 'connect', $serial])],
            ['label' => 'reboot',     'proc' => new Process([$adbPath, '-s', $serial, 'reboot'])],
        ];

        $allOk = true;

        foreach ($steps as $step) {
            /** @var \Symfony\Component\Process\Process $p */
            $p = $step['proc'];
            $p->setTimeout(20); // seconds
            try {
                $p->run();
                $ok = $p->isSuccessful();
                $allOk = $allOk && $ok;
                Log::info('ADB step ' . $step['label'], [
                    'serial'  => $serial,
                    'ok'      => $ok,
                    'exit'    => $p->getExitCode(),
                    'out'     => trim($p->getOutput()),
                    'err'     => trim($p->getErrorOutput()),
                ]);
                // if connect fails, no point continuing
                if ($step['label'] === 'connect' && !$ok) {
                    break;
                }
            } catch (\Throwable $e) {
                Log::error('ADB step exception: ' . $step['label'], [
                    'serial' => $serial,
                    'error'  => $e->getMessage(),
                ]);
                return false;
            }
        }

        return $allOk;
    }
}
