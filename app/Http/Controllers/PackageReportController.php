<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Client;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class PackageReportController extends Controller
{
    public function index(Request $request)
    {
        // Sorting (only allow known columns)
        $map = [
            'id'   => 'id',
            'name' => 'name',
        ];
        $sort      = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortCol   = $map[$sort] ?? 'id';

        // Paginate 15 per your original; keep everything else unchanged
        $packages = Package::query()
            ->orderBy($sortCol, $direction)
            ->paginate(15)
            ->appends($request->query());

        return view('reports.packages.index', compact('packages'));
    }

    public function preview(Request $request)
    {
        [$packages, $title] = $this->collectSelected($request);

        $pdf = PDF::loadView('reports.packages.pdf', compact('packages', 'title'))
                  ->setPaper('a4', 'portrait');

        // Opened in a new tab by the view's JS; no loader runs on the original page
        return $pdf->stream('packages_selected.pdf');
    }

    public function download(Request $request)
    {
        [$packages, $title] = $this->collectSelected($request);

        $pdf = PDF::loadView('reports.packages.pdf', compact('packages', 'title'))
                  ->setPaper('a4', 'portrait');

        // Opened in a new tab by the view's JS; no loader runs on the original page
        return $pdf->download('packages_selected.pdf');
    }

    private function collectSelected(Request $request): array
    {
        $ids = collect($request->input('selected_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter()
            ->unique();

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
