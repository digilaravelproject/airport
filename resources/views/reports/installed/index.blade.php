@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php
        $page_title = "Installed Boxes (with Channel Packages)";
        $sub_title  = "Reports";
    ?>

    @php
        // --- sorting helpers (same pattern as your live/index view) ---
        function nextDirInstalled($col) {
            $currentSort = request('sort','id');
            $currentDir  = request('direction','desc');
            if ($currentSort === $col) return $currentDir === 'asc' ? 'desc' : 'asc';
            return 'asc';
        }
        function sortIconInstalled($col) {
            $currentSort = request('sort','id');
            $currentDir  = request('direction','desc');
            if ($currentSort !== $col) return 'fas fa-sort text-muted';
            return $currentDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
        function sortUrlInstalled($col) {
            $params = request()->all();
            $params['sort'] = $col;
            $params['direction'] = nextDirInstalled($col);
            return request()->fullUrlWithQuery($params);
        }
    @endphp

    <!-- Page-Title -->
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
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Total Installed Boxes</h5>
        </div>

        <div class="card-body">

            {{-- Client Filter --}}
            <form method="GET" action="{{ route('installed-reports.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">-- All Clients --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ (string)request('client_id') === (string)$client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- preserve current sort state when filtering --}}
                <input type="hidden" name="sort" value="{{ request('sort','id') }}">
                <input type="hidden" name="direction" value="{{ request('direction','desc') }}">
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('installed-reports.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            <form id="selectionForm" method="POST" target="_self">
                @csrf

                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <a href="{{ sortUrlInstalled('box_id') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Box ID <i class="{{ sortIconInstalled('box_id') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlInstalled('box_ip') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Box IP <i class="{{ sortIconInstalled('box_ip') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlInstalled('box_mac') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        MAC ID <i class="{{ sortIconInstalled('box_mac') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlInstalled('box_serial_no') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Serial No <i class="{{ sortIconInstalled('box_serial_no') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlInstalled('client_name') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Client <i class="{{ sortIconInstalled('client_name') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlInstalled('location') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Establishment <i class="{{ sortIconInstalled('location') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlInstalled('box_model') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Box Model <i class="{{ sortIconInstalled('box_model') }}"></i>
                                    </a>
                                </th>

                                <th>Packages</th>
                                <th>Status</th>
                                <th>
                                    <a href="{{ sortUrlInstalled('warranty_date') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Warranty Date <i class="{{ sortIconInstalled('warranty_date') }}"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inventories as $idx => $inv)
                                <tr data-id="{{ $inv->id }}">
                                    <td><span class="badge bg-secondary">{{ $inv->box_id }}</span></td>
                                    <td>{{ $inv->box_ip }}</td>
                                    <td>{{ $inv->box_mac }}</td>
                                    <td>{{ $inv->box_serial_no }}</td>
                                    <td>
                                        @if($inv->client)
                                            {{ $inv->client->name }}
                                        @else
                                            <span class="text-muted">No client</span>
                                        @endif
                                    </td>
                                    <td>{{ $inv->location }}</td>
                                    <td>{{ $inv->box_model }}</td>

                                    <td>
                                        @if($inv->packages->count())
                                            {{ $inv->packages->pluck('name')->join(', ') }}
                                        @else
                                            <span class="text-muted">No packages</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->client)
                                            <span class="badge bg-success">Installed</span>
                                        @else
                                            <span class="badge bg-secondary">In Stock</span>
                                        @endif
                                    </td>
                                    <td>{{ $inv->warranty_date ? \Carbon\Carbon::parse($inv->warranty_date)->format('Y-m-d') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No installed boxes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            id="btnViewSelected"
                            data-action="{{ route('installed-reports.preview') }}">
                        View Selected
                    </button>

                    <button type="button"
                            class="btn btn-dark"
                            id="btnDownloadSelected"
                            data-action="{{ route('installed-reports.download') }}">
                        Download Selected
                    </button>

                    <a href="{{ route('installed-reports.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('selectionForm');
    const btnView = document.getElementById('btnViewSelected');
    const btnDownload = document.getElementById('btnDownloadSelected');

    // Collect visible row ids from the rendered table (tbody rows with data-id)
    function collectVisibleIds() {
        return Array.from(document.querySelectorAll('table tbody tr[data-id]'))
            .map(tr => tr.dataset.id)
            .filter(Boolean);
    }

    // POST form data with selected_ids[]=... (all visible rows)
    function openInNewTab(actionUrl, isDownload = false) {
        const ids = collectVisibleIds();
        if (!ids.length) {
            alert('No records to send.'); return;
        }

        const formData = new FormData();
        // append CSRF token
        formData.append('_token', '{{ csrf_token() }}');

        ids.forEach(id => formData.append('selected_ids[]', id));

        const newTab = window.open('', '_blank');
        fetch(actionUrl, {
            method: 'POST',
            headers: { /* CSRF already in formData */ },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Server error: ' + res.status);
            return res.blob();
        })
        .then(blob => {
            const fileURL = URL.createObjectURL(blob);
            if (isDownload) {
                const a = document.createElement('a');
                a.href = fileURL;
                a.download = 'installed_boxes_selected.pdf';
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
@endsection


