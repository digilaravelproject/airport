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
                        <li class="breadcrumb-item"><a href="#"><?php echo $sub_title ?></a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title ?></li>
                    </ol>
                </div>
                <h4 class="page-title"><?php echo $page_title ?></h4>
            </div>
        </div>
    </div>
    <!-- End Page Title -->

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
                    <!-- Optional: Pagination -->
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
            <!-- Inventory ID -->
            <div class="mb-3">
                <label class="form-label">Inventory ID</label>
                <input type="text" id="inventory_id" class="form-control" readonly>
            </div>

            <!-- Box Model -->
            <div class="mb-3">
                <label class="form-label">Box Model</label>
                <input type="text" id="inventory_box_model" class="form-control" readonly>
            </div>

            <!-- Serial No -->
            <div class="mb-3">
                <label class="form-label">Serial No</label>
                <input type="text" id="inventory_serial" class="form-control" readonly>
            </div>

            <!-- Client Info -->
            <div class="mb-3">
                <label class="form-label">Client</label>
                <input type="text" id="inventory_client" class="form-control" readonly>
            </div>

            <!-- Packages -->
            <div class="mb-3">
                <label class="form-label">Packages</label>
                <div id="packagesWrapper" class="border rounded p-2" style="max-height:200px; overflow-y:auto;">
                    @foreach($packages as $pkg)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="package_ids[]" value="{{ $pkg->id }}" id="pkg_{{ $pkg->id }}" disabled>
                            <label class="form-check-label" for="pkg_{{ $pkg->id }}">
                                {{ $pkg->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <!-- 🔴 Validation error -->
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
    let modalTitle = document.getElementById('inventoryPackageModalLabel');
    let form = document.getElementById('inventoryPackageForm');
    let saveBtn = document.getElementById('saveBtn');
    let methodDiv = document.getElementById('formMethod');

    // Reset form
    form.reset();
    document.getElementById('inventory_id').value = '';
    document.getElementById('inventory_box_model').value = '';
    document.getElementById('inventory_serial').value = '';
    document.getElementById('inventory_client').value = '';
    document.querySelectorAll('#packagesWrapper input[type=checkbox]').forEach(cb => { cb.checked = false; cb.disabled = true; });
    methodDiv.innerHTML = '';
    saveBtn.style.display = 'none';

    if (mode === 'edit' && data) {
        modalTitle.innerText = "Assign Packages";
        form.action = "/inventory-packages/" + data.id + "/assign";
        saveBtn.style.display = 'inline-block';

        document.getElementById('inventory_id').value = data.id;
        document.getElementById('inventory_box_model').value = data.box_model;
        document.getElementById('inventory_serial').value = data.box_serial_no;
        document.getElementById('inventory_client').value = data.client ? data.client.id + ' - ' + data.client.name : 'No client';

        document.querySelectorAll('#packagesWrapper input[type=checkbox]').forEach(cb => {
            cb.checked = data.packages.some(pkg => pkg.id == cb.value);
            cb.disabled = false;
        });
    }

    if (mode === 'view' && data) {
        modalTitle.innerText = "View Inventory Packages";

        document.getElementById('inventory_id').value = data.id;
        document.getElementById('inventory_box_model').value = data.box_model;
        document.getElementById('inventory_serial').value = data.box_serial_no;
        document.getElementById('inventory_client').value = data.client ? data.client.id + ' - ' + data.client.name : 'No client';

        document.querySelectorAll('#packagesWrapper input[type=checkbox]').forEach(cb => {
            cb.checked = data.packages.some(pkg => pkg.id == cb.value);
            cb.disabled = true;
        });

        saveBtn.style.display = 'none';
    }
}
</script>
@endsection
