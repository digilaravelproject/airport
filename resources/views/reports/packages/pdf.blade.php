<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Packages (Selected)' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#222; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; text-align: left; }
        th { background: #f4f4f4; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; background: #666; color:#fff; font-size: 11px; }
        .muted { color:#777; }
        .channels { white-space: normal; }
        .empty { color:#999; font-style:italic; padding:20px 0; }
        .debug { color:#c00; font-weight:bold; margin-bottom:10px; }
    </style>
</head>
<body>
    <div class="title">{{ $title ?? 'Packages (Selected)' }}</div>
    <div style="margin-bottom:10px;">Generated: {{ now()->format('Y-m-d H:i:s') }}</div>

    {{-- Debug indicator (visible when controller returns view in debug mode) --}}
    @if(!empty($debug) || request()->query('debug') == 1)
        <div class="debug">DEBUG: {{ $packages->count() }} package(s) in dataset</div>
    @endif

    @if($packages->isEmpty())
        <div class="empty">No packages to display. Please ensure you have selected packages on the previous page.</div>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:50px">ID</th>
                <th style="width:180px">Name</th>
                <th>Description</th>
                <th style="width:220px">Channels</th>
                <th style="width:80px">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $i => $pkg)
            <tr>
                <td><span class="badge">{{ $pkg->id }}</span></td>
                <td>{{ $pkg->name }}</td>
                <td>{!! nl2br(e($pkg->description ?? '-')) !!}</td>
                <td class="channels">
                    @if($pkg->relationLoaded('channels') ? $pkg->channels->count() : $pkg->channels()->count())
                        {{ ($pkg->relationLoaded('channels') ? $pkg->channels : $pkg->channels()->get())->pluck('channel_name')->join(', ') }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $pkg->active == 'Yes' ? 'Active' : 'Inactive' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
