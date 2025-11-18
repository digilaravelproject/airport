@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php $page_title = "Live Boxes"; $sub_title = "reports"; ?>

    @php
        function nextDirUti($col) {
            $currentSort = request('sort','id');
            $currentDir  = request('direction','desc');
            if ($currentSort === $col) return $currentDir === 'asc' ? 'desc' : 'asc';
            return 'asc';
        }
        function sortIconUti($col) {
            $currentSort = request('sort','id');
            $currentDir  = request('direction','desc');
            if ($currentSort !== $col) return 'fas fa-sort text-muted';
            return $currentDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
        function sortUrlUti($col) {
            $params = request()->all();
            $params['sort'] = $col;
            $params['direction'] = nextDirUti($col);
            return request()->fullUrlWithQuery($params);
        }
    @endphp

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">{{ $sub_title }}</a></li>
                        <li class="breadcrumb-item active">{{ $page_title }}</li>
                    </ol>
                </div>
                <h4 class="page-title">{{ $page_title }}</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3 mb-0">
            <i class="fas fa-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-3 mb-0">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Full-width table -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Live Boxes</h5>
                    <!-- <form method="GET" action="{{ route('live-reports.index') }}" class="d-flex">
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               class="form-control form-control-sm me-2" placeholder="Search">
                        <input type="hidden" name="sort" value="{{ request('sort','id') }}">
                        <input type="hidden" name="direction" value="{{ request('direction','desc') }}">
                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('live-reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form> -->
                </div>

                <div class="card-body">
                    {{-- One form to hold selections --}}
                    <form id="selectionForm" method="POST" target="_self">
                        @csrf

                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label fw-semibold" for="selectAll">
                                    Select All Records
                                </label>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_id') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Box ID <i class="{{ sortIconUti('box_id') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_ip') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Box IP <i class="{{ sortIconUti('box_ip') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_mac') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                MAC <i class="{{ sortIconUti('box_mac') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_serial_no') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Serial No <i class="{{ sortIconUti('box_serial_no') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('client_name') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Client <i class="{{ sortIconUti('client_name') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('location') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Establishment <i class="{{ sortIconUti('location') }}"></i>
                                            </a>
                                        </th>
                                        <th>Status</th>
                                        <th>App Status</th>
                                        <th style="width:240px;">Active Channel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($inventories as $key => $inventory)
                                    @php
                                        $isOnline = $inventory->is_online ?? false;
                                        $streamStatus = $inventory->stream_status ?? null;

                                       
                                        $candidateUrl = null;
                                        if (!empty($inventory->channel_source_in) && preg_match('#^(https?|udp|rtp|rtsp)://#i', $inventory->channel_source_in)) {
                                            $candidateUrl = $inventory->channel_source_in;
                                        } elseif (!empty($inventory->stream_url) && preg_match('#^(https?|udp|rtp|rtsp)://#i', $inventory->stream_url)) {
                                            $candidateUrl = $inventory->stream_url;
                                        } elseif (!empty($inventory->channel_source_in) && preg_match('#^[0-9\.:\[\]]+:[0-9]+$#', $inventory->channel_source_in)) {
                                            $candidateUrl = 'udp://'.$inventory->channel_source_in;
                                        }
                                    @endphp
                                    @php
                                        $raw = preg_replace('#^udp://#i', '', $candidateUrl);
                                        $displayName = $candidateUrl;

                                        if (!empty($candidateUrl)) {
                                            $channel = \App\Models\Channel::where('channel_source_in', $raw)->first();
                                            if ($channel && $channel->channel_name) {
                                                $displayName = $channel->channel_name;
                                            }
                                        }
                                    @endphp

                                    <tr data-inventory-id="{{ $inventory->id }}">
                                        <td onclick="event.stopPropagation();">
                                            <input type="checkbox" class="row-check" name="selected_ids[]" value="{{ $inventory->id }}">
                                        </td>
                                        <td><span class="badge bg-success">{{ $inventory->box_id }}</span></td>
                                        <td>{{ $inventory->box_ip }}</td>
                                        <td>{{ $inventory->box_mac }}</td>
                                        <td>{{ $inventory->box_serial_no }}</td>
                                        <td>{{ $inventory->client?->name }}</td>
                                        <td>{{ $inventory->location }}</td>

                                        <td>
                                            <span class="badge {{ $isOnline ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $isOnline ? 'Online' : 'Offline' }}
                                            </span>
                                        </td>

                                        <td class="stream-status">
                                            {{ $streamStatus ?? '—' }}
                                        </td>

                                        <td onclick="event.stopPropagation();">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="flex-grow-1 channel-display">
                                                    @if($streamStatus != '' && $streamStatus == 'Amino Zapper')
                                                        @if($isOnline && $candidateUrl)
                                                            <button onclick="launchPlayer('{{ preg_replace('#^udp://#i', '', $candidateUrl) }}')" type="button"
                                                                    class="btn btn-sm btn-link btn-play-vlc"
                                                                    data-inventory-id="{{ $inventory->id }}"
                                                                    data-url="{{ $candidateUrl }}">
                                                                {{ $displayName ?: $candidateUrl }}
                                                            </button>
                                                        @elseif($isOnline && !empty($displayName))
                                                            <button onclick="launchPlayer('{{ preg_replace('#^udp://#i', '', $inventory->channel_source_in) }}')" class="text-truncate d-inline-block channel-name" style="max-width:160px;" title="{{ $inventory->channel_source_in ?? $inventory->stream_url ?? '' }}">
                                                                {{ $displayName }}
                                                            </button>
                                                        @elseif($isOnline && !empty($inventory->channel_source_in))
                                                            <button onclick="launchPlayer('{{ preg_replace('#^udp://#i', '', $inventory->channel_source_in) }}')" class="text-truncate d-inline-block channel-name" style="max-width:160px;">
                                                                {{ $inventory->channel_source_in }}
                                                            </button>
                                                        @else
                                                            <span class="text-muted channel-placeholder">—</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted channel-placeholder">—</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="13" class="text-center text-muted">No boxes found.</td></tr>
                                @endforelse
                                </tbody>
                            </table>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    @if($inventories->total())
                                        Showing {{ $inventories->firstItem() }}–{{ $inventories->lastItem() }} of {{ $inventories->total() }}
                                    @endif
                                </div>
                                <div>
                                    {{ $inventories->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    id="btnViewSelected"
                                    data-action="{{ route('live-reports.preview') }}">
                                View Selected
                            </button>

                            <button type="button"
                                    class="btn btn-dark"
                                    id="btnDownloadSelected"
                                    data-action="{{ route('live-reports.download') }}">
                                Download Selected
                            </button>

                            <a href="{{ route('live-reports.index') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form> {{-- /selectionForm --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JS: selection + open-as-blob + per-row fetch (safe error handling) --}}
<script>
    // Launch VLC via Python helper
function launchPlayer(url){
    fetch(`http://localhost:5000/?url=udp://@${url}`)
        .then(resp=>resp.json())
        .then(data=>console.log(data.status))
        .catch(err=>console.error('Failed to launch player:', err));
}

document.addEventListener('DOMContentLoaded', function () {
    const selectAll   = document.getElementById('selectAll');
    const form        = document.getElementById('selectionForm');
    const btnView     = document.getElementById('btnViewSelected');
    const btnDownload = document.getElementById('btnDownloadSelected');

    const getRowChecks = () => Array.from(document.querySelectorAll('input.row-check'));

    function setAllRows(state) {
        getRowChecks().forEach(cb => cb.checked = state);
        selectAll.indeterminate = false;
        selectAll.checked = state;
    }

    selectAll?.addEventListener('change', (e) => setAllRows(e.target.checked));

    document.addEventListener('change', (e) => {
        if (!e.target.classList.contains('row-check')) return;
        const cbs = getRowChecks();
        const checkedCount = cbs.filter(cb => cb.checked).length;
        if (checkedCount === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        } else if (checkedCount === cbs.length) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = true;
        }
    });

    function openInNewTab(actionUrl, isDownload = false) {
        const formData = new FormData(form);
        const newTab = window.open('', '_blank');
        fetch(actionUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.blob())
        .then(blob => {
            const fileURL = URL.createObjectURL(blob);
            if (isDownload) {
                const a = document.createElement('a');
                a.href = fileURL;
                a.download = 'online_boxes_selected.pdf';
                a.click();
                newTab.close();
            } else {
                newTab.location.href = fileURL;
            }
        })
        .catch(err => {
            newTab.document.write('<p style="color:red;">Error generating PDF.</p>');
            console.error(err);
        });
    }

    btnView?.addEventListener('click', (e) => {
        e.preventDefault();
        openInNewTab(btnView.dataset.action, false);
    });

    btnDownload?.addEventListener('click', (e) => {
        e.preventDefault();
        openInNewTab(btnDownload.dataset.action, true);
    });

    // ------- Per-row fetch channel -------
    document.querySelectorAll('.btn-fetch-channel').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const invId = btn.dataset.inventoryId;
            if (!invId) return;

            const origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            // POST to the endpoint; note route must exist (see routes/web.php)
            const url = '{{ url("live-reports") }}/' + invId + '/active-channel';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                // If server returned non-JSON (like a Laravel error page) treat as failure.
                if (!res.ok) {
                    let msg = 'Fetch failed (server error: ' + res.status + ')';
                    try {
                        const j = await res.json();
                        if (j && j.message) msg = j.message;
                    } catch (_) {}
                    throw new Error(msg);
                }

                const json = await res.json().catch(() => null);
                if (!json) throw new Error('Invalid JSON response');

                const row = document.querySelector('tr[data-inventory-id="'+invId+'"]');
                if (!row) throw new Error('Row not found');

                const display = row.querySelector('.channel-display');
                const statusCell = row.querySelector('.stream-status');

                if (!display || !statusCell) throw new Error('UI cells missing');

                if (json.success) {
                    // update stream status
                    statusCell.textContent = json.stream_status ?? (json.name ? json.name : '—');

                    // update udp link / channel name (channelName is used if server found a channel row)
                    if (json.channel_name) {
                        // display channel name and link to UDP if server provided url
                        if (json.url) {
                            display.innerHTML = '<a href="'+json.url+'" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-link channel-link">' + json.channel_name + '</a>';
                        } else {
                            display.innerHTML = '<span class="text-truncate d-inline-block channel-name" style="max-width:160px;">' + json.channel_name + '</span>';
                        }
                    } else if (json.url) {
                        display.innerHTML = '<a href="'+json.url+'" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-link channel-link">' + (json.name ? json.name : json.url) + '</a>';
                    } else if (json.name) {
                        display.innerHTML = '<span class="text-truncate d-inline-block channel-name" style="max-width:160px;">' + json.name + '</span>';
                    } else {
                        display.innerHTML = '<span class="text-muted channel-placeholder">—</span>';
                    }
                } else {
                    const msg = json.message || 'Fetch failed';
                    display.innerHTML = '<span class="text-danger small">'+msg+'</span>';
                    statusCell.textContent = json.stream_status ?? '—';
                }
            } catch (err) {
                console.error('Fetch channel error', err);
                const row = document.querySelector('tr[data-inventory-id="'+invId+'"]');
                if (row) {
                    const display = row.querySelector('.channel-display');
                    const statusCell = row.querySelector('.stream-status');
                    if (display) display.innerHTML = '<span class="text-danger small">Fetch failed</span>';
                    if (statusCell) statusCell.textContent = '—';
                }
            } finally {
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }
        });
    });
});
</script>
@endsection
