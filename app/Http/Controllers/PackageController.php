<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Channel;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::with('channels');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // $packages = $query->get();
        $packages = $query->paginate(10); // or paginate(15), as you need

        $channels = Channel::all();

        $selectedPackage = null;
        if ($request->package_id) {
            $selectedPackage = Package::with('channels')->find($request->package_id);
        }

        return view('packages.index', compact('packages', 'channels', 'selectedPackage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'channel_id' => 'required|array',
            'channel_id.*' => 'exists:channels,id',
            'active'     => 'required|in:Yes,No',
        ]);

        $package = Package::create([
            'name'   => $request->name,
            'active' => $request->active,
        ]);

        $package->channels()->sync($request->channel_id);

        return redirect()->route('packages.index')->with('success', 'Package created successfully.');
    }

    public function update(Request $request, Package $package)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'channel_id' => 'required|array',
            'channel_id.*' => 'exists:channels,id',
            'active'     => 'required|in:Yes,No',
        ]);

        $package->update([
            'name'   => $request->name,
            'active' => $request->active,
        ]);

        $package->channels()->sync($request->channel_id);

        return redirect()->route('packages.index')->with('success', 'Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('packages.index')->with('success', 'Package deleted successfully.');
    }
}

