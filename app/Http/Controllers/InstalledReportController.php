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
    public function index(Request $request)
    {
        $clientId = $request->query('client_id');

        $inventories = Inventory::with(['client', 'packages'])
            ->where('status', 1)
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->orderByDesc('id')
            ->get(); // Load all (keeps Select All behavior for the current view)

        // For dropdown
        $clients = Client::orderBy('name')->get();

        return view('reports.installed.index', compact('inventories', 'clients'));
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
