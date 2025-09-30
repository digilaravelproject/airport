@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <?php
        $page_title = "Packages";
        $sub_title = "Channel Management";
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
                                                    onclick="openForm('edit', {{ $package->toJson() }})">
                                                Edit
                                            </button>
                                            <button class="btn btn-info btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#packageModal"
                                                    onclick="openForm('view', {{ $package->toJson() }})">
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
                    <!-- If you want pagination -->
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

                <!-- Channels (Checkboxes) -->
                <div class="mb-3">
                    <label class="form-label">Channels</label>
                    <div id="channelsWrapper" class="border rounded p-2" style="max-height:200px; overflow-y:auto;">
                        @foreach($channels as $ch)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="channel_id[]" 
                                    value="{{ $ch->id }}" id="channel_{{ $ch->id }}" disabled>
                                <label class="form-check-label" for="channel_{{ $ch->id }}">
                                    {{ $ch->channel_name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('channel_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Active</label>
                    <select name="active" id="active" class="form-select" disabled>
                        <option value="Yes" {{ old('active') == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('active') == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('active')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
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
function openForm(mode, data = null) {
    let modalTitle = document.getElementById('packageModalLabel');
    let form = document.getElementById('packageForm');
    let saveBtn = document.getElementById('saveBtn');
    let methodDiv = document.getElementById('formMethod');

    // Reset form
    form.reset();
    document.getElementById('package_id').value = '';
    document.getElementById('name').readOnly = true;
    document.querySelectorAll('#channelsWrapper input[type=checkbox]').forEach(cb => { cb.checked = false; cb.disabled = true; });
    document.getElementById('active').disabled = true;
    methodDiv.innerHTML = '';
    saveBtn.style.display = 'none';
    form.action = "{{ route('packages.store') }}";

    // Mode handling
    if (mode === 'add') {
        modalTitle.innerText = "Add Package";
        saveBtn.style.display = 'inline-block';
        document.getElementById('name').readOnly = false;
        document.querySelectorAll('#channelsWrapper input[type=checkbox]').forEach(cb => cb.disabled = false);
        document.getElementById('active').disabled = false;
    }

    if (mode === 'edit' && data) {
        modalTitle.innerText = "Edit Package";
        form.action = "/packages/" + data.id;
        methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        saveBtn.style.display = 'inline-block';

        document.getElementById('package_id').value = data.id;
        document.getElementById('name').value = data.name;
        document.getElementById('active').value = data.active;

        document.querySelectorAll('#channelsWrapper input[type=checkbox]').forEach(cb => {
            cb.checked = data.channels.some(ch => ch.id == cb.value);
            cb.disabled = false;
        });

        document.getElementById('name').readOnly = false;
        document.getElementById('active').disabled = false;
    }

    if (mode === 'view' && data) {
        modalTitle.innerText = "View Package";

        document.getElementById('package_id').value = data.id;
        document.getElementById('name').value = data.name;
        document.getElementById('active').value = data.active;

        document.querySelectorAll('#channelsWrapper input[type=checkbox]').forEach(cb => {
            cb.checked = data.channels.some(ch => ch.id == cb.value);
            cb.disabled = true;
        });

        document.getElementById('name').readOnly = true;
        document.getElementById('active').disabled = true;
    }
}
</script>
@endsection
