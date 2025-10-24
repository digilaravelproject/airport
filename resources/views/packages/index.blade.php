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

                            <div class="d-flex gap-3 align-items-center flex-wrap">
                                <!-- Filter: Type -->
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0 small text-muted">Type:</label>
                                    <select id="channel_filter_type" class="form-select form-select-sm" style="min-width: 120px;" disabled>
                                        <option value="all" selected>All</option>
                                        <option value="paid">Paid</option>
                                        <option value="free">Free</option>
                                    </select>
                                </div>

                                <!-- Filter: Genre -->
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0 small text-muted">Genre:</label>
                                    <select id="channel_filter_genre" class="form-select form-select-sm" style="min-width: 140px;" disabled>
                                        <option value="all" selected>All</option>
                                        @foreach($genres as $genre)
                                            <option value="{{ strtolower($genre) }}">{{ ucfirst($genre) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filter: Language -->
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0 small text-muted">Language:</label>
                                    <select id="channel_filter_language" class="form-select form-select-sm" style="min-width: 140px;" disabled>
                                        <option value="all" selected>All</option>
                                        @foreach($languages as $lang)
                                            <option value="{{ strtolower($lang) }}">{{ ucfirst($lang) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Select All -->
                                <div class="form-check ms-2">
                                    <input class="form-check-input" type="checkbox" id="select_all_channels" disabled>
                                    <label class="form-check-label" for="select_all_channels">Select all (visible)</label>
                                </div>
                            </div>
                        </div>

                        <div id="channelsWrapper" class="border rounded p-2 mt-2" style="max-height:240px; overflow-y:auto;">
                            @foreach($channels as $ch)
                                @php
                                    $type = strtolower(trim($ch->channel_type));
                                    $genre = strtolower(trim($ch->channel_genre ?? ''));
                                    $language = strtolower(trim($ch->language ?? ''));
                                @endphp
                                <div class="form-check channel-row" data-type="{{ $type }}" data-genre="{{ $genre }}" data-language="{{ $language }}">
                                    <input
                                        class="form-check-input channel-checkbox"
                                        type="checkbox"
                                        name="channel_id[]"
                                        value="{{ $ch->id }}"
                                        id="channel_{{ $ch->id }}"
                                        data-name="{{ $ch->channel_name }}"  {{-- used for selected list --}}
                                        disabled>
                                    <label class="form-check-label" for="channel_{{ $ch->id }}">
                                        {{ $ch->channel_name }}
                                        <span class="badge bg-light text-dark border ms-2">
                                            {{ ucfirst($type) }}
                                        </span>
                                        @if($ch->channel_genre)
                                            <span class="badge bg-light text-dark border ms-1">{{ $ch->channel_genre }}</span>
                                        @endif
                                        @if($ch->language)
                                            <span class="badge bg-light text-dark border ms-1">{{ $ch->language }}</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="small text-muted mt-1 d-flex flex-wrap align-items-center gap-2">
                            <span><span id="visibleCount">0</span> visible • <span id="selectedCount">0</span> selected</span>
                        </div>

                        {{-- NEW: read-only selected channel names (badges) --}}
                        <div id="selectedNamesWrap" class="mt-2" style="min-height: 1rem;">
                            <div id="selectedNames" class="d-flex flex-wrap gap-1"></div>
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
    const form = document.getElementById('packageForm');
    const saveBtn = document.getElementById('saveBtn');
    const modalTitle = document.getElementById('packageModalLabel');
    const methodDiv = document.getElementById('formMethod');
    const pkgIdInput = document.getElementById('package_id');
    const nameInput = document.getElementById('name');
    const activeSelect = document.getElementById('active');
    const channelsWrapper = document.getElementById('channelsWrapper');
    const allCb = document.getElementById('select_all_channels');
    const selectedCountEl = document.getElementById('selectedCount');
    const visibleCountEl = document.getElementById('visibleCount');
    const selectedNames = document.getElementById('selectedNames');

    // filters
    const filterType = document.getElementById('channel_filter_type');
    const filterGenre = document.getElementById('channel_filter_genre');
    const filterLanguage = document.getElementById('channel_filter_language');

    function allChannelRows() {
        return Array.from(channelsWrapper.querySelectorAll('.channel-row'));
    }

    function visibleChannelBoxes() {
        return allChannelRows().filter(r => !r.classList.contains('d-none'))
            .map(r => r.querySelector('.channel-checkbox'));
    }

    function renderSelectedNames() {
        // Clear
        selectedNames.innerHTML = '';
        // Collect names of ALL checked (not just visible) so user sees full selection
        const checked = channelsWrapper.querySelectorAll('.channel-checkbox:checked');
        if (!checked.length) return;

        checked.forEach(cb => {
            const name = cb.dataset.name || '';
            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary';
            badge.textContent = name;
            selectedNames.appendChild(badge);
        });
    }

    function updateCountsAndMaster() {
        const boxes = visibleChannelBoxes();
        const total = boxes.length;
        const checked = boxes.filter(cb => cb.checked).length;
        visibleCountEl.textContent = total;
        selectedCountEl.textContent = channelsWrapper.querySelectorAll('.channel-checkbox:checked').length;

        allCb.indeterminate = (checked > 0 && checked < total);
        allCb.checked = (total > 0 && checked === total);
        if (total === 0) { allCb.indeterminate = false; allCb.checked = false; }

        renderSelectedNames();
    }

    function applyFilter() {
        const typeVal = filterType.value;
        const genreVal = filterGenre.value;
        const langVal = filterLanguage.value;

        allChannelRows().forEach(row => {
            const type = (row.dataset.type || '').toLowerCase();
            const genre = (row.dataset.genre || '').toLowerCase();
            const language = (row.dataset.language || '').toLowerCase();

            const matchesType = (typeVal === 'all' || type === typeVal);
            const matchesGenre = (genreVal === 'all' || genre === genreVal);
            const matchesLang = (langVal === 'all' || language === langVal);

            row.classList.toggle('d-none', !(matchesType && matchesGenre && matchesLang));
        });
        updateCountsAndMaster();
    }

    function setDisabled(disabled) {
        nameInput.readOnly = disabled;
        activeSelect.disabled = disabled;
        filterType.disabled = disabled;
        filterGenre.disabled = disabled;
        filterLanguage.disabled = disabled;
        allCb.disabled = disabled;
        allChannelRows().forEach(r => r.querySelector('.channel-checkbox').disabled = disabled);
    }

    // main form logic (add/edit/view)
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
        filterType.value = 'all'; filterGenre.value = 'all'; filterLanguage.value = 'all';
        allChannelRows().forEach(r => r.classList.remove('d-none'));
        selectedNames.innerHTML = '';
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

    // filter change events
    filterType.addEventListener('change', applyFilter);
    filterGenre.addEventListener('change', applyFilter);
    filterLanguage.addEventListener('change', applyFilter);

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
