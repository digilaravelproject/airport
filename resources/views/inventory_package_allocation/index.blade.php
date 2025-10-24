@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <?php
        $page_title = "Inventory Packages";
        $sub_title = "Allocations";
    ?>
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

    <!-- Inventory Packages List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inventory Packages List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        @php
                            use Illuminate\Support\Facades\Storage;
                        @endphp
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Box ID</th>
                                    <th>Box Model</th>
                                    <th>Serial No</th>
                                    <th>Mac ID</th>
                                    <th>Client ID</th>
                                    <th>Client Name</th>
                                    <th>Allocated Packages</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventories as $key => $inventory)
                                    @php
                                        // Build JSON file URL if it exists
                                        $jsonRelPath = 'json/'.$inventory->box_id.'.json';
                                        $jsonUrl = Storage::disk('public')->exists($jsonRelPath)
                                            ? asset('storage/'.$jsonRelPath)
                                            : null;

                                        // Enrich inventory payload for JS (so we don't change controller)
                                        $invPayload = $inventory->toArray();
                                        $invPayload['json_url'] = $jsonUrl;
                                    @endphp
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td><span class="badge bg-secondary">{{ $inventory->box_id }}</span></td>
                                        <td>{{ $inventory->box_model }}</td>
                                        <td>{{ $inventory->box_serial_no }}</td>
                                        <td>{{ $inventory->box_mac }}</td>
                                        <td>
                                            @if($inventory->client)
                                                <span class="badge bg-info">{{ $inventory->client->id }}</span>
                                            @else
                                                <span class="text-muted">No client</span>
                                            @endif
                                        </td>
                                        <td>{{ $inventory->client->name ?? '-' }}</td>
                                        <td>
                                            @if($inventory->packages->count())
                                                {{ $inventory->packages->pluck('name')->join(', ') }}
                                            @else
                                                <span class="text-muted">No packages</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal" data-bs-target="#inventoryPackageModal"
                                                    onclick='openForm("edit", {!! json_encode($invPayload, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) !!})'>
                                                <i class="las la-pen"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-info"
                                                    data-bs-toggle="modal" data-bs-target="#inventoryPackageModal"
                                                    onclick='openForm("view", {!! json_encode($invPayload, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) !!})'>
                                                <i class="las la-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $inventories->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Package Modal -->
<div class="modal fade" id="inventoryPackageModal" tabindex="-1" aria-labelledby="inventoryPackageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="inventoryPackageForm">
        @csrf
        <div id="formMethod"></div>

        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="inventoryPackageModalLabel">Assign Packages</h5>

          <!-- NEW: View JSON link (hidden by default, shown in View mode when file exists) -->
          <a id="viewJsonLink"
             href="#"
             target="_blank"
             class="btn btn-outline-light btn-sm ms-2"
             style="display:none;">
            <i class="las la-file-code me-1"></i> View JSON
          </a>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <!-- Inventory Info -->
            <input type="hidden" id="inventory_id" class="form-control">
            <div class="mb-3">
                <label class="form-label">Box ID</label>
                <input type="text" id="box_id" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Box Model</label>
                <input type="text" id="inventory_box_model" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Serial No</label>
                <input type="text" id="inventory_serial" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Client</label>
                <input type="text" id="inventory_client" class="form-control" readonly>
            </div>

            <!-- Packages -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label mb-0">Packages</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="select_all_packages" disabled>
                        <label class="form-check-label" for="select_all_packages">Select All</label>
                    </div>
                </div>

                <div id="packagesWrapper" class="border rounded p-2 mt-2" style="max-height:200px; overflow-y:auto;">
                    @foreach($packages as $pkg)
                        <div class="form-check">
                            <input
                                class="form-check-input pkg-checkbox"
                                type="checkbox"
                                name="package_ids[]"
                                value="{{ $pkg->id }}"
                                id="pkg_{{ $pkg->id }}"
                                data-name="{{ $pkg->name }}"
                                disabled
                            >
                            <label class="form-check-label" for="pkg_{{ $pkg->id }}">
                                {{ $pkg->name }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <small class="text-muted d-block mt-1">
                    <span id="selectedCount">0</span> / <span id="totalCount">{{ count($packages) }}</span> selected
                </small>

                <div class="mt-2">
                    <span class="small text-muted me-2">Selected package:</span>
                    <span id="selectedPackageName" class="badge bg-secondary">None</span>
                </div>

                @error('package_ids')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="modal-footer">
          <button type="submit" id="saveBtn" class="btn btn-dark px-4" style="display:none;">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openForm(mode, data = null) {
    const modalTitle = document.getElementById('inventoryPackageModalLabel');
    const form       = document.getElementById('inventoryPackageForm');
    const saveBtn    = document.getElementById('saveBtn');
    const methodDiv  = document.getElementById('formMethod');

    const viewJsonLink = document.getElementById('viewJsonLink');

    const selectAll        = document.getElementById('select_all_packages'); // stays disabled
    const selectedCountEl  = document.getElementById('selectedCount');
    const totalCountEl     = document.getElementById('totalCount');
    const selectedNameEl   = document.getElementById('selectedPackageName');

    const pkgCheckboxes = () => Array.from(document.querySelectorAll('#packagesWrapper .pkg-checkbox'));

    function enforceSingleSelection(changedCb) {
        const boxes = pkgCheckboxes();
        if (changedCb && changedCb.checked) {
            boxes.forEach(cb => { if (cb !== changedCb) cb.disabled = true; });
        } else {
            boxes.forEach(cb => { cb.disabled = false; });
        }
    }

    function currentSelected() {
        return pkgCheckboxes().find(cb => cb.checked) || null;
    }

    function updateSelectedName() {
        const cb = currentSelected();
        selectedNameEl.textContent = cb ? (cb.dataset.name || 'Selected') : 'None';
        selectedNameEl.className = 'badge ' + (cb ? 'bg-primary' : 'bg-secondary');
    }

    function updateCounts() {
        const boxes   = pkgCheckboxes();
        const total   = boxes.length;
        const checked = boxes.filter(cb => cb.checked).length;

        selectedCountEl.textContent = checked;
        totalCountEl.textContent    = total;

        selectAll.indeterminate = false;
        selectAll.checked = false;

        updateSelectedName();
    }

    // Reset form
    form.reset();
    document.getElementById('inventory_id').value = '';
    document.getElementById('box_id').value = '';
    document.getElementById('inventory_box_model').value = '';
    document.getElementById('inventory_serial').value = '';
    document.getElementById('inventory_client').value = '';
    pkgCheckboxes().forEach(cb => { cb.checked = false; cb.disabled = true; });
    methodDiv.innerHTML = '';
    saveBtn.style.display = 'none';
    selectAll.disabled = true;
    updateCounts();

    // Hide JSON link by default
    viewJsonLink.style.display = 'none';
    viewJsonLink.href = '#';

    // Wire checkbox change events (single-select behavior)
    pkgCheckboxes().forEach(cb => {
        cb.onchange = () => {
            if (cb.checked) {
                pkgCheckboxes().forEach(other => { if (other !== cb) other.checked = false; });
            }
            enforceSingleSelection(cb);
            updateCounts();
        };
    });

    if (mode === 'edit' && data) {
        modalTitle.innerText = "Assign Packages";
        form.action = "/inventory-packages/" + data.id + "/assign";
        saveBtn.style.display = 'inline-block';

        document.getElementById('inventory_id').value = data.id;
        document.getElementById('box_id').value = data.box_id;
        document.getElementById('inventory_box_model').value = data.box_model;
        document.getElementById('inventory_serial').value = data.box_serial_no;
        document.getElementById('inventory_client').value = data.client ? (data.client.id + ' - ' + data.client.name) : 'No client';

        pkgCheckboxes().forEach(cb => cb.disabled = false);

        let preselectId = null;
        if (Array.isArray(data.packages) && data.packages.length > 0) {
            preselectId = String(data.packages[0].id);
        }

        pkgCheckboxes().forEach(cb => {
            cb.checked = (preselectId !== null && cb.value === preselectId);
        });

        enforceSingleSelection(currentSelected());
        updateCounts();

        // Keep JSON link hidden in edit mode (as requested)
        viewJsonLink.style.display = 'none';
    }

    if (mode === 'view' && data) {
        modalTitle.innerText = "View Inventory Packages";

        document.getElementById('inventory_id').value = data.id;
        document.getElementById('box_id').value = data.box_id;
        document.getElementById('inventory_box_model').value = data.box_model;
        document.getElementById('inventory_serial').value = data.box_serial_no;
        document.getElementById('inventory_client').value = data.client ? (data.client.id + ' - ' + data.client.name) : 'No client';

        // Preselect first assigned package (read-only)
        let selectedId = null;
        if (Array.isArray(data.packages) && data.packages.length > 0) {
            selectedId = String(data.packages[0].id);
        }

        pkgCheckboxes().forEach(cb => {
            cb.checked = (selectedId !== null && cb.value === selectedId);
            cb.disabled = true;
        });

        enforceSingleSelection(currentSelected());
        updateCounts();

        // Show JSON link if server indicated file exists
        if (data.json_url) {
            viewJsonLink.href = data.json_url;
            viewJsonLink.style.display = 'inline-block';
        } else {
            viewJsonLink.style.display = 'none';
        }

        saveBtn.style.display = 'none';
    }
}
</script>
@endsection
