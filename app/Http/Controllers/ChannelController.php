<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;

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
                      ->orWhere('channel_genre', 'like', "%{$search}%")
                      ->orWhere('channel_resolution', 'like', "%{$search}%")
                      ->orWhere('channel_type', 'like', "%{$search}%")
                      ->orWhere('language', 'like', "%{$search}%")
                      ->orWhere('active', $this->matchActive($search));
                } elseif ($field === 'active') {
                    $q->where('active', $this->matchActive($search));
                } else {
                    $allowed = ['id','channel_name','channel_genre','channel_resolution','channel_type','language'];
                    if (in_array($field, $allowed, true)) {
                        $q->where($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        $channels = $query->orderBy('id', 'desc')->get();
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
        ]);

        Channel::create($request->all());

        return redirect()->route('channels.index')->with('success', 'Channel created successfully.');
    }

    public function update(Request $request, Channel $channel)
    {
        $channel->update($request->all());

        return redirect()->route('channels.index')->with('success', 'Channel updated successfully.');
    }
}
