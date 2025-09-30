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
            $search = $request->search;
            $query->where('channel_name', 'like', "%{$search}%")
                  ->orWhere('channel_genre', 'like', "%{$search}%")
                  ->orWhere('channel_type', 'like', "%{$search}%");
        }

        $channels = $query->orderBy('id', 'desc')->get();
        $selectedChannel = null;

        if ($request->channel_id) {
            $selectedChannel = Channel::find($request->channel_id);
        }

        return view('channels.index', compact('channels', 'selectedChannel'));
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
