@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php $page_title = "Inventories (Online Only)"; $sub_title = "Setup Boxes"; ?>

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

    <div class="row">
        <!-- Left: Online Inventories Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Online Inventories</h5>
                    <form method="GET" action="{{ route('utility.online') }}" class="d-flex">
                        <input type="text" name="search" value="{{ $search ?? '' }}" 
                               class="form-control form-control-sm me-2" placeholder="Search">
                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('utility.online') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Box ID</th>
                                    <th>Model</th>
                                    <th>Serial No</th>
                                    <th>MAC</th>
                                    <th>Firmware</th>
                                    <th>Client</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse ($inventories as $key => $inventory)
                                <tr onclick="window.location='?inventory_id={{ $inventory->id }}&page={{ request('page',1) }}&search={{ urlencode($search ?? '') }}'" style="cursor:pointer;">
                                    <td>{{ ($inventories->currentPage()-1)*$inventories->perPage() + $key + 1 }}</td>
                                    <td><span class="badge bg-success">{{ $inventory->box_id }}</span></td>
                                    <td>{{ $inventory->box_model }}</td>
                                    <td>{{ $inventory->box_serial_no }}</td>
                                    <td>{{ $inventory->box_mac }}</td>
                                    <td>{{ $inventory->box_fw }}</td>
                                    <td>{{ $inventory->client?->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No online boxes found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $inventories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Selected Box Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Box Details</h6>
                </div>
                <div class="card-body">
                    @if($selectedInventory)
                        <div class="mb-3">
                            <label class="form-label">Box ID</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->id }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_model }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">MAC</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_mac }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Serial No</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_serial_no }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Firmware</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_fw }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">RCU Model</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->box_remote_model }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Warranty Date</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->warranty_date?->format('Y-m-d') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->client?->name }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" value="{{ $selectedInventory->location }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <div>
                                @if($selectedInventory->photo)
                                    <img src="{{ asset('storage/'.$selectedInventory->photo) }}" class="img-thumbnail" width="120">
                                @else
                                    <span class="text-muted">No photo</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-muted">Click a row to view details.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
