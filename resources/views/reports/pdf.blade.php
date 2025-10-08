<!DOCTYPE html>
<html>
<head>
    <title>Inventory Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Inventory Report</h2>
    <table>
        <thead>
            <tr>
                <th>Box ID</th>
                <th>Box Model</th>
                <th>Serial No</th>
                <th>Status</th>
                <th>Client</th>
                <th>Packages</th>
                <th>Warranty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventories as $inv)
            <tr>
                <td>{{ $inv->box_id }}</td>
                <td>{{ $inv->box_model }}</td>
                <td>{{ $inv->box_serial_no }}</td>
                <td>{{ $inv->status ? 'Active' : 'Inactive' }}</td>
                <td>{{ $inv->client->id ?? '-' }} - {{ $inv->client->name ?? '-' }}</td>
                <td>
                    @if($inv->packages->count())
                        {{ $inv->packages->pluck('name')->join(', ') }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $inv->warranty_date ? $inv->warranty_date->format('d-M-Y') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;">No records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
