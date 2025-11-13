@extends('layouts.app')

@section('content')
<style>
    /* NEW: keep Channel Name in a single line with ellipsis (no wrap) */
    .table tbody td:nth-child(3) {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px; /* adjust if you need a different visible width */
    }
</style>
<div class="container-fluid">
    <?php $page_title = "Channels"; $sub_title = "Reports"; ?>

    @php
        // Helpers to build sort links + icons (defaults mimic the original: sort by id ASC)
        function nextDirChan($col) {
            $currentSort = request('sort','id');
            $currentDir  = request('direction','asc');
            if ($currentSort === $col) return $currentDir === 'asc' ? 'desc' : 'asc';
            return 'asc';
        }
        function sortIconChan($col) {
            $currentSort = request('sort','id');
            $currentDir  = request('direction','asc');
            if ($currentSort !== $col) return 'fas fa-sort text-muted';
            return $currentDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
        function sortUrlChan($col) {
            $params = request()->all();
            $params['sort'] = $col;
            $params['direction'] = nextDirChan($col);
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Channels</h5>

            {{-- Per-page selector (pagination) --}}
            <form method="GET" action="{{ route('channel-reports.index') }}" class="d-flex align-items-center gap-2">
                <label class="text-muted mb-0">Per page</label>
                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([10,25,50,100] as $pp)
                        <option value="{{ $pp }}" {{ (int)request('per_page', $perPage) === $pp ? 'selected' : '' }}>
                            {{ $pp }}
                        </option>
                    @endforeach
                </select>
                {{-- Preserve current params (including sort/direction) --}}
                @foreach(request()->except('per_page') as $k=>$v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
            </form>
        </div>

        <div class="card-body">

            {{-- Channel Genre Filter --}}
            <form method="GET" action="{{ route('channel-reports.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Channel Genre</label>
                    <select name="channel_genre" class="form-select">
                        <option value="">-- All Genres --</option>
                        @foreach($genres as $g)
                            <option value="{{ $g }}" {{ request('channel_genre') === $g ? 'selected' : '' }}>
                                {{ $g }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('channel-reports.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>

                {{-- keep current per_page + sort on search --}}
                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage) }}">
                <input type="hidden" name="sort" value="{{ request('sort','id') }}">
                <input type="hidden" name="direction" value="{{ request('direction','asc') }}">
            </form>

            <form id="selectionForm" method="POST" target="_self">
                @csrf
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label fw-semibold" for="selectAll">Select All Records</label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;"></th>
                                <th>
                                    <a href="{{ sortUrlChan('id') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        ID <i class="{{ sortIconChan('id') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlChan('channel_name') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Name <i class="{{ sortIconChan('channel_name') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlChan('broadcast') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Broadcaster <i class="{{ sortIconChan('broadcast') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlChan('channel_genre') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Genre <i class="{{ sortIconChan('channel_genre') }}"></i>
                                    </a>
                                </th>

                                {{-- NEW fields: resolution, type, source in, source details, language, active --}}
                                <th>
                                    <a href="{{ sortUrlChan('channel_resolution') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Resolution <i class="{{ sortIconChan('channel_resolution') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlChan('channel_type') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Type <i class="{{ sortIconChan('channel_type') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlChan('channel_source_in') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Source In <i class="{{ sortIconChan('channel_source_in') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlChan('channel_source_details') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Source Details <i class="{{ sortIconChan('channel_source_details') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlChan('language') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Language <i class="{{ sortIconChan('language') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlChan('active') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Active <i class="{{ sortIconChan('active') }}"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($channels as $i => $ch)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-check" name="selected_ids[]" value="{{ $ch->id }}">
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $ch->id }}</span></td>
                                    <td>{{ $ch->channel_name }}</td>
                                    <td>{{ $ch->broadcast ?? '-' }}</td>
                                    <td>{{ $ch->channel_genre ?? '-' }}</td>

                                    {{-- New columns --}}
                                    <td>{{ $ch->channel_resolution ?? '-' }}</td>
                                    <td>{{ $ch->channel_type ?? '-' }}</td>
                                    <td>{{ $ch->channel_source_in ?? '-' }}</td>
                                    <td style="max-width:240px; white-space:normal;">{{ $ch->channel_source_details ?? '-' }}</td>
                                    <td>{{ $ch->language ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $ch->active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $ch->active ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted">No channels found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination links --}}
                <div class="mt-3">
                    {{ $channels->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            id="btnViewSelected"
                            data-action="{{ route('channel-reports.preview') }}">
                        View Selected
                    </button>

                    <button type="button"
                            class="btn btn-dark"
                            id="btnDownloadSelected"
                            data-action="{{ route('channel-reports.download') }}">
                        Download Selected
                    </button>

                    <a href="{{ route('channel-reports.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('selectionForm');
    const master = document.getElementById('selectAll');
    const btnView = document.getElementById('btnViewSelected');
    const btnDownload = document.getElementById('btnDownloadSelected');

    if (!form || !master) return;

    const rowBoxes = () => form.querySelectorAll('.row-check');

    function refreshMasterState() {
        const boxes = Array.from(rowBoxes());
        const total = boxes.length;
        const checked = boxes.filter(cb => cb.checked).length;
        master.checked = total > 0 && checked === total;
        master.indeterminate = checked > 0 && checked < total;
    }

    master.addEventListener('change', () => {
        rowBoxes().forEach(cb => cb.checked = master.checked);
        refreshMasterState();
    });

    form.addEventListener('change', e => {
        if (e.target && e.target.classList.contains('row-check')) refreshMasterState();
    });

    refreshMasterState();

    // Open PDF in new tab via fetch so the current page doesn't "load" forever
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
                a.download = 'channels_selected.pdf';
                a.click();
                newTab.close();
            } else {
                newTab.location.href = fileURL;
            }
        })
        .catch(err => {
            newTab.document.write('<p style="color:red;padding:1rem;">Error generating PDF.</p>');
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
});
</script>
@endsection
