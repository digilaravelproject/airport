<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use App\Models\Package;
use Illuminate\Http\Request;
use PDF;

class ReportController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        $packages = Package::all();

        return view('reports.index', compact('clients', 'packages'));
    }

    public function generate(Request $request)
    {
        $query = Inventory::with(['client', 'packages']);

        // ✅ Filter by status (Active/Inactive)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ✅ Filter by Client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // ✅ Filter by Package
        if ($request->filled('package_id')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('packages.id', $request->package_id);
            });
        }

        // ✅ Filter by Warranty Date
        if ($request->filled('warranty_before')) {
            $query->whereDate('warranty_date', '<=', $request->warranty_before);
        }

        $inventories = $query->get();

        // Generate PDF
        $pdf = PDF::loadView('reports.pdf', compact('inventories'));

        return $pdf->download('inventory_report.pdf');
    }
}
