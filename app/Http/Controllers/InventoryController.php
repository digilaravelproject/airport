<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Client;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class InventoryController extends Controller
{
    public function index(Request $request, $id = null)
    {
        // Counts for summary tabs
        $totalBoxes      = Inventory::count();
        $assignedBoxes   = Inventory::whereHas('packages')->count();
        $unassignedBoxes = Inventory::whereDoesntHave('packages')->count();

        $query = Inventory::query();

        if (!empty($id)) {
            $query->where('inventories.id', $id);
        }

        // Apply assignment filter from summary tabs
        $assign = $request->get('assign', 'all'); // 'all' | 'assigned' | 'unassigned'
        if ($assign === 'assigned') {
            $query->whereHas('packages');
        } elseif ($assign === 'unassigned') {
            $query->whereDoesntHave('packages');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $field  = $request->get('field', 'all');

            $query->where(function($q) use ($field, $search) {
                if ($field === 'all') {
                    $q->where('inventories.box_id', 'like', "%{$search}%")
                      ->orWhere('inventories.box_ip', 'like', "%{$search}%")
                      ->orWhere('inventories.box_model', 'like', "%{$search}%")
                      ->orWhere('inventories.box_serial_no', 'like', "%{$search}%")
                      ->orWhere('inventories.box_mac', 'like', "%{$search}%")
                      ->orWhere('inventories.box_fw', 'like', "%{$search}%")
                      ->orWhereHas('client', function($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%");
                      });
                } elseif ($field === 'client_name') {
                    $q->whereHas('client', function($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
                } else {
                    $allowed = ['box_id','box_ip','box_model','box_serial_no','box_mac','box_fw'];
                    if (in_array($field, $allowed, true)) {
                        $q->where("inventories.$field", 'like', "%{$search}%");
                    }
                }
            });
        }

        // Sorting
        $map = [
            'id'            => 'inventories.id',
            'box_id'        => 'inventories.box_id',
            'box_ip'        => 'inventories.box_ip',
            'location'      => 'inventories.location',
            'box_mac'       => 'inventories.box_mac',
            'client_name'   => 'clients.name',
            'box_model'     => 'inventories.box_model',
            'box_serial_no' => 'inventories.box_serial_no',
            'box_fw'        => 'inventories.box_fw',
            'created_at'    => 'inventories.created_at',
        ];

        if ($request->has('sort')) {
            $sort = $request->get('sort', 'id');
            $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        } else {
            $sort = 'box_id';
            $direction = 'asc';
        }

        $sortColumn = $map[$sort] ?? $map['id'];

        // Only join clients if needed (sorting by client name)
        if ($sort === 'client_name') {
            $query->leftJoin('clients', 'clients.id', '=', 'inventories.client_id')
                  ->select('inventories.*');
        }

        // Eager load client for view
        $query->with('client');

        // PAGINATE: keep existing 10 per page
        $inventories = $query->orderBy($sortColumn, $direction)->paginate(10);

        $selectedInventory = $request->inventory_id
            ? Inventory::with('client')->find($request->inventory_id)
            : null;

        $clients = Client::all();

        return view('inventories.index', compact(
            'inventories', 'selectedInventory', 'clients',
            'totalBoxes', 'assignedBoxes', 'unassignedBoxes',
            'sort', 'direction'
        ));
    }

    public function show(Request $request, $id)
    {
        return $this->index($request, $id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'box_id'        => 'required|string|max:255|unique:inventories,box_id',
            'box_model'     => 'required',
            // ensure unique serial on create
            'box_serial_no' => 'required|unique:inventories,box_serial_no',
            'box_mac'       => 'required|unique:inventories,box_mac',
            // keep: box_ip unique when provided
            'box_ip'        => 'nullable|unique:inventories,box_ip',
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
            // ensure unique serial, ignoring current record
            'box_serial_no' => 'required|unique:inventories,box_serial_no,' . $inventory->id,
            'box_mac'       => 'required|unique:inventories,box_mac,' . $inventory->id,
            // keep: unique box_ip, ignoring current record
            'box_ip'        => 'nullable|unique:inventories,box_ip,' . $inventory->id,
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventories', 'public');
        }

        $inventory->update($data);

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $rows = (new FastExcel)->import($request->file('file'));

        $report = ['inserted'=>0,'updated'=>0,'skipped'=>0];
        $payload = [];

        // Preload existing IP -> serial map to enforce uniqueness across DB
        // Allows the *same* serial to keep/update its own IP, but prevents conflicts with other serials
        $existingIpToSerial = Inventory::whereNotNull('box_ip')
            ->pluck('box_serial_no', 'box_ip')
            ->all();

        // Track IPs seen within the current import to prevent duplicates in the same file
        $seenIps = []; // box_ip => box_serial_no

        foreach ($rows as $row) {
            $row = collect($row)->keyBy(fn($v,$k)=>strtolower(str_replace(' ','_',$k)));

            if (collect($row)->filter()->isEmpty()) continue;

            $serial = trim((string)($row['box_serial_no'] ?? ''));
            if ($serial === '') { $report['skipped']++; continue; }

            $ip = trim((string)($row['box_ip'] ?? ''));

            // Enforce uniqueness for non-empty IPs
            if ($ip !== '') {
                // Duplicate within the same file (and for a different serial)
                if (isset($seenIps[$ip]) && $seenIps[$ip] !== $serial) {
                    $report['skipped']++;
                    continue;
                }

                // Conflict with an existing record in DB (and for a different serial)
                if (isset($existingIpToSerial[$ip]) && $existingIpToSerial[$ip] !== $serial) {
                    $report['skipped']++;
                    continue;
                }

                // Reserve this IP for this serial in this import batch
                $seenIps[$ip] = $serial;
            }

            $payload[] = [
                'box_id'           => trim((string)($row['box_id'] ?? '')),
                'box_model'        => trim((string)($row['box_model'] ?? '')),
                'box_serial_no'    => $serial,
                'box_mac'          => trim((string)($row['box_mac'] ?? '')),
                'box_ip'           => $ip,
                'box_subnet'       => trim((string)($row['box_subnet'] ?? '')),
                'gateway'          => trim((string)($row['box_gateway'] ?? '')),
                'box_fw'           => trim((string)($row['box_fw'] ?? '')),
                'box_os'           => trim((string)($row['box_os'] ?? '')),
                'box_remote_model' => trim((string)($row['box_remote_model'] ?? '')),
                'supplier_name'    => trim((string)($row['supplier'] ?? $row['supplier_name'] ?? '')),
                'status'           => in_array(strtolower(trim((string)($row['status'] ?? ''))), ['1','active','Active','yes','true','y'], true) ? 1 : 0,
                'created_at'       => $this->parseImportDate($row['import_date'] ?? null),
                'updated_at'       => now(),
            ];
        }

        if ($payload) {
            $serials   = array_column($payload, 'box_serial_no');
            $existing  = Inventory::whereIn('box_serial_no', $serials)->pluck('box_serial_no')->all();
            $existsMap = array_flip($existing);

            foreach ($payload as $p) {
                isset($existsMap[$p['box_serial_no']]) ? $report['updated']++ : $report['inserted']++;
            }

            Inventory::upsert(
                $payload,
                ['box_serial_no'],
                ['box_id','box_model','box_mac','box_ip','box_subnet','gateway','box_fw','box_os','box_remote_model','supplier_name','status','created_at','updated_at']
            );
        }

        return back()->with('success', "Import completed. Inserted: {$report['inserted']}, Updated: {$report['updated']}, Skipped: {$report['skipped']}.");
    }

    private function parseImportDate($value): ?Carbon
    {
        if (!$value) return null;
        if (is_numeric($value)) {
            try { return Carbon::instance(ExcelDate::excelToDateTimeObject($value)); } catch (\Throwable) {}
        }
        $value = trim((string)$value);
        foreach (['d/m/Y','d-m-Y','Y-m-d','m/d/Y'] as $fmt) {
            try { return Carbon::createFromFormat($fmt, $value); } catch (\Throwable) {}
        }
        return null;
    }

    public function destroy(Inventory $inventory)
    {
        // optionally perform authorization checks here
        // e.g. $this->authorize('delete', $client);

        // If you need to cascade or handle inventories, do cleanup here.
        // Example: $client->inventories()->delete(); // if appropriate

        $inventory->delete();

        return redirect()->route('inventories.index')->with('success', 'Box deleted successfully.');
    }
}
