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
                    <form method="GET" action="{{ route('live-reports.index') }}" class="d-flex">
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               class="form-control form-control-sm me-2" placeholder="Search">
                        <input type="hidden" name="sort" value="{{ request('sort','id') }}">
                        <input type="hidden" name="direction" value="{{ request('direction','desc') }}">
                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('live-reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
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
                                        <th>No</th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_id') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Box ID <i class="{{ sortIconUti('box_id') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_model') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Model <i class="{{ sortIconUti('box_model') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_serial_no') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Serial No <i class="{{ sortIconUti('box_serial_no') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_mac') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                MAC <i class="{{ sortIconUti('box_mac') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_ip') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Box IP <i class="{{ sortIconUti('box_ip') }}"></i>
                                            </a>
                                        </th>
                                        <th>Status</th>
                                        <th>
                                            <a href="{{ sortUrlUti('box_fw') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Firmware <i class="{{ sortIconUti('box_fw') }}"></i>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ sortUrlUti('client_name') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                                Client <i class="{{ sortIconUti('client_name') }}"></i>
                                            </a>
                                        </th>
                                        {{-- NEW: Active Channel action column --}}
                                        <th style="width:110px;">UDP Stream</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($inventories as $key => $inventory)
                                    @php $isOnline = $inventory->is_online ?? false; @endphp
                                    <tr>
                                        <td onclick="event.stopPropagation();">
                                            <input type="checkbox" class="row-check" name="selected_ids[]" value="{{ $inventory->id }}">
                                        </td>
                                        <td>{{ ($inventories->firstItem() ?? 0) + $key }}</td>
                                        <td><span class="badge bg-success">{{ $inventory->box_id }}</span></td>
                                        <td>{{ $inventory->box_model }}</td>
                                        <td>{{ $inventory->box_serial_no }}</td>
                                        <td>{{ $inventory->box_mac }}</td>
                                        <td>{{ $inventory->box_ip }}</td>
                                        <td>
                                            <span class="badge {{ $isOnline ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $isOnline ? 'Online' : 'Offline' }}
                                            </span>
                                        </td>
                                        <td>{{ $inventory->box_fw }}</td>
                                        <td>{{ $inventory->client?->name }}</td>
                                        <td onclick="event.stopPropagation();">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                onclick="playActiveChannel({{ $inventory->id }})"
                                                title="Discover and play in VLC">
                                                <i class="fas fa-play"></i> Play
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="text-center text-muted">No boxes found.</td></tr>
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

{{-- Selection + open-as-blob for preview/download --}}
<script>
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
});
</script>

{{-- NEW: Active Channel discovery + client VLC helper --}}
<script>
const PY_HELPER_BASE = 'http://127.0.0.1:5000'; // Python helper runs on the CLIENT machine

async function playActiveChannel(inventoryId) {
    const btns = document.querySelectorAll('button[onclick^="playActiveChannel"]');
    btns.forEach(b => b.disabled = true);

    try {
        // 1) Ask LiveReportController for the active channel URL
        const r1 = await fetch(`{{ route('live-reports.activeChannel', ['inventory' => '___ID___']) }}`.replace('___ID___', inventoryId), {
            headers: { 'Accept': 'application/json' }
        });
        const j1 = await r1.json();
        if (!j1.success) {
            alert(j1.message || 'Active channel not found.');
            return;
        }
        const url = j1.url;

        // 2) Ask local Python helper to launch VLC
        const params = new URLSearchParams({ url });
        const r2 = await fetch(`${PY_HELPER_BASE}/?` + params.toString(), { method: 'GET' });

        if (!r2.ok) {
            const t = await r2.text();
            alert('Failed to contact local VLC helper.\nIs it running?\n\nResponse: ' + t);
            return;
        }

        const j2 = await r2.json().catch(() => ({}));
        if (j2 && j2.status) {
            console.log(j2.status);
        } else if (j2 && j2.error) {
            alert('Local helper error: ' + j2.error);
        } else {
            console.log('VLC launch requested.');
        }
    } catch (e) {
        alert('Error: ' + (e && e.message ? e.message : e));
    } finally {
        btns.forEach(b => b.disabled = false);
    }
}
</script>
@endsection
