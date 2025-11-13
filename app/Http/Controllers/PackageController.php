<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $pivotTable = $this->getPivotTableName();
        $hasSortOrder = false;
        $orderColumn = null;

        if ($pivotTable && (Schema::hasColumn($pivotTable, 'sort_order') || Schema::hasColumn($pivotTable, 'position'))) {
            $hasSortOrder = true;
            $orderColumn = Schema::hasColumn($pivotTable, 'sort_order') ? 'sort_order' : 'position';
        }

        $query = Package::withCount('channels');

        if ($hasSortOrder) {
            $query = $query->with(['channels' => function ($q) use ($pivotTable, $orderColumn) {
                $q->orderBy($pivotTable . '.' . $orderColumn, 'asc');
            }]);
        } else {
            $query = $query->with('channels');
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $map = [
            'id'              => 'id',
            'name'            => 'name',
            'channels'        => 'channels_count',
            'active'          => 'active',
            'created_at'      => 'created_at',
        ];

        if ($request->has('sort')) {
            $sort = $request->get('sort', 'id');
            $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        } else {
            $sort = 'id';
            $direction = 'asc';
        }

        $sortColumn = $map[$sort] ?? 'id';

        $packages = $query
            ->orderBy($sortColumn, $direction)
            ->paginate(10)
            ->withQueryString();

        $channels = Channel::orderBy('channel_name')->get();

        $genres = Channel::whereNotNull('channel_genre')
            ->pluck('channel_genre')->unique()->sort()->values();

        $languages = Channel::whereNotNull('language')
            ->pluck('language')->unique()->sort()->values();

        return view('packages.index', compact(
            'packages', 'channels', 'genres', 'languages',
            'sort', 'direction'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:packages,name'],
            'description' => ['nullable', 'string'],
            'channel_id'  => ['required', 'array', 'min:1'],
            'channel_id.*'=> ['integer', 'exists:channels,id'],
            'active'      => ['required', Rule::in(['Yes','No'])],
        ]);

        $package = Package::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'active'      => $validated['active'],
        ]);

        $this->syncChannelsWithOrder($package, $validated['channel_id']);

        return redirect()->route('packages.index')->with('success','Package created successfully.');
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('packages','name')->ignore($package->id)],
            'description' => ['nullable', 'string'],
            'channel_id'  => ['required','array','min:1'],
            'channel_id.*'=> ['integer','exists:channels,id'],
            'active'      => ['required', Rule::in(['Yes','No'])],
        ]);

        $package->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'active'      => $validated['active'],
        ]);

        $this->syncChannelsWithOrder($package, $validated['channel_id']);

        return redirect()->route('packages.index')->with('success','Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('packages.index')->with('success','Package deleted successfully.');
    }

    protected function syncChannelsWithOrder(Package $package, array $orderedIds)
    {
        $pivotTable = $this->getPivotTableName();
        $sortColumn = null;

        if ($pivotTable) {
            if (Schema::hasColumn($pivotTable, 'sort_order')) $sortColumn = 'sort_order';
            elseif (Schema::hasColumn($pivotTable, 'position')) $sortColumn = 'position';
        }

        if ($sortColumn) {
            $syncData = [];
            foreach ($orderedIds as $idx => $id) {
                $syncData[$id] = [$sortColumn => $idx + 1];
            }
            $package->channels()->sync($syncData);
        } else {
            $package->channels()->sync($orderedIds);
        }
    }

    protected function getPivotTableName()
    {
        $possible = [
            'channel_package', 'package_channel',
            'channels_packages', 'packages_channels',
        ];

        foreach ($possible as $p) {
            if (Schema::hasTable($p)) return $p;
        }
        return null;
    }
}
