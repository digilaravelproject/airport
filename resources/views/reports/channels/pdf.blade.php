<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Channels (Selected)' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f4f4f4; text-align: left; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; background: #666; color:#fff; font-size: 11px; }
        .muted { color:#777; }
        .small { font-size: 11px; }
    </style>
</head>
<body>
    <div class="title">{{ $title ?? 'Channels (Selected)' }}</div>
    <div style="margin-bottom:10px;">Generated: {{ now()->format('Y-m-d H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                <th style="width:48px;">ID</th>
                <th>Name</th>
                <th>Broadcast</th>
                <th>Genre</th>
                <th>Resolution</th>
                <th>Type</th>
                <th>Source In</th>
                <th>Source Details</th>
                <th>Language</th>
                <th>Active</th>
            </tr>
        </thead>
        <tbody>
            @foreach($channels as $i => $ch)
            <tr>
                <td><span class="badge">{{ $ch->id }}</span></td>
                <td>{{ $ch->channel_name }}</td>
                <td>{{ $ch->broadcast }}</td>
                <td>{{ $ch->channel_genre }}</td>
                <td>{{ $ch->channel_resolution ?? '-' }}</td>
                <td>{{ $ch->channel_type ?? '-' }}</td>
                <td>{{ $ch->channel_source_in ?? '-' }}</td>
                <td>{{ $ch->channel_source_details ?? '-' }}</td>
                <td>{{ $ch->language ?? '-' }}</td>
                <td>{{ $ch->active ? 'Yes' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
