<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Inventories (Online Status via ADB)' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border:1px solid #999; padding:6px; }
        th { background:#eee; }
        .badge { display:inline-block; padding:2px 6px; border-radius:4px; font-size:11px; }
        .bg-success { background: #28a745; color:#fff; }
        .bg-secondary { background: #6c757d; color:#fff; }
        .bg-muted { background:#ccc; color:#000; }
    </style>
</head>
<body>
    <h3 style="margin-bottom:10px;">{{ $title ?? 'Inventories (Online Status via ADB)' }}</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Box ID</th>
                <th>Model</th>
                <th>Serial</th>
                <th>MAC</th>
                <th>Client</th>
                <th>Packages</th>
                <th>Status</th>
                <th>Warranty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $i => $inv)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $inv->box_id }}</td>
                <td>{{ $inv->box_model }}</td>
                <td>{{ $inv->box_serial_no }}</td>
                <td>{{ $inv->box_mac }}</td>
                <td>{{ $inv->client?->name }}</td>
                <td>{{ $inv->packages->pluck('name')->join(', ') }}</td>
                <td>
                    @if(!is_null($inv->is_online))
                        <span class="badge {{ $inv->is_online ? 'bg-success' : 'bg-secondary' }}">
                            {{ $inv->is_online ? 'Online' : 'Offline' }}
                        </span>
                    @else
                        <span class="badge bg-muted">Unknown</span>
                    @endif
                </td>
                <td>{{ $inv->warranty_date ? \Carbon\Carbon::parse($inv->warranty_date)->format('Y-m-d') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
