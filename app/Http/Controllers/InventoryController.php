<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with('client');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('box_model', 'like', "%{$search}%")
                  ->orWhere('box_serial_no', 'like', "%{$search}%")
                  ->orWhere('box_mac', 'like', "%{$search}%");
        }

        $inventories = $query->orderBy('id', 'desc')->get();
        $selectedInventory = $request->inventory_id ? Inventory::with('client')->find($request->inventory_id) : null;
        $clients = Client::all();

        return view('inventories.index', compact('inventories', 'selectedInventory', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'box_model' => 'required',
            'box_serial_no' => 'required',
            'box_mac' => 'required|unique:inventories,box_mac',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventories', 'public');
        }

        Inventory::create($data);

        return redirect()->route('inventories.index')->with('success', 'Inventory added successfully.');
    }

    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'box_model' => 'required',
            'box_serial_no' => 'required',
            'box_mac' => 'required|unique:inventories,box_mac,' . $inventory->id,
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventories', 'public');
        }

        $inventory->update($data);

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
    }
}
