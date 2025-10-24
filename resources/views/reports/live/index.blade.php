@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php
        $page_title = "Currently Live Boxes (with Channel Packages)";
        $sub_title  = "Reports";
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-light d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h5 class="mb-2 mb-md-0">Currently Live Boxes</h5>
            <div class="text-muted">
                Live = HTTP <code>/system</code> or <code>/system/health</code> success, else ICMP ping
            </div>
        </div>

        <div class="card-body">
            <!-- One form for both actions -->
            <form id="selectionForm" method="POST">
                @csrf

                <div class="d-flex align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label fw-semibold" for="selectAll">
                            Select All Records
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;"></th>
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
                                    <td>
                                        <input type="checkbox" class="row-check" name="selected_ids[]" value="{{ $inv->id }}">
                                    </td>
                                    <td>{{ $idx + 1 }}</td>
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
                                    <td><span class="badge bg-success">Live</span></td>
                                    <td>{{ $inv->warranty_date ? \Carbon\Carbon::parse($inv->warranty_date)->format('Y-m-d') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No live boxes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <button type="submit"
                            class="btn btn-outline-secondary"
                            formaction="{{ route('live-reports.preview') }}"
                            formmethod="POST"
                            formtarget="_blank">
                        View Selected
                    </button>

                    <button type="submit"
                            class="btn btn-dark"
                            formaction="{{ route('live-reports.download') }}"
                            formmethod="POST">
                        Download Selected
                    </button>

                    <a href="{{ route('live-reports.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form   = document.getElementById('selectionForm');
    const master = document.getElementById('selectAll');
    if (!form || !master) return;

    const rowBoxes = () => form.querySelectorAll('input.row-check');

    function refreshMasterState() {
        const boxes = Array.from(rowBoxes());
        const total = boxes.length;
        const checked = boxes.filter(cb => cb.checked).length;
        master.checked = (checked === total && total > 0);
        master.indeterminate = (checked > 0 && checked < total);
    }

    master.addEventListener('change', function () {
        rowBoxes().forEach(cb => cb.checked = master.checked);
        refreshMasterState();
    });

    form.addEventListener('change', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('row-check')) {
            refreshMasterState();
        }
    });

    refreshMasterState();
});
</script>
@endsection
