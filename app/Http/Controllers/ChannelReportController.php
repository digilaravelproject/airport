<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class ChannelReportController extends Controller
{
    public function index()
    {
        // Load all channels (no filters); adjust ordering/columns as needed
        $channels = Channel::orderBy('id')->get();

        return view('reports.channels.index', compact('channels'));
    }

    public function preview(Request $request)
    {
        [$channels, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.channels.pdf', compact('channels', 'title'))
                  ->setPaper('a4', 'portrait');
        return $pdf->stream('channels_selected.pdf');
    }

    public function download(Request $request)
    {
        [$channels, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.channels.pdf', compact('channels', 'title'))
                  ->setPaper('a4', 'portrait');
        return $pdf->download('channels_selected.pdf');
    }

    private function collectSelected(Request $request): array
    {
        $ids = collect($request->input('selected_ids', []))
            ->map(fn($v) => (int)$v)->filter()->unique();

        if ($ids->isEmpty()) {
            throw ValidationException::withMessages([
                'selected_ids' => 'Please select at least one channel.',
            ]);
        }

        $channels = Channel::whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        return [$channels, 'Channels (Selected)'];
    }
}
