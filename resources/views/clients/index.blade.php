@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php
        $page_title = "Clients";
        $sub_title = "Subscribers";
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
        <!-- Left: Clients Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Clients List</h5>
                    <form method="GET" action="{{ route('clients.index') }}" class="d-flex">
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-sm me-2" placeholder="Search">

                        <!-- Dropdown for search field -->
                        <select name="field" class="form-select form-select-sm me-2" style="width:150px;">
                            <option value="all" {{ request('field') == 'all' ? 'selected' : '' }}>All Fields</option>
                            <option value="id" {{ request('field') == 'id' ? 'selected' : '' }}>Client ID</option>
                            <option value="name" {{ request('field') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="contact_person" {{ request('field') == 'contact_person' ? 'selected' : '' }}>Contact Person</option>
                            <option value="contact_no" {{ request('field') == 'contact_no' ? 'selected' : '' }}>Mobile</option>
                            <option value="city" {{ request('field') == 'city' ? 'selected' : '' }}>City</option>
                            <option value="email" {{ request('field') == 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Client ID</th>
                                    <th>Name</th>
                                    <th>Contact Person</th>
                                    <th>Mobile</th>
                                    <th>City</th>
                                    <th>Joined On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clients as $key => $client)
                                    <tr onclick="window.location='?client_id={{ $client->id }}'" style="cursor: pointer;">
                                        <td>{{ $key+1 }}</td>
                                        <td><span class="badge bg-secondary">{{ $client->id }}</span></td>
                                        <td>{{ $client->name }}</td>
                                        <td>{{ $client->contact_person }}</td>
                                        <td>{{ $client->contact_no }}</td>
                                        <td>{{ $client->city }}</td>
                                        <td>{{ $client->created_at->format('d-m-Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Subscriber Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Subscriber Details</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" onclick="enableForm('add')">Add</button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="enableForm('edit')" {{ !$selectedClient ? 'disabled' : '' }}>Edit</button>
                        <button type="button" class="btn btn-sm btn-info" onclick="enableForm('view')" {{ !$selectedClient ? 'disabled' : '' }}>View</button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" id="clientForm"
                          action="{{ $selectedClient ? route('clients.update',$selectedClient->id) : route('clients.store') }}">
                        @csrf
                        @if($selectedClient) @method('PUT') @endif

                        <!-- Form Fields -->
                        <div class="mb-3">
                            <label class="form-label">Client ID</label>
                            <input type="text" class="form-control" value="{{ $selectedClient->id ?? '' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $selectedClient->name ?? '') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" disabled>
                                <option value="Paid" {{ old('type', $selectedClient->type ?? '')=='Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Free" {{ old('type', $selectedClient->type ?? '')=='Free' ? 'selected' : '' }}>Free</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $selectedClient->contact_person ?? '') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $selectedClient->contact_no ?? '') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $selectedClient->email ?? '') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Billing Address</label>
                            <textarea name="address" class="form-control" rows="2" readonly>{{ old('address', $selectedClient->address ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $selectedClient->city ?? '') }}" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIN</label>
                                <input type="text" name="pin" class="form-control" value="{{ old('pin', $selectedClient->pin ?? '') }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GST No</label>
                                <input type="text" name="gst_no" class="form-control" value="{{ old('gst_no', $selectedClient->gst_no ?? '') }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $selectedClient->state ?? '') }}" readonly>
                        </div>

                        <hr>
                        <h6 class="text-muted mb-2">Boxes</h6>
                        <div class="inventory-panel">
                            <div class="inventory-scroll table-responsive">
                                <table class="table table-sm table-bordered inventory-table mb-0">
                                    <thead class="table-light inventory-head">
                                        <tr>
                                            <th style="width:72px;">ID</th>
                                            <th>IP</th>
                                            <th>MAC</th>
                                            <th>LOCATION</th> <!-- changed -->
                                            <th>PACKAGE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $inventories = $selectedClient ? $selectedClient->inventories : collect();
                                        @endphp
                                        @forelse($inventories as $inv)
                                            <tr onclick="window.location='{{ route('inventories.show', $inv->id) }}'" style="cursor:pointer;">
                                                <td>{{ $inv->box_id }}</td>
                                                <td class="text-monospace">{{ $inv->box_ip }}</td>
                                                <td class="text-monospace">{{ $inv->box_mac }}</td>
                                                <td>{{ $inv->location ?? '-' }}</td> <!-- replaced SERIAL with LOCATION -->
                                                <td>
                                                    @if($inv->relationLoaded('packages') && $inv->packages->count())
                                                        {{ $inv->packages->pluck('name')->join(', ') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No inventory found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" id="saveBtn" class="btn btn-dark px-4 mt-3" style="display:none;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .inventory-panel { border:1px solid #dee2e6; border-radius:.25rem; }
    .inventory-scroll { max-height: 220px; overflow: auto; }
    .inventory-table th, .inventory-table td { padding:.35rem .5rem; vertical-align: middle; }
    .inventory-head th { position: sticky; top: 0; z-index: 1; }
    .text-monospace { font-family: monospace; }
</style>

<script>
function enableForm(mode) {
    let form = document.getElementById('clientForm');
    let inputs = form.querySelectorAll('input, select, textarea');
    let saveBtn = document.getElementById('saveBtn');

    if (mode === 'add') {
        inputs.forEach(el => {
            if (el.name && el.type !== "hidden") {
                el.value='';
                el.readOnly = false;
                el.disabled = false;
            }
        });
        saveBtn.style.display = 'inline-block';
        form.action = "{{ route('clients.store') }}";
        let methodInput = form.querySelector('input[name=\"_method\"]');
        if (methodInput) methodInput.remove();
    }

    if (mode === 'edit') {
        inputs.forEach(el => {
            if (el.tagName.toLowerCase() === 'select' || el.tagName.toLowerCase() === 'textarea') {
                el.disabled = false;
                el.readOnly = false;
            } else if (el.type !== "hidden") {
                el.readOnly = false;
            }
        });
        saveBtn.style.display = 'inline-block';
    }

    if (mode === 'view') {
        inputs.forEach(el => {
            el.disabled = true;
            el.readOnly = true;
        });
        saveBtn.style.display = 'none';
    }
}
</script>
@endsection
