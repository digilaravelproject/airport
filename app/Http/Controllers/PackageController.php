<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class PackageController extends Controller
{
    public function index_old(Request $request)
    {
        $pivotTable = $this->getPivotTableName();
        $hasSortOrder = false;
        $orderColumn = null;

        if ($pivotTable && (Schema::hasColumn($pivotTable, 'sort_order') || Schema::hasColumn($pivotTable, 'position'))) {
            $hasSortOrder = true;
            $orderColumn = Schema::hasColumn($pivotTable, 'sort_order') ? 'sort_order' : 'position';
        }

        $query = Package::withCount('channels');

        if ($hasSortOrder && $pivotTable && $orderColumn) {
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
            'id'         => 'id',
            'name'       => 'name',
            'channels'   => 'channels_count',
            'active'     => 'active',
            'created_at' => 'created_at',
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

    public function index(Request $request)
    {
        $pivotTable = $this->getPivotTableName();
        $hasSortOrder = false;
        $orderColumn = null;

        if ($pivotTable && (Schema::hasColumn($pivotTable, 'sort_order') || Schema::hasColumn($pivotTable, 'position'))) {
            $hasSortOrder = true;
            $orderColumn = Schema::hasColumn($pivotTable, 'sort_order') ? 'sort_order' : 'position';
        }

        // Eager load channels (try to let DB do the ordering, but we'll re-normalize in PHP too)
        if ($hasSortOrder && $pivotTable && $orderColumn) {
            $query = Package::with([
                'channels' => function ($q) use ($pivotTable, $orderColumn) {
                    $q->orderBy($pivotTable . '.' . $orderColumn, 'asc');
                }
            ])->withCount('channels');
        } else {
            $query = Package::with('channels')->withCount('channels');
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $map = [
            'id'         => 'id',
            'name'       => 'name',
            'channels'   => 'channels_count',
            'active'     => 'active',
            'created_at' => 'created_at',
        ];

        $sort      = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortColumn = $map[$sort] ?? 'id';

        $packages = $query
            ->orderBy($sortColumn, $direction)
            ->paginate(10)
            ->withQueryString();

        // 🔥 FIX: Sort channels by pivot.sort_order ASC after pagination
        $packages->getCollection()->transform(function ($package) {
            if ($package->relationLoaded('channels')) {
                $package->setRelation(
                    'channels',
                    $package->channels->sortBy('pivot.sort_order')->values() // ASC
                );
            }
            return $package;
        });

        // --- ENSURE final channels collection is ordered by pivot ascending (robust fallback) ---
        if ($hasSortOrder && $orderColumn) {
            $packages->getCollection()->transform(function ($package) use ($orderColumn) {
                // if relation not loaded, skip (should be loaded though)
                if (!$package->relationLoaded('channels')) return $package;

                // sort by pivot-><orderColumn> ascending; fallback to id if pivot absent
                $sorted = $package->channels->sortBy(function ($ch) use ($orderColumn) {
                    // pivot may be present as integer or null
                    $v = $ch->pivot->{$orderColumn} ?? null;
                    return is_null($v) ? PHP_INT_MAX : (int) $v;
                })->values(); // reindex

                // set the sorted collection back onto the model
                $package->setRelation('channels', $sorted);

                return $package;
            });
        }

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

    /**
     * Return package JSON (including channels in server-sorted order).
     * Used by the client when opening the Edit/View modal to ensure authoritative ordering.
     */
    public function show(Package $package)
    {
        $package->load('channels');
        return response()->json($package);
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

    /**
     * Persist the ordered channel IDs into pivot sort column when available.
     * Keeps incoming order, casts to int and dedupes.
     */
    protected function syncChannelsWithOrder(Package $package, array $orderedIds)
    {
        $orderedIds = array_values(array_unique(array_map('intval', $orderedIds)));

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
