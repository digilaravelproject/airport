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
        // Base query with channel count for sorting by "Channels"
        $query = Package::with('channels')->withCount('channels');

        // (Optional) simple search by package name if you add a search box later
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $map = [
            'id'              => 'id',
            'name'            => 'name',
            'channels'        => 'channels_count', // sort by number of channels
            'active'          => 'active',
            'created_at'      => 'created_at',
        ];

        $sort = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortColumn = $map[$sort] ?? 'id';

        $packages = $query
            ->orderBy($sortColumn, $direction)
            ->paginate(10)
            ->withQueryString(); // preserve sort/direction (& future filters)

        // load all channels for the modal filters
        $channels = Channel::orderBy('channel_name')->get();

        // unique genres & languages for dropdowns
        $genres = Channel::whereNotNull('channel_genre')
            ->pluck('channel_genre')->unique()->sort()->values();

        $languages = Channel::whereNotNull('language')
            ->pluck('language')->unique()->sort()->values();

        $selectedPackage = null;
        if ($request->filled('package_id')) {
            $selectedPackage = Package::with('channels')->find($request->package_id);
        }

        return view('packages.index', compact(
            'packages', 'channels', 'genres', 'languages', 'selectedPackage',
            'sort', 'direction'
        ));
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
