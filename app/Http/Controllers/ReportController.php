<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use App\Models\Package;
use Illuminate\Http\Request;
use PDF;

class ReportController extends Controller
{
    /**
     * Show filter form and an HTML table of results (with pagination).
     * Uses GET so filters are shareable and buttons can reuse the query string.
     */
    public function index(Request $request)
    {
        $clients  = Client::orderBy('name')->get();
        $packages = Package::orderBy('name')->get();

        $query = Inventory::with(['client', 'packages'])->orderByDesc('id');

        // Filters (all optional)
        if ($request->filled('status')) {
            // expects "1" or "0"
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('package_id')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('packages.id', $request->package_id);
            });
        }

        if ($request->filled('warranty_before')) {
            $query->whereDate('warranty_date', '<=', $request->warranty_before);
        }

        // Show results (all if no filters, as requested)
        $inventories = $query->paginate(15)->withQueryString();

        return view('reports.index', compact('clients', 'packages', 'inventories'));
    }

    /**
     * Render the PDF in-browser (inline view/preview)
     */
    public function preview(Request $request)
    {
        $inventories = $this->buildQueryFromRequest($request)->get();
        $pdf = PDF::loadView('reports.pdf', compact('inventories'));
        return $pdf->stream('inventory_report.pdf'); // opens in a new tab
    }

    /**
     * Download the PDF
     */
    public function download(Request $request)
    {
        $inventories = $this->buildQueryFromRequest($request)->get();
        $pdf = PDF::loadView('reports.pdf', compact('inventories'));
        return $pdf->download('inventory_report.pdf');
    }

    /**
     * Build the common query used by both preview and download.
     */
    private function buildQueryFromRequest(Request $request)
    {
        $query = Inventory::with(['client', 'packages'])->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('package_id')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('packages.id', $request->package_id);
            });
        }

        if ($request->filled('warranty_before')) {
            $query->whereDate('warranty_date', '<=', $request->warranty_before);
        }

        return $query;
    }
}
