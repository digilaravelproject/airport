<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class InstalledReportController extends Controller
{
    /**
     * Show all installed boxes (status = 1) with selection checkboxes.
     */
    public function index()
    {
        // You can adjust the "installed" definition here
        $inventories = Inventory::with(['client', 'packages'])
            ->where('status', 1)
            ->orderByDesc('id')
            ->get(); // Load all, since Select All means all records anyway

        return view('reports.installed.index', compact('inventories'));
    }

    /**
     * Preview PDF of selected records.
     */
    public function preview(Request $request)
    {
        [$inventories, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.installed.pdf', compact('inventories', 'title'))
                 ->setPaper('a4', 'landscape');
        return $pdf->stream('installed_boxes_selected.pdf');
    }

    /**
     * Download PDF of selected records.
     */
    public function download(Request $request)
    {
        [$inventories, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.installed.pdf', compact('inventories', 'title'))
                 ->setPaper('a4', 'landscape');
        return $pdf->download('installed_boxes_selected.pdf');
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
