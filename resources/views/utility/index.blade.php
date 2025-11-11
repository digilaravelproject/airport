@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php $page_title = "Inventories (Online Only)"; $sub_title = "Setup Boxes"; ?>

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

    @if(session('error'))
        <div class="alert alert-danger mt-3 mb-0 alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left: Inventories Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Online Inventories</h5>
                    <form method="GET" action="{{ route('utility.online') }}" class="d-flex">
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               class="form-control form-control-sm me-2" placeholder="Search">
                        <input type="hidden" name="sort" value="{{ request('sort','id') }}">
                        <input type="hidden" name="direction" value="{{ request('direction','desc') }}">
                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('utility.online') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
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
                                    <!-- NEW: Box IP (sortable) -->
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
                                    {{-- Active Channel (play) --}}
                                    <th>Active Channel</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse ($inventories as $key => $inventory)
                                @php $isOnline = $inventory->is_online ?? false; @endphp
                                <tr onclick="window.location='?inventory_id={{ $inventory->id }}&page={{ request('page',1) }}&search={{ urlencode($search ?? '') }}&sort={{ request('sort','id') }}&direction={{ request('direction','desc') }}'" style="cursor:pointer;">
                                    <td>{{ ($inventories->firstItem() ?? 0) + $key }}</td>
                                    <td><span class="badge bg-success">{{ $inventory->box_id }}</span></td>
                                    <td>{{ $inventory->box_model }}</td>
                                    <td>{{ $inventory->box_serial_no }}</td>
                                    <td>{{ $inventory->box_mac }}</td>
                                    <!-- NEW: Box IP value -->
                                    <td>{{ $inventory->box_ip }}</td> {{-- ← if your attribute name differs, update here --}}
                                    <td>
                                        <span class="badge {{ $isOnline ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </span>
                                    </td>
                                    <td>{{ $inventory->box_fw }}</td>
                                    <td>{{ $inventory->client?->name }}</td>
                                    {{-- Active channel play button (does not steal row click) --}}
                                    <td onclick="event.stopPropagation();">
                                        <button
                                            class="btn btn-sm btn-outline-primary"
                                            onclick="playActiveChannel({{ $inventory->id }})"
                                            title="Discover and play in VLC">
                                            <i class="fas fa-play"></i> Play
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted">No boxes found.</td></tr>
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
                </div>
            </div>
        </div>

        <!-- Right: Selected Box Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Box Details</h6>
                </div>
                <div class="card-body">
                    @if($selectedInventory)
                        <div class="mb-3">
                            <label class="form-label">Box ID</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_id }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_model }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">MAC</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_mac }}" readonly>
                        </div>
                        <!-- NEW: Box IP in details -->
                        <div class="mb-3">
                            <label class="form-label">Box IP</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_ip }}" readonly> {{-- ← update attribute if needed --}}
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Serial No</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_serial_no }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Firmware</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_fw }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">RCU Model</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_remote_model }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Warranty Date</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->warranty_date?->format('Y-m-d') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->client?->name }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->location }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <div>
                                @if($selectedInventory->photo)
                                    <img src="{{ asset('storage/'.$selectedInventory->photo) }}" class="img-thumbnail" width="120">
                                @else
                                    <span class="text-muted">No photo</span>
                                @endif
                            </div>
                        </div>

                        {{-- Active Channel (readonly) --}}
                        <div class="mb-3">
                            <label class="form-label">Active Channel URL</label>
                            <input type="text" id="activeChannelField" class="form-control"
                                   value="{{ $selectedActiveChannel ?? '' }}" readonly
                                   placeholder="Click Play in the table to fetch">
                        </div>

                    @else
                        <div class="text-muted">Click a row to view details.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JS – discover active channel then call local Python VLC helper --}}
<script>
const PY_HELPER_BASE = 'http://127.0.0.1:5000'; // The Python helper must run on the CLIENT computer

async function playActiveChannel(inventoryId) {
    const btns = document.querySelectorAll('button[onclick^="playActiveChannel"]');
    btns.forEach(b => b.disabled = true);

    try {
        // 1) Ask Laravel for the active channel URL (server discovers per-device)
        const r1 = await fetch(`{{ route('utility.activeChannel', ['inventory' => '___ID___']) }}`.replace('___ID___', inventoryId), {
            headers: { 'Accept': 'application/json' }
        });
        const j1 = await r1.json();
        if (!j1.success) {
            alert(j1.message || 'Active channel not found.');
            return;
        }
        const url = j1.url;

        // Put it into the right panel field if present
        const acField = document.getElementById('activeChannelField');
        if (acField) acField.value = url;

        // 2) Ask local Python helper to launch VLC on the client machine
        const params = new URLSearchParams({ url });
        const r2 = await fetch(`${PY_HELPER_BASE}/?` + params.toString(), { method: 'GET' });

        if (!r2.ok) {
            const t = await r2.text();
            alert('Failed to contact local VLC helper.\nIs it running?\n\nResponse: ' + t);
            return;
        }

        const j2 = await r2.json().catch(() => ({}));
        if (j2 && j2.status) {
            // Optional toast
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
