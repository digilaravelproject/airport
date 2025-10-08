@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php
        $page_title = "Generate Inventory Reports";
        $sub_title = "Reports";
    ?>
    <!-- Page-Title -->
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
    <!-- End Page Title -->

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Generate Inventory Reports</h5>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('reports.index') }}" class="mb-3">
                        <div class="row g-3">
                            <!-- Status -->
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="" {{ request('status') === null ? 'selected' : '' }}>All</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Client -->
                            <div class="col-md-3">
                                <label class="form-label">Client</label>
                                <select name="client_id" class="form-select">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ (string)request('client_id') === (string)$client->id ? 'selected' : '' }}>
                                            {{ $client->id }} - {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Package -->
                            <div class="col-md-3">
                                <label class="form-label">Package</label>
                                <select name="package_id" class="form-select">
                                    <option value="">All Packages</option>
                                    @foreach($packages as $pkg)
                                        <option value="{{ $pkg->id }}" {{ (string)request('package_id') === (string)$pkg->id ? 'selected' : '' }}>
                                            {{ $pkg->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Warranty Date -->
                            <div class="col-md-3">
                                <label class="form-label">Warranty Before</label>
                                <input type="date" name="warranty_before" class="form-control" value="{{ request('warranty_before') }}">
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>

                            <!-- View PDF (opens in new tab) -->
                            <button type="submit"
                                    class="btn btn-outline-secondary"
                                    formaction="{{ route('reports.preview') }}"
                                    formmethod="GET"
                                    target="_blank"
                                    data-no-loader="true"> <!-- 👈 added -->
                                View PDF
                            </button>

                            <!-- Download PDF -->
                            <button type="submit"
                                    class="btn btn-dark"
                                    formaction="{{ route('reports.download') }}"
                                    formmethod="GET"
                                    data-no-loader="true"> <!-- 👈 added -->
                                Download PDF
                            </button>

                            <!-- Reset filters -->
                            <a href="{{ route('reports.index') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <!-- Results Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Box ID</th>
                                    <th>Box Model</th>
                                    <th>Serial No</th>
                                    <th>MAC ID</th>
                                    <th>Client</th>
                                    <th>Packages</th>
                                    <th>Status</th>
                                    <th>Warranty Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventories as $idx => $inv)
                                    <tr>
                                        <td>{{ $inventories->firstItem() + $idx }}</td>
                                        <td><span class="badge bg-secondary">{{ $inv->box_id }}</span></td>
                                        <td>{{ $inv->box_model }}</td>
                                        <td>{{ $inv->box_serial_no }}</td>
                                        <td>{{ $inv->box_mac }}</td>
                                        <td>
                                            @if($inv->client)
                                                {{ $inv->client->id }} - {{ $inv->client->name }}
                                            @else
                                                <span class="text-muted">No client</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($inv->packages->count())
                                                {{ $inv->packages->pluck('name')->join(', ') }}
                                            @else
                                                <span class="text-muted">No packages</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ (string)$inv->status === '1' ? 'bg-success' : 'bg-danger' }}">
                                                {{ (string)$inv->status === '1' ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $inv->warranty_date ? \Carbon\Carbon::parse($inv->warranty_date)->format('Y-m-d') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No records found.</td>
                                    </tr>
                                @endforelse
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
@endsection
