<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Inventory;
use App\Services\InventoryOnlineService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class LiveReportController extends Controller
{
    public function __construct(private InventoryOnlineService $onlineService) {}

    /**
     * Online/Offline listing (ADB-based), with search/sort/pagination.
     */
    public function index(Request $request)
    {
        $search    = trim((string) $request->get('search', ''));
        $perPage   = (int) ($request->get('per_page', 10) ?: 10);
        $page      = max(1, (int) $request->get('page', 1));

        // Sorting map (DB columns)
        $map = [
            'box_id'        => 'inventories.box_id',
            'box_model'     => 'inventories.box_model',
            'box_serial_no' => 'inventories.box_serial_no',
            'box_mac'       => 'inventories.box_mac',
            'box_fw'        => 'inventories.box_fw',
            'client_name'   => 'clients.name',
            'id'            => 'inventories.id',
        ];
        $sort      = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortCol   = $map[$sort] ?? $map['id'];

        // Base query (+search)
        $baseQuery = Inventory::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('box_model', 'like', "%{$search}%")
                      ->orWhere('box_serial_no', 'like', "%{$search}%")
                      ->orWhere('box_mac', 'like', "%{$search}%")
                      ->orWhere('box_fw', 'like', "%{$search}%")
                      ->orWhere('box_id', 'like', "%{$search}%");
                });
            })
            ->with('client');

        if ($sort === 'client_name') {
            $baseQuery->leftJoin('clients', 'clients.id', '=', 'inventories.client_id')
                      ->select('inventories.*');
        }

        $baseQuery->orderBy($sortCol, $direction);

        // Paginate
        $inventories = $baseQuery
            ->paginate($perPage, ['*'], 'page', $page)
            ->appends($request->query());

        // ADB online status for current page only
        $currentPage = collect($inventories->items());

        $onlineIds = $this->onlineService->detectOnline(
            $currentPage->map(fn ($inv) => (object) [
                'id'       => $inv->id,
                'box_ip'   => $inv->box_ip,
                'adb_port' => $inv->adb_port ?? null,
            ])
        );

        foreach ($currentPage as $inv) {
            $inv->is_online = in_array($inv->id, $onlineIds, true);
        }

        // For completeness, though not used in this view
        $clients = Client::orderBy('name')->get();

        return view('reports.live.index', [
            'inventories' => $inventories,
            'clients'     => $clients,
            'search'      => $search,
            'sort'        => $sort,
            'direction'   => $direction,
        ]);
    }

    /**
     * Stream PDF for selected rows (includes ADB Online/Offline).
     */
    public function preview(Request $request)
    {
        [$inventories, $title] = $this->collectSelectedForPdf($request);
        $pdf = PDF::loadView('reports.live.pdf', compact('inventories', 'title'))
                 ->setPaper('a4', 'landscape');

        return $pdf->stream('online_boxes_selected.pdf');
    }

    /**
     * Download PDF for selected rows (includes ADB Online/Offline).
     */
    public function download(Request $request)
    {
        [$inventories, $title] = $this->collectSelectedForPdf($request);
        $pdf = PDF::loadView('reports.live.pdf', compact('inventories', 'title'))
                 ->setPaper('a4', 'landscape');

        return $pdf->download('online_boxes_selected.pdf');
    }

    /**
     * Helper: collect selected IDs, annotate ADB online status, return data for PDF.
     */
    private function collectSelectedForPdf(Request $request): array
    {
        $selected = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        if ($selected->isEmpty()) {
            throw ValidationException::withMessages([
                'selected_ids' => 'Please select at least one record before viewing or downloading.',
            ]);
        }

        $inventories = Inventory::with(['client', 'packages'])
            ->whereIn('id', $selected)
            ->orderByDesc('id')
            ->get();

        $onlineIds = $this->onlineService->detectOnline(
            $inventories->map(fn ($inv) => (object) [
                'id'       => $inv->id,
                'box_ip'   => $inv->box_ip,
                'adb_port' => $inv->adb_port ?? null,
            ])
        );

        $inventories->each(function ($inv) use ($onlineIds) {
            $inv->is_online = in_array($inv->id, $onlineIds, true);
        });

        $title = 'Inventories (Online Status via ADB)';

        return [$inventories, $title];
    }
}
