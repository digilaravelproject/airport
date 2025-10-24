<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Inventory;
use App\Models\Package;
use App\Services\InventoryOnlineService;
use Illuminate\Http\Request;
use PDF;

class LiveReportController extends Controller
{
    public function __construct(private InventoryOnlineService $onlineService)
    {
    }

    /**
     * Filters + paginated HTML table for currently-live boxes (with channel packages)
     */
    public function index(Request $request)
    {
        $clients  = Client::orderBy('name')->get();
        $packages = Package::orderBy('name')->get();

        // Base query with relationships
        $baseQuery = $this->baseQuery($request);

        // We must first fetch candidates (without pagination), detect who is online,
        // then paginate only the "online" set.
        $candidates = $baseQuery->get(['id', 'mgmt_url', 'mgmt_token', 'box_ip']);
        $onlineIds  = $this->onlineService->detectOnline($candidates);

        $inventories = Inventory::with(['client', 'packages'])
            ->whereIn('id', $onlineIds)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('reports.live.index', compact('clients', 'packages', 'inventories'));
    }

    /**
     * Inline PDF preview of currently-live boxes
     */
    public function preview(Request $request)
    {
        [$inventories, $title] = $this->collectLiveForPdf($request);
        $pdf = PDF::loadView('reports.live.pdf', compact('inventories', 'title'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('live_boxes_report.pdf');
    }

    /**
     * PDF download of currently-live boxes
     */
    public function download(Request $request)
    {
        [$inventories, $title] = $this->collectLiveForPdf($request);
        $pdf = PDF::loadView('reports.live.pdf', compact('inventories', 'title'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('live_boxes_report.pdf');
    }

    // ----------------- helpers -----------------

    private function baseQuery(Request $request)
    {
        $q = Inventory::with(['client', 'packages'])->orderByDesc('id');

        // Optional filters
        if ($request->filled('status')) {
            $q->where('status', $request->string('status')->toString());
        }
        if ($request->filled('client_id')) {
            $q->where('client_id', $request->integer('client_id'));
        }
        if ($request->filled('package_id')) {
            $pkgId = (int)$request->get('package_id');
            $q->whereHas('packages', fn ($qq) => $qq->where('packages.id', $pkgId));
        }
        if ($request->filled('warranty_before')) {
            $q->whereDate('warranty_date', '<=', $request->get('warranty_before'));
        }

        return $q;
    }

    /**
     * Returns [Collection $inventories, string $title] for PDF use.
     */
    private function collectLiveForPdf(Request $request): array
    {
        $baseQuery  = $this->baseQuery($request);
        $candidates = $baseQuery->get(['id', 'mgmt_url', 'mgmt_token', 'box_ip']);
        $onlineIds  = $this->onlineService->detectOnline($candidates);

        $inventories = Inventory::with(['client', 'packages'])
            ->whereIn('id', $onlineIds)
            ->orderByDesc('id')
            ->get();

        $title = 'Currently Live Boxes (with Channel Packages)';

        return [$inventories, $title];
    }
}
