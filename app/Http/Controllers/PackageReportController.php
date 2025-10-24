<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class PackageReportController extends Controller
{
    public function index()
    {
        // Load all packages (no filters); adjust ordering/columns as needed
        $packages = Package::orderBy('name')->get();

        return view('reports.packages.index', compact('packages'));
    }

    public function preview(Request $request)
    {
        [$packages, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.packages.pdf', compact('packages', 'title'))
                  ->setPaper('a4', 'portrait');
        return $pdf->stream('packages_selected.pdf');
    }

    public function download(Request $request)
    {
        [$packages, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.packages.pdf', compact('packages', 'title'))
                  ->setPaper('a4', 'portrait');
        return $pdf->download('packages_selected.pdf');
    }

    private function collectSelected(Request $request): array
    {
        $ids = collect($request->input('selected_ids', []))
            ->map(fn($v) => (int)$v)->filter()->unique();

        if ($ids->isEmpty()) {
            throw ValidationException::withMessages([
                'selected_ids' => 'Please select at least one package.',
            ]);
        }

        $packages = Package::whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        return [$packages, 'Packages (Selected)'];
    }
}
