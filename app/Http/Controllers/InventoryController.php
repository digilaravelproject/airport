<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request, $id = null)
    {
        $query = Inventory::with('client');

        if (!empty($id)) {
            // keep existing behavior: filter by primary key when visiting /inventories/{id}
            $query->where('id', $id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $field  = $request->get('field', 'all');

            $query->where(function($q) use ($field, $search) {
                if ($field === 'all') {
                    $q->where('box_id', 'like', "%{$search}%")
                      ->orWhere('box_ip', 'like', "%{$search}%")
                      ->orWhere('box_model', 'like', "%{$search}%")
                      ->orWhere('box_serial_no', 'like', "%{$search}%")
                      ->orWhere('box_mac', 'like', "%{$search}%")
                      ->orWhere('box_fw', 'like', "%{$search}%")
                      ->orWhereHas('client', function($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%");
                      });
                } elseif ($field === 'client_name') {
                    $q->whereHas('client', function($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
                } else {
                    // allow only known columns
                    $allowed = ['box_id','box_ip','box_model','box_serial_no','box_mac','box_fw'];
                    if (in_array($field, $allowed, true)) {
                        $q->where($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        $inventories = $query->orderBy('id', 'desc')->get();
        $selectedInventory = $request->inventory_id ? Inventory::with('client')->find($request->inventory_id) : null;
        $clients = Client::all();

        return view('inventories.index', compact('inventories', 'selectedInventory', 'clients'));
    }

    public function show(Request $request, $id)
    {
        // reuse the same listing page but filtered by this primary key $id
        return $this->index($request, $id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'box_id'        => 'required|string|max:255|unique:inventories,box_id',
            'box_model'     => 'required',
            'box_serial_no' => 'required',
            'box_mac'       => 'required|unique:inventories,box_mac',
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
            'box_id'        => 'required|string|max:255|unique:inventories,box_id,' . $inventory->id,
            'box_model'     => 'required',
            'box_serial_no' => 'required',
            'box_mac'       => 'required|unique:inventories,box_mac,' . $inventory->id,
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventories', 'public');
        }

        $inventory->update($data);

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
    }
}
