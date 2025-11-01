<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Packages (Selected)' }}</title>
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
    <div class="title">{{ $title ?? 'Packages (Selected)' }}</div>
    <div style="margin-bottom:10px;">Generated: {{ now()->format('Y-m-d H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $i => $pkg)
            <tr>
                <td><span class="badge">{{ $pkg->id }}</span></td>
                <td>{{ $pkg->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
