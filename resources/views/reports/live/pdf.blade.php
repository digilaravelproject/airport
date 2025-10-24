<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Live Boxes Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f2f2f2; text-align: left; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; background: #666; color:#fff; font-size: 11px; }
        .muted { color:#777; }
    </style>
</head>
<body>
    <div class="title">{{ $title ?? 'Live Boxes Report' }}</div>
    <div style="margin-bottom:10px;">Generated at: {{ now()->format('Y-m-d H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Box ID</th>
                <th>Box Model</th>
                <th>Serial No</th>
                <th>MAC ID</th>
                <th>Client</th>
                <th>Packages</th>
                <th>Status</th>
                <th>Warranty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventories as $i => $inv)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><span class="badge">{{ $inv->box_id }}</span></td>
                    <td>{{ $inv->box_model }}</td>
                    <td>{{ $inv->box_serial_no }}</td>
                    <td>{{ $inv->box_mac }}</td>
                    <td>
                        @if($inv->client)
                            {{ $inv->client->id }} - {{ $inv->client->name }}
                        @else
                            <span class="muted">No client</span>
                        @endif
                    </td>
                    <td>
                        @if($inv->packages->count())
                            {{ $inv->packages->pluck('name')->join(', ') }}
                        @else
                            <span class="muted">No packages</span>
                        @endif
                    </td>
                    <td>{{ (string)$inv->status === '1' ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $inv->warranty_date ? \Carbon\Carbon::parse($inv->warranty_date)->format('Y-m-d') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="muted">No live boxes found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
