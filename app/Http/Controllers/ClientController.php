<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $field = $request->get('field', 'all');
            $search = $request->search;

            $query->where(function($q) use ($field, $search) {
                if ($field === 'all') {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('contact_no', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                } else {
                    $q->where($field, 'like', "%{$search}%");
                }
            });
        }

        $clients = $query->orderBy('id', 'desc')->get();

        $selectedClient = null;
        if ($request->client_id) {
            $selectedClient = Client::with([
                'inventories.packages',
            ])->find($request->client_id);
        }

        return view('clients.index', compact('clients', 'selectedClient'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
        ]);

        Client::create($request->only([
            'name','type','contact_person','contact_no','email',
            'address','city','pin','gst_no','state'
        ]));

        return redirect()->route('clients.index')->with('success','Client created successfully.');
    }

    public function update(Request $request, Client $client)
    {
        $client->update($request->only([
            'name','type','contact_person','contact_no','email',
            'address','city','pin','gst_no','state'
        ]));

        return redirect()->route('clients.index')->with('success','Client updated successfully.');
    }
}
