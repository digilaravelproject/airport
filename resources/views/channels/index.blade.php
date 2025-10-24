@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <?php
        $page_title = "Channels";
        $sub_title = "Management";
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

    <!-- Channel Import Section (same UX as Inventory Import) -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color:#0f172a;">
                    <h6 class="text-light mb-0">
                        <i class="fas fa-file-import me-2"></i>Import Channel Data
                    </h6>
                    <small class="text-light">Upload Excel (.xlsx, .xls, .csv)</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('channels.import') }}" enctype="multipart/form-data" id="channelImportForm">
                        @csrf
                        <div class="row align-items-end g-3">
                            <div class="col-md-6">
                                <label for="file" class="form-label fw-semibold">Select File</label>
                                <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-upload me-1"></i> Import
                                </button>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="{{ asset('sample/Channel_Import_Format.xlsx') }}"
                                   class="btn btn-outline-secondary w-100" download>
                                    <i class="fas fa-download me-1"></i> Sample File
                                </a>
                            </div>
                        </div>
                    </form>

                    @if ($errors->has('file'))
                        <div class="text-danger small mt-2">{{ $errors->first('file') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- /Channel Import Section -->

    <div class="row">
        <!-- Left: Channels Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Channels List</h5>
                    <form method="GET" action="{{ route('channels.index') }}" class="d-flex">
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-sm me-2" placeholder="Search">

                        <!-- search-field dropdown -->
                        <select name="field" class="form-select form-select-sm me-2" style="width:160px;">
                            <option value="all" {{ request('field','all')=='all' ? 'selected' : '' }}>All Fields</option>
                            <option value="id" {{ request('field')=='id' ? 'selected' : '' }}>Channel ID</option>
                            <option value="channel_name" {{ request('field')=='channel_name' ? 'selected' : '' }}>Name</option>
                            <option value="channel_genre" {{ request('field')=='channel_genre' ? 'selected' : '' }}>Genre</option>
                            <option value="channel_resolution" {{ request('field')=='channel_resolution' ? 'selected' : '' }}>Resolution</option>
                            <option value="channel_type" {{ request('field')=='channel_type' ? 'selected' : '' }}>Type</option>
                            <option value="language" {{ request('field')=='language' ? 'selected' : '' }}>Language</option>
                            <option value="active" {{ request('field')=='active' ? 'selected' : '' }}>Active</option>
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('channels.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Channel ID</th>
                                    <th>Name</th>
                                    <th>Genre</th>
                                    <th>Resolution</th>
                                    <th>Type</th>
                                    <th>Language</th>
                                    <th>Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($channels as $key => $channel)
                                    <tr onclick="window.location='?channel_id={{ $channel->id }}'" style="cursor: pointer;">
                                        <td>{{ $key+1 }}</td>
                                        <td><span class="badge bg-secondary">{{ $channel->id }}</span></td>
                                        <td>{{ $channel->channel_name }}</td>
                                        <td>{{ $channel->channel_genre }}</td>
                                        <td>{{ $channel->channel_resolution }}</td>
                                        <td>{{ $channel->channel_type }}</td>
                                        <td>{{ $channel->language ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $channel->active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $channel->active ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- Add pagination here if you later switch to paginate() --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Channel Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Channel Details</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" onclick="enableForm('add')">Add</button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="enableForm('edit')" {{ !$selectedChannel ? 'disabled' : '' }}>Edit</button>
                        <button type="button" class="btn btn-sm btn-info" onclick="enableForm('view')" {{ !$selectedChannel ? 'disabled' : '' }}>View</button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" id="channelForm"
                        action="{{ $selectedChannel ? route('channels.update',$selectedChannel->id) : route('channels.store') }}">
                        @csrf
                        @if($selectedChannel) @method('PUT') @endif

                        <!-- Channel Fields -->
                        <div class="mb-3">
                            <label class="form-label">Channel ID</label>
                            <input type="text" class="form-control" value="{{ $selectedChannel->id ?? '' }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="channel_name" class="form-control @error('channel_name') is-invalid @enderror"
                                value="{{ old('channel_name', $selectedChannel->channel_name ?? '') }}" readonly>
                            @error('channel_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Source IN</label>
                            <input type="text" name="channel_source_in" class="form-control"
                                value="{{ old('channel_source_in', $selectedChannel->channel_source_in ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Source Details</label>
                            <input type="text" name="channel_source_details" class="form-control"
                                value="{{ old('channel_source_details', $selectedChannel->channel_source_details ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stream Type Out</label>
                            <input type="text" name="channel_stream_type_out" class="form-control"
                                value="{{ old('channel_stream_type_out', $selectedChannel->channel_stream_type_out ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" name="channel_url" class="form-control"
                                value="{{ old('channel_url', $selectedChannel->channel_url ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Genre</label>
                            <input type="text" name="channel_genre" class="form-control"
                                value="{{ old('channel_genre', $selectedChannel->channel_genre ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Resolution</label>
                            <input type="text" name="channel_resolution" class="form-control"
                                value="{{ old('channel_resolution', $selectedChannel->channel_resolution ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="channel_type" class="form-select @error('channel_type') is-invalid @enderror" disabled>
                                <option value="Paid" {{ old('channel_type', $selectedChannel->channel_type ?? '')=='Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Free" {{ old('channel_type', $selectedChannel->channel_type ?? '')=='Free' ? 'selected' : '' }}>Free</option>
                            </select>
                            @error('channel_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Language</label>
                            <input type="text" name="language" class="form-control"
                                value="{{ old('language', $selectedChannel->language ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Encryption</label>
                            <select name="encryption" class="form-select" disabled>
                                <option value="1" {{ old('encryption', $selectedChannel->encryption ?? '') ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('encryption', $selectedChannel->encryption ?? '')==0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Active</label>
                            <select name="active" class="form-select" disabled>
                                <option value="1" {{ old('active', $selectedChannel->active ?? '') ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('active', $selectedChannel->active ?? '')==0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <!-- Save Button -->
                        <div class="text-end">
                            <button type="submit" id="saveBtn" class="btn btn-dark px-4" style="display:none;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function enableForm(mode) {
    let form = document.getElementById('channelForm');
    let inputs = form.querySelectorAll('input, select');
    let saveBtn = document.getElementById('saveBtn');

    if (mode === 'add') {
        inputs.forEach(el => {
            if (el.name && el.type !== "hidden") {
                el.value=''; el.readOnly = false; el.disabled = false;
            }
        });
        saveBtn.style.display = 'inline-block';
        form.action = "{{ route('channels.store') }}";

        let methodInput = form.querySelector('input[name=\"_method\"]');
        if (methodInput) methodInput.remove();
    }

    if (mode === 'edit') {
        inputs.forEach(el => {
            if (el.tagName.toLowerCase() === 'select') { el.disabled = false; }
            else if (el.type !== "hidden") { el.readOnly = false; }
        });
        saveBtn.style.display = 'inline-block';
    }

    if (mode === 'view') {
        inputs.forEach(el => {
            if (el.tagName.toLowerCase() === 'select') { el.disabled = true; }
            else if (el.type !== "hidden") { el.readOnly = true; }
        });
        saveBtn.style.display = 'none';
    }
}
</script>
@endsection
