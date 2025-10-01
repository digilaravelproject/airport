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
                "name" => (string) $counter,
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
        $data[$package->name] = $channels;
    }

    // Filename = Box Serial No (e.g., 101.json)
    $filename = $inventory->box_serial_no . ".json";
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

    return redirect()->route('inventory-packages.index')
        ->with('success', 'Packages assigned successfully.');
}

}
