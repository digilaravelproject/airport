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

    <!-- Inventory Packages List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inventory Packages List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Inventory ID</th>
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
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td><span class="badge bg-secondary">{{ $inventory->id }}</span></td>
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
                                                    onclick="openForm('edit', {{ $inventory->toJson() }})">
                                                <i class="las la-pen"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-info"
                                                    data-bs-toggle="modal" data-bs-target="#inventoryPackageModal"
                                                    onclick="openForm('view', {{ $inventory->toJson() }})">
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
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <!-- Inventory Info -->
            <div class="mb-3">
                <label class="form-label">Inventory ID</label>
                <input type="text" id="inventory_id" class="form-control" readonly>
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
                            <input class="form-check-input pkg-checkbox" type="checkbox" name="package_ids[]" value="{{ $pkg->id }}" id="pkg_{{ $pkg->id }}" disabled>
                            <label class="form-check-label" for="pkg_{{ $pkg->id }}">
                                {{ $pkg->name }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <small class="text-muted">
                    <span id="selectedCount">0</span> / <span id="totalCount">{{ count($packages) }}</span> selected
                </small>

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
    const form = document.getElementById('inventoryPackageForm');
    const saveBtn = document.getElementById('saveBtn');
    const methodDiv = document.getElementById('formMethod');
    const selectAll = document.getElementById('select_all_packages');
    const selectedCountEl = document.getElementById('selectedCount');
    const totalCountEl = document.getElementById('totalCount');

    const pkgCheckboxes = () => Array.from(document.querySelectorAll('#packagesWrapper .pkg-checkbox'));

    // Update counts and master
    function updateCounts() {
        const boxes = pkgCheckboxes();
        const total = boxes.length;
        const checked = boxes.filter(cb => cb.checked).length;

        selectedCountEl.textContent = checked;
        totalCountEl.textContent = total;

        selectAll.indeterminate = (checked > 0 && checked < total);
        selectAll.checked = (checked === total && total > 0);
        if (total === 0) {
            selectAll.indeterminate = false;
            selectAll.checked = false;
        }
    }

    // Reset form
    form.reset();
    document.getElementById('inventory_id').value = '';
    document.getElementById('inventory_box_model').value = '';
    document.getElementById('inventory_serial').value = '';
    document.getElementById('inventory_client').value = '';
    pkgCheckboxes().forEach(cb => { cb.checked = false; cb.disabled = true; });
    methodDiv.innerHTML = '';
    saveBtn.style.display = 'none';
    selectAll.disabled = true;
    selectAll.checked = false;
    selectAll.indeterminate = false;
    updateCounts();

    // Events
    selectAll.onchange = () => {
        pkgCheckboxes().forEach(cb => { if (!cb.disabled) cb.checked = selectAll.checked; });
        updateCounts();
    };
    pkgCheckboxes().forEach(cb => {
        cb.onchange = () => updateCounts();
    });

    if (mode === 'edit' && data) {
        modalTitle.innerText = "Assign Packages";
        form.action = "/inventory-packages/" + data.id + "/assign";
        saveBtn.style.display = 'inline-block';

        document.getElementById('inventory_id').value = data.id;
        document.getElementById('inventory_box_model').value = data.box_model;
        document.getElementById('inventory_serial').value = data.box_serial_no;
        document.getElementById('inventory_client').value = data.client ? data.client.id + ' - ' + data.client.name : 'No client';

        pkgCheckboxes().forEach(cb => {
            cb.checked = data.packages.some(pkg => pkg.id == cb.value);
            cb.disabled = false;
        });

        selectAll.disabled = false;
        updateCounts();
    }

    if (mode === 'view' && data) {
        modalTitle.innerText = "View Inventory Packages";

        document.getElementById('inventory_id').value = data.id;
        document.getElementById('inventory_box_model').value = data.box_model;
        document.getElementById('inventory_serial').value = data.box_serial_no;
        document.getElementById('inventory_client').value = data.client ? data.client.id + ' - ' + data.client.name : 'No client';

        pkgCheckboxes().forEach(cb => {
            cb.checked = data.packages.some(pkg => pkg.id == cb.value);
            cb.disabled = true;
        });

        selectAll.disabled = true;
        updateCounts();
        saveBtn.style.display = 'none';
    }
}
</script>
@endsection
