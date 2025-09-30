<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Package;
use Illuminate\Http\Request;

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

    public function assign_old(Request $request, Inventory $inventory)
    {
        $request->validate([
            'package_ids'   => 'required|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        // Sync packages
        $inventory->packages()->sync($request->package_ids);

        // ✅ Fetch the updated packages with channel details
        $packages = $inventory->packages()->with('channels')->get();

        // ✅ Build JSON structure
        $channels = [];
        $counter = 1;
        foreach ($packages as $package) {
            foreach ($package->channels as $channel) {
                $channels[] = [
                    "name"     => (string) $counter,
                    "desc"     => $channel->channel_name,
                    "url"      => $channel->channel_url,
                    "starting" => $counter === 1 ? true : null, // ✅ mark first channel as starting
                ];
                $counter++;
            }
        }

        // ✅ Remove null keys (Laravel collection style)
        $channels = collect($channels)->map(function ($ch) {
            return array_filter($ch, fn($v) => !is_null($v));
        })->values()->toArray();

        $data = ["DTV" => $channels];

        // ✅ Define filename (e.g. Sample_101.json for Box No. 101)
        $filename = "Sample_" . $inventory->id . ".json";
        $path = storage_path("app/public/json/" . $filename);

        // ✅ Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        // ✅ Save JSON
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        return redirect()->route('inventory-packages.index')
            ->with('success', 'Packages assigned successfully.');
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

        // Build JSON structure
        $data = [];
        foreach ($packages as $package) {
            $channels = [];
            $counter = 1;
            foreach ($package->channels as $channel) {
                $channels[] = array_filter([
                    "name"     => (string) $counter,
                    "desc"     => $channel->channel_name,
                    "url"      => $channel->channel_url,
                    "starting" => $counter === 1 ? true : null,
                ]);
                $counter++;
            }
            $data[$package->name] = $channels; // ✅ Dynamic key = package name
        }

        // Define filename (e.g. Sample_101.json for Box No. 101)
        $filename = $inventory->box_serial_no . ".json";
        $path = storage_path("app/public/json/" . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        // ✅ Delete old file if exists
        if (file_exists($path)) {
            unlink($path);
        }

        // Save JSON
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        return redirect()->route('inventory-packages.index')
            ->with('success', 'Packages assigned successfully.');
    }
}
