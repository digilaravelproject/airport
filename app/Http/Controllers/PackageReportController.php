<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class PackageReportController extends Controller
{
    public function index(Request $request)
    {
        $map = [
            'id'   => 'id',
            'name' => 'name',
        ];
        $sort      = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortCol   = $map[$sort] ?? 'id';

        $packages = Package::with('channels')
            ->orderBy($sortCol, $direction)
            ->paginate(15)
            ->appends($request->query());

        return view('reports.packages.index', compact('packages'));
    }

    public function preview(Request $request)
    {
        [$packages, $title] = $this->collectSelected($request);

        // debug mode: return HTML so you can inspect the rows in browser (useful for local testing)
        if ($request->query('debug') == 1) {
            // return the same HTML used by the PDF blade so you can visually confirm dataset
            return view('reports.packages.pdf', compact('packages', 'title'))->with('debug', true);
        }

        // ensure related channels are loaded for PDF
        $packages->load('channels');

        $pdf = PDF::loadView('reports.packages.pdf', compact('packages', 'title'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('packages_selected.pdf');
    }

    public function download(Request $request)
    {
        [$packages, $title] = $this->collectSelected($request);

        $packages->load('channels');

        $pdf = PDF::loadView('reports.packages.pdf', compact('packages', 'title'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('packages_selected.pdf');
    }

    private function collectSelected(Request $request): array
    {
        $ids = collect($request->input('selected_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter()
            ->unique();

        if ($ids->isEmpty()) {
            // throw a validation error so the user sees a friendly message in the new tab
            throw ValidationException::withMessages([
                'selected_ids' => 'Please select at least one package.',
            ]);
        }

        $packages = Package::with('channels')
            ->whereIn('id', $ids->all())
            ->orderBy('name')
            ->get();

        return [$packages, 'Packages (Selected)'];
    }
}
