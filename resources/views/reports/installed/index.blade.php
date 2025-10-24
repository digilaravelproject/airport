@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php
        $page_title = "Installed Boxes (with Channel Packages)";
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
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Total Installed Boxes</h5>
        </div>

        <div class="card-body">
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
                                    <td>
                                        <span class="badge bg-success">Installed</span>
                                    </td>
                                    <td>{{ $inv->warranty_date ? \Carbon\Carbon::parse($inv->warranty_date)->format('Y-m-d') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No installed boxes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <button type="submit"
                            class="btn btn-outline-secondary"
                            formaction="{{ route('installed-reports.preview') }}"
                            formtarget="_blank">
                        View Selected
                    </button>

                    <button type="submit"
                            class="btn btn-dark"
                            formaction="{{ route('installed-reports.download') }}">
                        Download Selected
                    </button>

                    <a href="{{ route('installed-reports.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');

    // Helper to get ALL row checkboxes currently in the DOM
    const getRowChecks = () => Array.from(document.querySelectorAll('input.row-check'));

    function setAllRows(state) {
        getRowChecks().forEach(cb => { cb.checked = state; });
        selectAll.indeterminate = false; // reset the dash state
        selectAll.checked = state;       // reflect final state on master
    }

    // Master checkbox toggles all rows
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            setAllRows(e.target.checked);
        });
    }

    // Keep master checkbox state in sync (checked / unchecked / indeterminate)
    document.addEventListener('change', (e) => {
        if (!e.target.classList.contains('row-check')) return;

        const cbs = getRowChecks();
        const checkedCount = cbs.filter(cb => cb.checked).length;

        if (checkedCount === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        } else if (checkedCount === cbs.length) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = true; // dashed state
        }
    });
});
</script>
@endsection
