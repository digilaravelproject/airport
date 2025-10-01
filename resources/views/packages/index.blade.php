@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php
        $page_title = "Packages";
        $sub_title = "Channel Management";
    ?>
    <!-- Page Title -->
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

    <!-- Package List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Package List</h5>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#packageModal" onclick="openForm('add')">
                        + Add Package
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Channels</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($packages as $key => $package)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td><span class="badge bg-secondary">{{ $package->id }}</span></td>
                                        <td>{{ $package->name }}</td>
                                        <td>
                                            @if($package->channels->count())
                                                {{ $package->channels->pluck('channel_name')->join(', ') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $package->active == 'Yes' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $package->active }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#packageModal"
                                                onclick='openForm("edit", @json($package))'>
                                                Edit
                                            </button>
                                            <button class="btn btn-info btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#packageModal"
                                                onclick='openForm("view", @json($package))'>
                                                View
                                            </button>
                                            <form method="POST" action="{{ route('packages.destroy', $package->id) }}" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            {{ $packages->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Modal -->
    <div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <form method="POST" id="packageForm" action="{{ route('packages.store') }}">
                @csrf
                <div id="formMethod"></div>

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="packageModalLabel">Add Package</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Package ID -->
                    <div class="mb-3">
                        <label class="form-label">Package ID</label>
                        <input type="text" id="package_id" class="form-control" readonly>
                    </div>

                    <!-- Package Name -->
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" readonly>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Channels -->
                    <div class="mb-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <label class="form-label mb-0">Channels</label>

                            <div class="d-flex gap-3 align-items-center">
                                <!-- Filter -->
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0 small text-muted">Filter:</label>
                                    <select id="channel_filter" class="form-select form-select-sm" style="min-width: 140px;" disabled>
                                        <option value="all" selected>All</option>
                                        <option value="paid">Paid</option>
                                        <option value="free">Free</option>
                                    </select>
                                </div>

                                <!-- Select All -->
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select_all_channels" disabled>
                                    <label class="form-check-label" for="select_all_channels">Select all (visible)</label>
                                </div>
                            </div>
                        </div>

                        <div id="channelsWrapper" class="border rounded p-2 mt-2" style="max-height:240px; overflow-y:auto;">
                            @foreach($channels as $ch)
                                @php
                                    $type = strtolower(trim($ch->channel_type));
                                @endphp
                                <div class="form-check channel-row" data-type="{{ $type }}">
                                    <input class="form-check-input channel-checkbox" type="checkbox" name="channel_id[]" 
                                        value="{{ $ch->id }}" id="channel_{{ $ch->id }}" disabled>
                                    <label class="form-check-label" for="channel_{{ $ch->id }}">
                                        {{ $ch->channel_name }}
                                        <span class="badge rounded-pill {{ $type === 'paid' ? 'text-bg-warning' : 'text-bg-secondary' }} ms-2">
                                            {{ ucfirst($type) }}
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="small text-muted mt-1">
                            <span id="visibleCount">0</span> visible •
                            <span id="selectedCount">0</span> selected
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Active</label>
                        <select name="active" id="active" class="form-select" disabled>
                            <option value="Yes" {{ old('active') == 'Yes' ? 'selected' : '' }}>Yes</option>
                            <option value="No"  {{ old('active') == 'No'  ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" id="saveBtn" class="btn btn-dark px-4">Save</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const form            = document.getElementById('packageForm');
    const saveBtn         = document.getElementById('saveBtn');
    const modalTitle      = document.getElementById('packageModalLabel');
    const methodDiv       = document.getElementById('formMethod');
    const pkgIdInput      = document.getElementById('package_id');
    const nameInput       = document.getElementById('name');
    const activeSelect    = document.getElementById('active');
    const channelsWrapper = document.getElementById('channelsWrapper');
    const filterSelect    = document.getElementById('channel_filter');
    const allCb           = document.getElementById('select_all_channels');
    const selectedCountEl = document.getElementById('selectedCount');
    const visibleCountEl  = document.getElementById('visibleCount');

    function allChannelRows() {
        return Array.from(channelsWrapper.querySelectorAll('.channel-row'));
    }
    function visibleChannelBoxes() {
        return allChannelRows().filter(r => !r.classList.contains('d-none'))
                               .map(r => r.querySelector('.channel-checkbox'));
    }

    function updateCountsAndMaster() {
        const boxes = visibleChannelBoxes();
        const total = boxes.length;
        const checked = boxes.filter(cb => cb.checked).length;

        visibleCountEl.textContent  = total;
        selectedCountEl.textContent = checked;

        allCb.indeterminate = (checked > 0 && checked < total);
        allCb.checked = (total > 0 && checked === total);
        if (total === 0) { allCb.indeterminate = false; allCb.checked = false; }
    }

    function applyFilter() {
        const val = filterSelect.value;
        allChannelRows().forEach(row => {
            const type = (row.getAttribute('data-type') || '').toLowerCase();
            const show = (val === 'all' || val === type);
            row.classList.toggle('d-none', !show);
        });
        updateCountsAndMaster();
    }

    function setDisabled(disabled) {
        nameInput.readOnly = disabled;
        activeSelect.disabled = disabled;
        filterSelect.disabled = disabled;
        allCb.disabled = disabled;
        allChannelRows().forEach(r => r.querySelector('.channel-checkbox').disabled = disabled);
    }

    // Public function for modal buttons
    window.openForm = function(mode, data = null) {
        form.reset();
        methodDiv.innerHTML = '';
        form.action = "{{ route('packages.store') }}";
        saveBtn.style.display = 'none';
        pkgIdInput.value = '';
        nameInput.value = '';
        activeSelect.value = 'Yes';
        allChannelRows().forEach(r => r.querySelector('.channel-checkbox').checked = false);
        allCb.checked = false; allCb.indeterminate = false;

        filterSelect.value = 'all';
        allChannelRows().forEach(r => r.classList.remove('d-none'));
        applyFilter();

        if (mode === 'add') {
            modalTitle.innerText = "Add Package";
            saveBtn.style.display = 'inline-block';
            setDisabled(false);
        }

        if (mode === 'edit' && data) {
            modalTitle.innerText = "Edit Package";
            form.action = "{{ url('packages') }}/" + data.id;
            methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            saveBtn.style.display = 'inline-block';
            pkgIdInput.value = data.id ?? '';
            nameInput.value  = data.name ?? '';
            activeSelect.value = data.active ?? 'Yes';

            setDisabled(false);
            const selectedIds = (data.channels || []).map(ch => String(ch.id));
            allChannelRows().forEach(r => {
                const cb = r.querySelector('.channel-checkbox');
                cb.checked = selectedIds.includes(cb.value);
            });
            applyFilter();
        }

        if (mode === 'view' && data) {
            modalTitle.innerText = "View Package";
            pkgIdInput.value = data.id ?? '';
            nameInput.value  = data.name ?? '';
            activeSelect.value = data.active ?? 'Yes';

            const selectedIds = (data.channels || []).map(ch => String(ch.id));
            allChannelRows().forEach(r => {
                const cb = r.querySelector('.channel-checkbox');
                cb.checked = selectedIds.includes(cb.value);
            });
            setDisabled(true);
            applyFilter();
        }
    };

    // Wire events
    filterSelect.addEventListener('change', applyFilter);
    allCb.addEventListener('change', () => {
        const checked = allCb.checked;
        visibleChannelBoxes().forEach(cb => cb.checked = checked);
        updateCountsAndMaster();
    });
    channelsWrapper.addEventListener('change', e => {
        if (e.target.classList.contains('channel-checkbox')) updateCountsAndMaster();
    });

    document.addEventListener('DOMContentLoaded', applyFilter);
})();
</script>
@endsection
