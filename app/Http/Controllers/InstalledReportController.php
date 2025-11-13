<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class InstalledReportController extends Controller
{
    /**
     * Show all installed boxes (status = 1) with selection checkboxes.
     * Adds optional filtering by client_id (GET param).
     */
    /**
     * Show all installed boxes (status = 1) with selection checkboxes.
     * Optional filtering by client_id (GET param).
     * Added safe sorting (asc/desc) with arrows in the view—kept logic/design intact.
     */
    public function index(Request $request)
    {
        $clientId  = $request->query('client_id');

        // --- sorting (whitelist columns) ---
        $map = [
            'id'            => 'inventories.id',
            'box_id'        => 'inventories.box_id',
            'box_model'     => 'inventories.box_model',
            'box_serial_no' => 'inventories.box_serial_no',
            'box_mac'       => 'inventories.box_mac',
            'warranty_date' => 'inventories.warranty_date',
            'client_name'   => 'clients.name', // requires join
        ];

        if ($request->has('sort')) {
            $sortInput = $request->get('sort', 'id');
            $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        } else {
            $sortInput = 'box_id';
            $direction = 'asc';
        }

        $sortCol   = $map[$sortInput] ?? $map['id'];

        // base query
        $query = Inventory::query()
            ->where('status', 1)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->with(['client', 'packages']);

        // join only when sorting by client_name
        if ($sortInput === 'client_name') {
            $query->leftJoin('clients', 'clients.id', '=', 'inventories.client_id')
                  ->select('inventories.*');
        }

        $query->orderBy($sortCol, $direction);

        // get all (keeps existing "Select All" behavior for the current view)
        $inventories = $query->get();

        // For dropdown
        $clients = Client::orderBy('name')->get();

        return view('reports.installed.index', [
            'inventories' => $inventories,
            'clients'     => $clients,
            'sort'        => $sortInput,
            'direction'   => $direction,
        ]);
    }

    /**
     * Preview PDF of selected records.
     */
    public function preview(Request $request)
    {
        [$inventories, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.installed.pdf', compact('inventories', 'title'))
                 ->setPaper('a4', 'landscape');
        return $pdf->stream('installed_boxes.pdf');
    }

    /**
     * Download PDF of selected records.
     */
    public function download(Request $request)
    {
        [$inventories, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.installed.pdf', compact('inventories', 'title'))
                 ->setPaper('a4', 'landscape');
        return $pdf->download('installed_boxes.pdf');
    }

    /**
     * Helper — collect selected inventory IDs.
     */
    private function collectSelected(Request $request): array
    {
        $selected = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn($id) => (int)$id)
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

        $title = 'Installed Boxes (with Channel Packages)';

        return [$inventories, $title];
    }
}
