<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Client;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::with('client')->get();
        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        $clients = Client::all();
        return view('locations.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'location_name' => 'required'
        ]);

        Location::create($request->all());
        return redirect()->route('locations.index')->with('success','Location created successfully.');
    }

    public function edit(Location $location)
    {
        $clients = Client::all();
        return view('locations.edit', compact('location','clients'));
    }

    public function update(Request $request, Location $location)
    {
        $location->update($request->all());
        return redirect()->route('locations.index')->with('success','Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('locations.index')->with('success','Location deleted successfully.');
    }
}
