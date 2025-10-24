@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php $page_title = "Channels"; $sub_title = "Reports"; ?>
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
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Channels</h5>
            <div class="text-muted">Select rows and View/Download</div>
        </div>

        <div class="card-body">
            <form id="selectionForm" method="POST">
                @csrf
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label fw-semibold" for="selectAll">Select All Records</label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;"></th>
                                <th>#</th>
                                <th>ID</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($channels as $i => $ch)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-check" name="selected_ids[]" value="{{ $ch->id }}">
                                    </td>
                                    <td>{{ $i + 1 }}</td>
                                    <td><span class="badge bg-secondary">{{ $ch->id }}</span></td>
                                    <td>{{ $ch->channel_name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No channels found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit"
                            class="btn btn-outline-secondary"
                            formaction="{{ route('channel-reports.preview') }}"
                            formmethod="POST"
                            formtarget="_blank">
                        View Selected
                    </button>

                    <button type="submit"
                            class="btn btn-dark"
                            formaction="{{ route('channel-reports.download') }}"
                            formmethod="POST">
                        Download Selected
                    </button>

                    <a href="{{ route('channel-reports.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('selectionForm');
    const master = document.getElementById('selectAll');
    if (!form || !master) return;

    const rowBoxes = () => form.querySelectorAll('.row-check');

    function refreshMasterState() {
        const boxes = Array.from(rowBoxes());
        const total = boxes.length;
        const checked = boxes.filter(cb => cb.checked).length;
        master.checked = total > 0 && checked === total;
        master.indeterminate = checked > 0 && checked < total;
    }

    master.addEventListener('change', () => {
        rowBoxes().forEach(cb => cb.checked = master.checked);
        refreshMasterState();
    });

    form.addEventListener('change', e => {
        if (e.target && e.target.classList.contains('row-check')) refreshMasterState();
    });

    refreshMasterState();
});
</script>
@endsection
