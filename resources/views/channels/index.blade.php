@extends('layouts.app')

@section('content')
<style>
    .import-header { background-color: #0f172a !important; }
    .summary-card { cursor:pointer; transition: transform .05s ease-in; }
    .summary-card:hover { transform: translateY(-1px); }
    .summary-active { border-color:#0d6efd !important; box-shadow: 0 0 0 .1rem rgba(13,110,253,.15); }
    .table thead a { font-weight:600; }

    /* NEW: keep Channel Name in a single line with ellipsis (no wrap) */
    .table tbody td:nth-child(2) {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px; /* adjust if you need a different visible width */
    }
</style>

@php
    // Helpers for sortable headers (DataTables-like arrows)
    function nextDirCh($col) {
        $currentSort = request('sort','id');
        $currentDir  = request('direction','desc');
        if ($currentSort === $col) return $currentDir === 'asc' ? 'desc' : 'asc';
        return 'asc';
    }
    function sortIconCh($col) {
        $currentSort = request('sort','id');
        $currentDir  = request('direction','desc');
        if ($currentSort !== $col) return 'fas fa-sort text-muted';
        return $currentDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }
    function sortUrlCh($col) {
        $params = request()->all();
        $params['sort'] = $col;
        $params['direction'] = nextDirCh($col);
        return request()->fullUrlWithQuery($params);
    }
@endphp

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

    <!-- Channel Import Section -->
    <!-- <div class="row mb-3">
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
    </div> -->
    <!-- /Channel Import Section -->

    <div class="row">
        <!-- Left: Channels Table -->
        <div class="col-md-9">
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
                            <option value="broadcast" {{ request('field')=='broadcast' ? 'selected' : '' }}>Broadcast</option>
                            <option value="channel_genre" {{ request('field')=='channel_genre' ? 'selected' : '' }}>Genre</option>
                            <option value="channel_resolution" {{ request('field')=='channel_resolution' ? 'selected' : '' }}>Resolution</option>
                            <option value="channel_type" {{ request('field')=='channel_type' ? 'selected' : '' }}>Type</option>
                            <option value="language" {{ request('field')=='language' ? 'selected' : '' }}>Language</option>
                            <option value="channel_source_in" {{ request('field')=='channel_source_in' ? 'selected' : '' }}>Source In</option>
                            <option value="channel_source_details" {{ request('field')=='channel_source_details' ? 'selected' : '' }}>Source Details</option>
                            <option value="active" {{ request('field')=='active' ? 'selected' : '' }}>Active</option>
                        </select>

                        {{-- Preserve current sort in the search form --}}
                        <input type="hidden" name="sort" value="{{ request('sort','id') }}">
                        <input type="hidden" name="direction" value="{{ request('direction','desc') }}">

                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('channels.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <a href="{{ sortUrlCh('id') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Channel ID <i class="{{ sortIconCh('id') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('channel_name') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Name <i class="{{ sortIconCh('channel_name') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('broadcast') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Broadcaster <i class="{{ sortIconCh('broadcast') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('channel_genre') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Genre <i class="{{ sortIconCh('channel_genre') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('channel_resolution') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Resolution <i class="{{ sortIconCh('channel_resolution') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('channel_type') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Type <i class="{{ sortIconCh('channel_type') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('language') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Language <i class="{{ sortIconCh('language') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('channel_source_in') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Source In <i class="{{ sortIconCh('channel_source_in') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('channel_source_details') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Source Details <i class="{{ sortIconCh('channel_source_details') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlCh('active') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Active <i class="{{ sortIconCh('active') }}"></i>
                                        </a>
                                    </th>

                                    {{-- NEW: Actions column --}}
                                    <th style="width:140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($channels as $key => $channel)
                                    <tr onclick="window.location='{{ request()->fullUrlWithQuery(['channel_id'=>$channel->id]) }}'" style="cursor: pointer;">
                                        <td><span class="badge bg-secondary">{{ $channel->id }}</span></td>
                                        <td>{{ $channel->channel_name }}</td>
                                        <td>{{ $channel->broadcast }}</td>
                                        <td>{{ $channel->channel_genre }}</td>
                                        <td>{{ $channel->channel_resolution }}</td>
                                        <td>{{ $channel->channel_type }}</td>
                                        <td>{{ $channel->language }}</td>
                                        <td>{{ $channel->channel_source_in ?? '-' }}</td>
                                        <td>{{ $channel->channel_source_details ?? '-' }}</td>
                                        <td>{{ $channel->language ?? '-' }}</td>

                                        <!-- Actions -->
                                        <td>
                                            <form method="POST" action="{{ route('channels.destroy', $channel->id) }}"
                                                  style="display:inline-block;margin:0;padding:0;"
                                                  onsubmit="return confirm('Are you sure you want to delete this channel? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if($channels->isEmpty())
                            <div class="text-center text-muted py-3">No records found.</div>
                        @endif
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            @if($channels->total())
                                Showing {{ $channels->firstItem() }}–{{ $channels->lastItem() }} of {{ $channels->total() }}
                            @endif
                        </div>
                        <div>
                            {{ $channels->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Channel Details -->
        <div class="col-md-3">
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

                        <!-- Broadcast -->
                        <div class="mb-3">
                            <label class="form-label">Broadcast</label>
                            <input type="text" name="broadcast" class="form-control @error('broadcast') is-invalid @enderror"
                                value="{{ old('broadcast', $selectedChannel->broadcast ?? '') }}" readonly>
                            @error('broadcast')
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
