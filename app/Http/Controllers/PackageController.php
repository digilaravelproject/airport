<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::with('channels');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $packages = $query->orderByDesc('id')->paginate(10);

        // load all channels
        $channels = Channel::orderBy('channel_name')->get();

        $selectedPackage = null;
        if ($request->filled('package_id')) {
            $selectedPackage = Package::with('channels')->find($request->package_id);
        }

        return view('packages.index', compact('packages', 'channels', 'selectedPackage'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:packages,name'],
            'channel_id'  => ['required', 'array', 'min:1'],
            'channel_id.*'=> ['integer', 'exists:channels,id'],
            'active'      => ['required', Rule::in(['Yes','No'])],
        ]);

        $package = Package::create([
            'name'   => $validated['name'],
            'active' => $validated['active'],
        ]);

        $package->channels()->sync($validated['channel_id']);

        return redirect()->route('packages.index')->with('success','Package created successfully.');
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('packages','name')->ignore($package->id)],
            'channel_id'  => ['required','array','min:1'],
            'channel_id.*'=> ['integer','exists:channels,id'],
            'active'      => ['required', Rule::in(['Yes','No'])],
        ]);

        $package->update([
            'name'   => $validated['name'],
            'active' => $validated['active'],
        ]);

        $package->channels()->sync($validated['channel_id']);

        return redirect()->route('packages.index')->with('success','Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('packages.index')->with('success','Package deleted successfully.');
    }
}
