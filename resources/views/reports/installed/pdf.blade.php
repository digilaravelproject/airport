<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Installed Boxes (Selected)' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f4f4f4; text-align: left; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; background: #666; color:#fff; font-size: 11px; }
        .muted { color:#777; }
    </style>
</head>
<body>
    <div class="title">{{ $title ?? 'Installed Boxes (Selected)' }}</div>
    <div style="margin-bottom:10px;">Generated: {{ now()->format('Y-m-d H:i:s') }}</div>

    <table class="table table-bordered table-hover mb-0 align-middle">
    <thead class="table-light">
        <tr>
            <th style="width:40px;"></th> {{-- checkbox column --}}
            <th>Box ID</th>
            <th>Box IP</th>
            <th>MAC ID</th>
            <th>Client</th>
            <th>Establishment</th>
            <th>Box Model</th>
            <th>Serial No</th>
            <th>Packages</th>
            <th>Status</th>
            <th>Warranty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inventories as $i => $inv)
            <tr>
                <td>
                    <input type="checkbox" class="row-check" name="selected_ids[]" value="{{ $inv->id }}">
                </td>

                <td>
                    <span class="badge bg-secondary">{{ $inv->box_id }}</span>
                </td>

                <td>{{ $inv->box_ip ?? '-' }}</td>

                <td>{{ $inv->box_mac ?? '-' }}</td>

                <td>
                    @if($inv->client)
                        {{ $inv->client->id }} - {{ $inv->client->name }}
                    @else
                        <span class="text-muted">No client</span>
                    @endif
                </td>

                <td>{{ $inv->location ?? '-' }}</td>

                <td>{{ $inv->box_model ?? '-' }}</td>

                <td>{{ $inv->box_serial_no ?? '-' }}</td>

                <td>
                    @if($inv->packages->count())
                        {{ $inv->packages->pluck('name')->join(', ') }}
                    @else
                        <span class="text-muted">No packages</span>
                    @endif
                </td>

                <td>
                    <span class="badge bg-success">Installed</span>
                </td>

                <td>
                    {{ $inv->warranty_date ? \Carbon\Carbon::parse($inv->warranty_date)->format('Y-m-d') : '-' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
