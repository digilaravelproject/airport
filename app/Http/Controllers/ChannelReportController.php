<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class ChannelReportController extends Controller
{
    public function index(Request $request)
    {
        // Per-page pagination (default 10; allowed: 10, 25, 50, 100)
        $allowed = [10, 25, 50, 100];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $allowed, true)) {
            $perPage = 10;
        }

        // Optional genre filter
        $genre = $request->get('channel_genre');

        // Distinct genres for dropdown
        $genres = Channel::whereNotNull('channel_genre')
            ->where('channel_genre', '!=', '')
            ->distinct()
            ->orderBy('channel_genre')
            ->pluck('channel_genre')
            ->toArray();

        // Listing with optional genre filter + pagination
        $channels = Channel::select('id', 'channel_name', 'broadcast', 'channel_genre')
            ->when($genre, fn($q) => $q->where('channel_genre', $genre))
            ->orderBy('id')
            ->paginate($perPage)
            ->appends($request->query());

        return view('reports.channels.index', [
            'channels' => $channels,
            'perPage'  => $perPage,
            'genres'   => $genres,
        ]);
    }

    public function preview(Request $request)
    {
        [$channels, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.channels.pdf', compact('channels', 'title'))
                  ->setPaper('a4', 'portrait');
        return $pdf->stream('channels_list.pdf');
    }

    public function download(Request $request)
    {
        [$channels, $title] = $this->collectSelected($request);
        $pdf = PDF::loadView('reports.channels.pdf', compact('channels', 'title'))
                  ->setPaper('a4', 'portrait');
        return $pdf->download('channels_list.pdf');
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

        // Include broadcast & channel_genre for PDF as well.
        $channels = Channel::whereIn('id', $ids)
            ->orderBy('id')
            ->select('id', 'channel_name', 'broadcast', 'channel_genre')
            ->get();

        return [$channels, 'Channels (Selected)'];
    }
}
