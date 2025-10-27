<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class ChannelController extends Controller
{
    public function index(Request $request)
    {
        $query = Channel::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $field  = $request->get('field', 'all');

            $query->where(function($q) use ($field, $search) {
                if ($field === 'all') {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('channel_name', 'like', "%{$search}%")
                      ->orWhere('broadcast', 'like', "%{$search}%")
                      ->orWhere('channel_genre', 'like', "%{$search}%")
                      ->orWhere('channel_resolution', 'like', "%{$search}%")
                      ->orWhere('channel_type', 'like', "%{$search}%")
                      ->orWhere('language', 'like', "%{$search}%")
                      ->orWhere('active', $this->matchActive($search));
                } elseif ($field === 'active') {
                    $q->where('active', $this->matchActive($search));
                } else {
                    $allowed = ['id','channel_name','channel_genre','channel_resolution','channel_type','language','broadcast'];
                    if (in_array($field, $allowed, true)) {
                        $q->where($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        // Paginate 10 per page and keep current query string (search, field, channel_id, etc.)
        $channels = $query->orderBy('id', 'desc')
                          ->paginate(10)
                          ->withQueryString();

        $selectedChannel = $request->channel_id ? Channel::find($request->channel_id) : null;

        return view('channels.index', compact('channels', 'selectedChannel'));
    }

    private function matchActive(string $term): int
    {
        $t = strtolower(trim($term));
        if (in_array($t, ['1','yes','y','true','on','active'], true)) return 1;
        if (in_array($t, ['0','no','n','false','off','inactive'], true)) return 0;
        return -1;
    }

    public function store(Request $request)
    {
        $request->validate([
            'channel_name' => 'required',
            'channel_type' => 'required',
            'broadcast'    => 'nullable|string',
        ]);

        Channel::create($request->all());

        return redirect()->route('channels.index')->with('success', 'Channel created successfully.');
    }

    public function update(Request $request, Channel $channel)
    {
        $request->validate([
            'broadcast' => 'nullable|string',
        ]);

        $channel->update($request->all());

        return redirect()->route('channels.index')->with('success', 'Channel updated successfully.');
    }

    /**
     * Bulk import channels (same UX & behavior style as inventory import).
     * Accepts .xlsx, .xls, .csv. Upserts on channel_name.
     * Optionally accepts a "broadcast" column.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            $rows = (new FastExcel)->import($request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the file. Please verify format.');
        }

        $report = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];
        $payload = [];

        foreach ($rows as $row) {
            // Normalize headings to snake_case (e.g., "Channel Name" -> "channel_name")
            $row = collect($row)->keyBy(function($v, $k) {
                return strtolower(str_replace([' ', '-'], '_', trim($k)));
            });

            // Skip completely empty rows
            if (collect($row)->filter(fn($v) => $v !== null && $v !== '')->isEmpty()) {
                continue;
            }

            $name = trim((string)($row['channel_name'] ?? ''));
            if ($name === '') { $report['skipped']++; continue; }

            // Cast flags (encryption, active)
            $encryption = $this->toBoolInt($row['encryption'] ?? null);
            $active     = $this->toBoolInt($row['active'] ?? null);

            $payload[] = [
                'channel_name'           => $name,
                'broadcast'              => trim((string)($row['broadcast'] ?? '')),
                'channel_source_in'      => trim((string)($row['channel_source_in'] ?? '')),
                'channel_source_details' => trim((string)($row['channel_source_details'] ?? '')),
                'channel_stream_type_out'=> trim((string)($row['channel_stream_type_out'] ?? '')),
                'channel_url'            => trim((string)($row['channel_url'] ?? '')),
                'channel_genre'          => trim((string)($row['channel_genre'] ?? '')),
                'channel_resolution'     => trim((string)($row['channel_resolution'] ?? '')),
                'channel_type'           => trim((string)($row['channel_type'] ?? '')),
                'language'               => trim((string)($row['language'] ?? '')),
                'encryption'             => $encryption,
                'active'                 => $active,
            ];
        }

        if ($payload) {
            // Determine which will be updates vs inserts (upsert by channel_name)
            $names = array_column($payload, 'channel_name');
            $exists = Channel::whereIn('channel_name', $names)->pluck('channel_name')->all();
            $existsMap = array_flip($exists);

            foreach ($payload as $p) {
                isset($existsMap[$p['channel_name']]) ? $report['updated']++ : $report['inserted']++;
            }

            Channel::upsert(
                $payload,
                ['channel_name'],
                [
                    'broadcast',
                    'channel_source_in',
                    'channel_source_details',
                    'channel_stream_type_out',
                    'channel_url',
                    'channel_genre',
                    'channel_resolution',
                    'channel_type',
                    'language',
                    'encryption',
                    'active',
                    'updated_at'
                ]
            );
        }

        return back()->with('success', "Import completed. Inserted: {$report['inserted']}, Updated: {$report['updated']}, Skipped: {$report['skipped']}.");
    }

    private function toBoolInt($value): ?int
    {
        if ($value === null) return null;
        $t = strtolower(trim((string)$value));
        if (in_array($t, ['1','yes','y','true','on'], true))  return 1;
        if (in_array($t, ['0','no','n','false','off'], true)) return 0;
        return null;
    }
}
