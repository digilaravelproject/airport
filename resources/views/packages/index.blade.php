@extends('layouts.app')

@section('content')
<style>
    /* OR — explicitly target the last TD of each TR (more explicit) */
    .table tbody tr td:last-child {
        width: 20% !important;
    }

    /* ensure selected and channel list columns share similar height behavior */
    #selectedNamesWrap { max-height: 240px; overflow-y: auto; }
    #channelsWrapper { max-height: 240px; overflow-y: auto; }
    
</style>
<style>
    /* Fix table row height & alignment */
    #channelsListTable td,
    #channelsListTable th {
        white-space: nowrap;            /* Single line only */
        padding: 10px 12px !important;  /* Equal padding */
        vertical-align: middle !important;
    }

    /* Ensure number column stays aligned and centered */
    #channelsListTable td:first-child,
    #channelsListTable th:first-child {
        text-align: center;
        width: 80px !important;
    }

    /* Prevent table overflow issues */
    #channelsListTable {
        table-layout: auto;
        width: 100%;
    }
</style>

<div class="container-fluid">
    <?php
        $page_title = "Packages";
        $sub_title  = "Channel Management";
    ?>

    @php
        // Helpers for sortable headers (same pattern you used on other pages)
        function nextDirPkg($col) {
            $currentSort = request('sort', 'id');
            $currentDir  = request('direction', 'desc');
            if ($currentSort === $col) return $currentDir === 'asc' ? 'desc' : 'asc';
            return 'asc';
        }
        function sortIconPkg($col) {
            $currentSort = request('sort', 'id');
            $currentDir  = request('direction', 'desc');
            if ($currentSort !== $col) return 'fas fa-sort text-muted';
            return $currentDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
        function sortUrlPkg($col) {
            $params = request()->all();
            $params['sort'] = $col;
            $params['direction'] = nextDirPkg($col);
            return request()->fullUrlWithQuery($params);
        }
    @endphp

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
                        <style>
                            /* existing description styles kept */
                            .desc-wrap { display:flex; align-items:center; gap:.5rem; max-width:420px; flex-wrap:nowrap;}
                            .desc-text { display:inline-block; min-width:0; flex:1 1 auto; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.2; max-width:100%;}
                            @media (min-width:576px){ .desc-text { max-width: calc(100% - 90px); } }
                            .desc-text.expanded { white-space:normal; overflow:visible; max-width:100%;}
                            .read-more { cursor:pointer; color:#0d6efd; background:none; border:none; padding:0; margin:0; font-size:.9rem; text-decoration:underline; white-space:nowrap; flex:0 0 auto;}
                            .read-more:focus { outline:none; box-shadow:none; }
                            @media (max-width:768px){ .desc-wrap { max-width:220px; } }

                            /* Selected names / DnD styling */
                            #selectedNames { min-height: 2rem; }
                            .selected-item {
                                display: inline-flex;
                                align-items: center;
                                gap: .4rem;
                                padding: .28rem .5rem;
                                border-radius: .375rem;
                                background: #6c757d;
                                color: white;
                                cursor: grab;
                                user-select: none;
                                margin: 0 .25rem .25rem 0;
                            }
                            .selected-item .handle {
                                display:inline-flex;
                                align-items:center;
                                justify-content:center;
                                width: 18px;
                                height: 18px;
                                margin-right:6px;
                                background: rgba(255,255,255,0.15);
                                border-radius: 3px;
                                font-size: 12px;
                            }
                            .selected-item.dragging {
                                opacity: .45;
                                transform: scale(.98);
                            }
                            .selected-item-number { font-weight:700; margin-right:6px; }
                            .selected-item button.remove-item {
                                background: none;
                                border: none;
                                color: white;
                                padding: 0 .25rem;
                                font-size: 14px;
                                line-height: 1;
                            }

                            /* placeholder while dragging */
                            .selected-placeholder {
                                display: inline-flex;
                                align-items: center;
                                gap: .4rem;
                                padding: .28rem .5rem;
                                border-radius: .375rem;
                                background: rgba(0,0,0,0.05);
                                color: #333;
                                border: 1px dashed rgba(0,0,0,0.2);
                                margin: 0 .25rem .25rem 0;
                                min-width: 80px;
                                min-height: 24px;
                            }

                            /* drag mirror (created in JS) styling */
                            .drag-mirror {
                                position: fixed;
                                pointer-events: none;
                                z-index: 99999;
                                transform: translate(-50%,-50%) scale(1.01);
                                opacity: 0.95;
                            }

                            /* show-all link styling in table cell */
                            .show-all-link { margin-left:6px; font-weight:600; cursor: pointer; color: #0d6efd; text-decoration: underline; background: none; border: none; padding: 0;}
                        </style>

                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <a href="{{ sortUrlPkg('id') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            No <i class="{{ sortIconPkg('id') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlPkg('name') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Name <i class="{{ sortIconPkg('name') }}"></i>
                                        </a>
                                    </th>

                                    <!-- Description header -->
                                    <th>
                                        <span class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Description
                                        </span>
                                    </th>

                                    <th>
                                        <a href="{{ sortUrlPkg('channels') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Channels <i class="{{ sortIconPkg('channels') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlPkg('active') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Status <i class="{{ sortIconPkg('active') }}"></i>
                                        </a>
                                    </th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($packages as $key => $package)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $package->id }}</span></td>
                                        <td>{{ $package->name }}</td>

                                        <!-- Description column -->
                                        <td>
                                            <div class="desc-wrap">
                                                @php
                                                    $raw = $package->description ?? '';
                                                    $preview = Str::limit($raw, 30, '');
                                                    $preview = rtrim($preview, ". \t\n\r\0\x0B");
                                                @endphp

                                                <span class="desc-text" data-full="{{ e($raw) }}">{{ e($preview) }}</span>
                                            </div>
                                        </td>

                                        <td>
                                            @if($package->channels->count())
                                                {{-- show first 4 channels joined --}}
                                                @php
                                                    $first = $package->channels->slice(0,4)->pluck('channel_name')->join(', ');
                                                    $totalChannels = $package->channels->count();
                                                    // prepare JSON data for the modal: include id and name (and preserve current order)
                                                    $channelsJson = $package->channels->map(function($c,$i){
                                                        return ['id' => $c->id, 'name' => $c->channel_name];
                                                    });
                                                @endphp

                                                <span class="text-muted-small">{{ $first }}</span>

                                                @if($totalChannels > 4)
                                                    <button
                                                        class="show-all-link btn btn-link p-0"
                                                        type="button"
                                                        data-channels='@json($channelsJson)'>
                                                        Show all ({{ $totalChannels }})
                                                    </button>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $package->active == 'Yes' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $package->active == 'Yes' ? 'Active' : 'Inactive' }}
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

                        @if($packages->isEmpty())
                            <div class="text-center text-muted py-3">No records found.</div>
                        @endif
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            {{ $packages->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Modal (layout updated: selected channels left, filters + list right) -->
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

                    <!-- Description in modal -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4" readonly></textarea>
                        @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Channels & filters (UPDATED layout: selected on left, list + filters on right) -->
                    <div class="mb-3">
                        <div class="row g-3">
                          <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                              <label class="form-label mb-0">Channels</label>

                              <div class="d-flex gap-3 align-items-center flex-wrap">
                                  <div class="d-flex align-items-center gap-2">
                                      <label class="mb-0 small text-muted">Type:</label>
                                      <select id="channel_filter_type" class="form-select form-select-sm" style="min-width: 120px;" disabled>
                                          <option value="all" selected>All</option>
                                          <option value="paid">Paid</option>
                                          <option value="free">Free</option>
                                      </select>
                                  </div>

                                  <div class="d-flex align-items-center gap-2">
                                      <label class="mb-0 small text-muted">Genre:</label>
                                      <select id="channel_filter_genre" class="form-select form-select-sm" style="min-width: 140px;" disabled>
                                          <option value="all" selected>All</option>
                                          @foreach($genres as $genre)
                                              <option value="{{ strtolower($genre) }}">{{ ucfirst($genre) }}</option>
                                          @endforeach
                                      </select>
                                  </div>

                                  <div class="d-flex align-items-center gap-2">
                                      <label class="mb-0 small text-muted">Language:</label>
                                      <select id="channel_filter_language" class="form-select form-select-sm" style="min-width: 140px;" disabled>
                                          <option value="all" selected>All</option>
                                          @foreach($languages as $lang)
                                              <option value="{{ strtolower($lang) }}">{{ ucfirst($lang) }}</option>
                                          @endforeach
                                      </select>
                                  </div>

                                  <div class="form-check ms-2">
                                      <input class="form-check-input" type="checkbox" id="select_all_channels" disabled>
                                      <label class="form-check-label" for="select_all_channels">Select all (visible)</label>
                                  </div>
                              </div>
                          </div>
                          <!-- LEFT: Filters + channels list -->
                            <div class="col-md-8">
                                <div id="channelsWrapper" class="border rounded p-2" style="max-height:240px; overflow-y:auto;">
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
                                                name="channel_checkbox_dummy[]"
                                                value="{{ $ch->id }}"
                                                id="channel_{{ $ch->id }}"
                                                data-name="{{ $ch->channel_name }}">
                                            <label class="form-check-label" for="channel_{{ $ch->id }}">
                                                {{ $ch->channel_name }}
                                                <span class="badge bg-light text-dark border ms-2">{{ ucfirst($type) }}</span>
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

                                <!-- ordered inputs will be injected here at submit -->
                                <div id="ordered_channel_inputs"></div>
                            </div>
                            <!-- RIGHT: Selected channels (drag/drop area) -->
                            <div class="col-md-4">
                                <label class="form-label mb-1">Selected Channels (drag to reorder)</label>
                                <div id="selectedNamesWrap" class="border rounded p-2" style="min-height:180px; max-height:240px; overflow:auto;">
                                    <div id="selectedNames" class="d-flex flex-wrap gap-1" aria-live="polite"></div>
                                </div>
                                <div class="small text-muted mt-1">Ordered selection will be sent to the server on Save.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Active</label>
                        <select name="active" id="active" class="form-select" disabled>
                            <option value="Yes" {{ old('active') == 'Yes' ? 'selected' : '' }}>Active</option>
                            <option value="No"  {{ old('active') == 'No'  ? 'selected' : '' }}>Inactive</option>
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

    <!-- Channels "Show all" Modal (new) -->
    <!-- Channels "Show all" Modal (new) -->
    <div class="modal fade" id="channelsModal" tabindex="-1" aria-labelledby="channelsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="channelsModalLabel">Channels (All)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0 channel-table-compact" id="channelsListTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px">#</th>
                            <th>Channel Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- rows injected by JS -->
                    </tbody>
                </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
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
    const descriptionInput = document.getElementById('description');
    const activeSelect = document.getElementById('active');
    const channelsWrapper = document.getElementById('channelsWrapper');
    const allCb = document.getElementById('select_all_channels');
    const selectedCountEl = document.getElementById('selectedCount');
    const visibleCountEl = document.getElementById('visibleCount');
    const selectedNames = document.getElementById('selectedNames');
    const filterType = document.getElementById('channel_filter_type');
    const filterGenre = document.getElementById('channel_filter_genre');
    const filterLanguage = document.getElementById('channel_filter_language');
    const orderedInputsContainer = document.getElementById('ordered_channel_inputs');

    function allChannelRows() { return Array.from(channelsWrapper.querySelectorAll('.channel-row')); }
    function visibleChannelBoxes() { return allChannelRows().filter(r => !r.classList.contains('d-none')).map(r => r.querySelector('.channel-checkbox')); }

    // create a selected-item DOM for a checkbox (does not append)
    function buildSelectedItem(cb) {
        const id = cb.value;
        const name = cb.dataset.name || cb.getAttribute('data-name') || cb.nextElementSibling?.innerText || 'Unknown';
        const wrapper = document.createElement('div');
        wrapper.className = 'selected-item';
        wrapper.dataset.id = id;

        const handle = document.createElement('span');
        handle.className = 'handle';
        handle.innerHTML = '&#x2630;'; // hamburger
        wrapper.appendChild(handle);

        const number = document.createElement('span');
        number.className = 'selected-item-number';
        number.textContent = '1.';
        wrapper.appendChild(number);

        const label = document.createElement('span');
        label.className = 'selected-item-label';
        label.textContent = name.trim();
        wrapper.appendChild(label);

        const rem = document.createElement('button');
        rem.type = 'button';
        rem.className = 'remove-item';
        rem.innerHTML = '&times;';
        rem.addEventListener('click', (ev) => {
            ev.stopPropagation();
            // uncheck corresponding checkbox
            const linkedCb = channelsWrapper.querySelector('.channel-checkbox[value="'+id+'"]');
            if (linkedCb) {
                linkedCb.checked = false;
                // trigger change handler
                linkedCb.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                // remove element directly
                wrapper.remove();
                updateNumbers();
            }
        });
        wrapper.appendChild(rem);

        return wrapper;
    }

    // update numbers shown on selected-items
    function updateNumbers() {
        Array.from(selectedNames.children).forEach((c, i) => {
            const num = c.querySelector('.selected-item-number');
            if (num) num.textContent = (i + 1) + '.';
        });
        selectedCountEl.textContent = channelsWrapper.querySelectorAll('.channel-checkbox:checked').length;
    }

    // build selectedNames DOM based on the order array (ids)
    function populateSelectedNamesFromOrder(orderIds) {
        selectedNames.innerHTML = '';
        orderIds.forEach(id => {
            const cb = channelsWrapper.querySelector('.channel-checkbox[value="'+id+'"]');
            if (!cb) return;
            // ensure checkbox is checked
            if (!cb.checked) cb.checked = true;
            const item = buildSelectedItem(cb);
            selectedNames.appendChild(item);
        });
        updateNumbers();
    }

    // ensure selectedNames matches current checked boxes.
    function syncSelectedNamesWithCheckboxes() {
        const checkedCbs = Array.from(channelsWrapper.querySelectorAll('.channel-checkbox:checked'));
        const currentOrder = Array.from(selectedNames.children).map(c => c.dataset.id);

        // map existing nodes
        const idToNode = {};
        currentOrder.forEach(id => {
            const node = selectedNames.querySelector('[data-id="'+id+'"]');
            if (node) idToNode[id] = node;
        });

        // include newly checked items at end (in the order found)
        checkedCbs.forEach(cb => {
            const id = cb.value;
            if (!idToNode[id]) {
                idToNode[id] = buildSelectedItem(cb);
                currentOrder.push(id);
            }
        });

        // filter out any ids that are no longer checked
        const finalOrder = currentOrder.filter(id => checkedCbs.some(cb => cb.value === id));

        // rebuild
        selectedNames.innerHTML = '';
        finalOrder.forEach((id) => {
            const node = idToNode[id];
            if (node) selectedNames.appendChild(node);
        });

        updateNumbers();
    }

    // Update counts/master checkbox
    function updateCountsAndMaster() {
        const boxes = visibleChannelBoxes();
        const total = boxes.length;
        const checkedVisible = boxes.filter(cb => cb.checked).length;
        visibleCountEl.textContent = total;
        selectedCountEl.textContent = channelsWrapper.querySelectorAll('.channel-checkbox:checked').length;

        allCb.indeterminate = (checkedVisible > 0 && checkedVisible < total);
        allCb.checked = (total > 0 && checkedVisible === total);
        if (total === 0) { allCb.indeterminate = false; allCb.checked = false; }
    }

    // filters
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
        descriptionInput.readOnly = disabled;
        activeSelect.disabled = disabled;
        filterType.disabled = disabled;
        filterGenre.disabled = disabled;
        filterLanguage.disabled = disabled;
        allCb.disabled = disabled;
        allChannelRows().forEach(r => r.querySelector('.channel-checkbox').disabled = disabled);
    }

    // openForm: supports add, edit, view
    window.openForm = function(mode, data = null) {
        form.reset();
        methodDiv.innerHTML = '';
        form.action = "{{ route('packages.store') }}";
        saveBtn.style.display = 'none';
        pkgIdInput.value = '';
        nameInput.value = '';
        descriptionInput.value = '';
        activeSelect.value = 'Yes';
        allChannelRows().forEach(r => r.querySelector('.channel-checkbox').checked = false);
        allCb.checked = false; allCb.indeterminate = false;
        filterType.value = 'all'; filterGenre.value = 'all'; filterLanguage.value = 'all';
        allChannelRows().forEach(r => r.classList.remove('d-none'));
        selectedNames.innerHTML = '';
        orderedInputsContainer.innerHTML = '';
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
            descriptionInput.value = data.description ?? '';
            activeSelect.value = data.active ?? 'Yes';
            setDisabled(false);

            // If server provides channels in order, use that order to populate selected list.
            const selectedIds = (data.channels || []).map(ch => String(ch.id));
            // mark checkboxes
            allChannelRows().forEach(r => {
                const cb = r.querySelector('.channel-checkbox');
                cb.checked = selectedIds.includes(cb.value);
            });
            // populate selectedNames in same order as selectedIds (server order)
            populateSelectedNamesFromOrder(selectedIds);
            updateCountsAndMaster();
        }

        if (mode === 'view' && data) {
            modalTitle.innerText = "View Package";
            pkgIdInput.value = data.id ?? '';
            nameInput.value  = data.name ?? '';
            descriptionInput.value = data.description ?? '';
            activeSelect.value = data.active ?? 'Yes';
            setDisabled(true);

            const selectedIds = (data.channels || []).map(ch => String(ch.id));
            allChannelRows().forEach(r => {
                const cb = r.querySelector('.channel-checkbox');
                cb.checked = selectedIds.includes(cb.value);
            });
            populateSelectedNamesFromOrder(selectedIds);
            updateCountsAndMaster();
        }
    };

    // events
    filterType.addEventListener('change', applyFilter);
    filterGenre.addEventListener('change', applyFilter);
    filterLanguage.addEventListener('change', applyFilter);

    allCb.addEventListener('change', () => {
        const checked = allCb.checked;
        visibleChannelBoxes().forEach(cb => {
            cb.checked = checked;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        });
        updateCountsAndMaster();
    });

    // when any checkbox toggles, sync selected list accordingly
    channelsWrapper.addEventListener('change', (e) => {
        if (!e.target.classList.contains('channel-checkbox')) return;
        // if checked => append to end if not present
        const cb = e.target;
        if (cb.checked) {
            // add item at end if not already present
            if (!selectedNames.querySelector('[data-id="'+cb.value+'"]')) {
                const item = buildSelectedItem(cb);
                selectedNames.appendChild(item);
            }
        } else {
            // remove item from selectedNames
            const node = selectedNames.querySelector('[data-id="'+cb.value+'"]');
            if (node) node.remove();
        }
        updateNumbers();
        updateCountsAndMaster();
    });

    /* -------------- Pointer-based DnD (mouse + touch) -------------- */

    let draggingNode = null;
    let dragMirror = null;
    let placeholder = null;
    let startPointerOffset = { x: 0, y: 0 };

    // create placeholder node (reused)
    function createPlaceholder(width, height) {
        const ph = document.createElement('div');
        ph.className = 'selected-placeholder';
        ph.style.width = width ? width + 'px' : '';
        ph.style.height = height ? height + 'px' : '';
        return ph;
    }

    // pointerdown on handle or item -> start drag
    selectedNames.addEventListener('pointerdown', (e) => {
        const handle = e.target.closest('.handle');
        const item = e.target.closest('.selected-item');
        if (!item) return;

        // only start drag if pointer is on handle OR target is the item itself but not on the remove button
        const removeBtn = e.target.closest('.remove-item');
        if (!handle && removeBtn) return; // don't start drag when clicking remove

        e.preventDefault();
        // capture pointer
        item.setPointerCapture(e.pointerId);

        startDrag(item, e);
    });

    function startDrag(node, pointerEvent) {
        // set state
        draggingNode = node;
        draggingNode.classList.add('dragging');

        // create drag mirror (clone) and style it
        dragMirror = node.cloneNode(true);
        dragMirror.classList.add('drag-mirror');
        dragMirror.style.width = node.offsetWidth + 'px';
        dragMirror.style.height = node.offsetHeight + 'px';
        dragMirror.style.boxSizing = 'border-box';
        dragMirror.style.margin = '0';
        dragMirror.style.opacity = '0.95';
        dragMirror.style.transform = 'translate(-50%,-50%)';
        document.body.appendChild(dragMirror);

        // compute pointer offset inside node so mirror stays aligned
        const rect = node.getBoundingClientRect();
        startPointerOffset.x = pointerEvent.clientX - (rect.left + rect.width / 2);
        startPointerOffset.y = pointerEvent.clientY - (rect.top + rect.height / 2);

        // create and insert placeholder
        placeholder = createPlaceholder(rect.width, rect.height);
        node.parentNode.insertBefore(placeholder, node.nextSibling);

        // hide original node visually but keep in DOM (so keyboard/readers unaffected)
        node.style.visibility = 'hidden';

        // attach move/end listeners to document to track pointer outside container
        document.addEventListener('pointermove', onPointerMove);
        document.addEventListener('pointerup', onPointerUp);
        document.addEventListener('pointercancel', onPointerUp);
    }

    function onPointerMove(e) {
        if (!draggingNode || !dragMirror) return;

        // move mirror
        dragMirror.style.left = (e.clientX - startPointerOffset.x) + 'px';
        dragMirror.style.top = (e.clientY - startPointerOffset.y) + 'px';

        // Find best insertion position
        const items = Array.from(selectedNames.querySelectorAll('.selected-item:not(.dragging)'));
        if (items.length === 0) {
            // only dragging item present
            if (placeholder.parentNode !== selectedNames) selectedNames.appendChild(placeholder);
            return;
        }

        // Determine pointer row top (approx) using items bounding boxes
        let pointerRowTop = null;
        for (const it of items) {
            const r = it.getBoundingClientRect();
            if (e.clientY >= r.top - r.height/2 && e.clientY <= r.bottom + r.height/2) {
                pointerRowTop = r.top;
                break;
            }
        }
        if (pointerRowTop === null) pointerRowTop = items[0].getBoundingClientRect().top;

        // find the first child in same row whose centerX is > pointerX
        let inserted = false;
        for (const it of items) {
            const r = it.getBoundingClientRect();
            // ensure same row (within half-height)
            if (Math.abs(r.top - pointerRowTop) > r.height / 2) continue;
            const centerX = r.left + r.width / 2;
            if (centerX > e.clientX) {
                if (placeholder.parentNode !== selectedNames || selectedNames.children[Array.prototype.indexOf.call(selectedNames.children, it)] !== placeholder) {
                    selectedNames.insertBefore(placeholder, it);
                }
                inserted = true;
                break;
            }
        }

        if (!inserted) {
            // append to end of row
            if (placeholder.parentNode !== selectedNames) selectedNames.appendChild(placeholder);
            else {
                // ensure placeholder is after last item in that row
                // nothing special to do
            }
        }
    }

    function onPointerUp(e) {
        if (!draggingNode) return;

        // remove listeners
        document.removeEventListener('pointermove', onPointerMove);
        document.removeEventListener('pointerup', onPointerUp);
        document.removeEventListener('pointercancel', onPointerUp);

        // replace placeholder with node
        if (placeholder && placeholder.parentNode) {
            placeholder.parentNode.insertBefore(draggingNode, placeholder);
            placeholder.parentNode.removeChild(placeholder);
        }

        // cleanup mirror and restore node styles
        if (dragMirror && dragMirror.parentNode) dragMirror.parentNode.removeChild(dragMirror);
        draggingNode.style.visibility = '';
        draggingNode.classList.remove('dragging');

        // clear state
        draggingNode = null;
        dragMirror = null;
        placeholder = null;

        // finally update numbers (checkboxes unchanged)
        updateNumbers();
    }

    // When an item is removed via its remove button or programmatic change, ensure no stale mirror/placeholder.
    function cancelActiveDrag() {
        if (dragMirror && dragMirror.parentNode) dragMirror.parentNode.removeChild(dragMirror);
        if (placeholder && placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
        if (draggingNode) { draggingNode.style.visibility = ''; draggingNode.classList.remove('dragging'); }
        draggingNode = dragMirror = placeholder = null;
        document.removeEventListener('pointermove', onPointerMove);
        document.removeEventListener('pointerup', onPointerUp);
        document.removeEventListener('pointercancel', onPointerUp);
    }

    // If user navigates away or modal closed while dragging, cancel
    document.addEventListener('visibilitychange', cancelActiveDrag);
    window.addEventListener('blur', cancelActiveDrag);

    /* -------------- end pointer DnD -------------- */

    // Before submit, create ordered hidden inputs channel_id[] according to current selectedNames order
    form.addEventListener('submit', (e) => {
        orderedInputsContainer.innerHTML = '';
        const orderIds = Array.from(selectedNames.children).map(c => c.dataset.id);

        // fallback: if empty, collect checked checkboxes (no order available)
        if (orderIds.length === 0) {
            const checked = Array.from(channelsWrapper.querySelectorAll('.channel-checkbox:checked')).map(cb => cb.value);
            checked.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'channel_id[]';
                inp.value = id;
                orderedInputsContainer.appendChild(inp);
            });
            return;
        }

        orderIds.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'channel_id[]';
            inp.value = id;
            orderedInputsContainer.appendChild(inp);
        });
    });

    /* description preview code (kept same) */
    function initDescriptionPreviews() {
        const rows = document.querySelectorAll('.desc-wrap');
        rows.forEach(wrap => {
            const textEl = wrap.querySelector('.desc-text');
            const btn = wrap.querySelector('.read-more');

            if (!textEl || !btn) return;

            textEl.classList.remove('expanded');
            btn.style.display = 'none';
            btn.removeAttribute('data-listener');

            setTimeout(() => {
                const isOverflowing = textEl.scrollWidth > textEl.clientWidth + 1;
                if (isOverflowing) {
                    btn.style.display = 'inline-block';
                    btn.textContent = 'Read more';
                    btn.setAttribute('aria-expanded', 'false');

                    if (!btn.getAttribute('data-listener')) {
                        btn.addEventListener('click', () => {
                            const expanded = btn.getAttribute('aria-expanded') === 'true';
                            if (!expanded) {
                                textEl.classList.add('expanded');
                                btn.textContent = 'Read less';
                                btn.setAttribute('aria-expanded', 'true');
                            } else {
                                textEl.classList.remove('expanded');
                                btn.textContent = 'Read more';
                                btn.setAttribute('aria-expanded', 'false');
                            }
                        });
                        btn.setAttribute('data-listener', '1');
                    }
                } else {
                    btn.style.display = 'none';
                }
            }, 10);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        applyFilter();
        initDescriptionPreviews();
        // ensure numbers initialised
        updateNumbers();
        window.addEventListener('resize', () => {
            setTimeout(initDescriptionPreviews, 150);
            // cancel any active drag if resizing (avoid odd states)
            cancelActiveDrag();
        });
    });

    /* ---------- New: handle "Show all (N)" clicks and populate channelsModal ---------- */
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.show-all-link');
        if (!btn) return;
        e.preventDefault();

        try {
            // data-channels contains JSON array [{id, name}, ...] in package order
            const raw = btn.getAttribute('data-channels') || '[]';
            // parse safely
            const channels = JSON.parse(raw);

            const tbody = document.querySelector('#channelsListTable tbody');
            tbody.innerHTML = '';

            channels.forEach(function(ch, index) {
                const tr = document.createElement('tr');

                // sort order number (1-based)
                const tdNum = document.createElement('td');
                tdNum.textContent = (index + 1);
                tdNum.style.verticalAlign = 'middle';
                tr.appendChild(tdNum);

                const tdName = document.createElement('td');
                tdName.textContent = ch.name ?? '';
                tdName.style.verticalAlign = 'middle';
                tr.appendChild(tdName);

                tbody.appendChild(tr);
            });

            // show bootstrap modal
            const modalEl = document.getElementById('channelsModal');
            const bootstrapModal = new bootstrap.Modal(modalEl);
            bootstrapModal.show();
        } catch (err) {
            console.error('Failed to open channels modal:', err);
            alert('Unable to open channel list.');
        }
    });
    /* ----------------------------------------------------------------------------- */

})();
</script>
@endsection
