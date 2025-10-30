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

            {{-- Per-page selector (pagination) --}}
            <form method="GET" action="{{ route('channel-reports.index') }}" class="d-flex align-items-center gap-2">
                <label class="text-muted mb-0">Per page</label>
                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([10,25,50,100] as $pp)
                        <option value="{{ $pp }}" {{ (int)request('per_page', $perPage) === $pp ? 'selected' : '' }}>
                            {{ $pp }}
                        </option>
                    @endforeach
                </select>
                {{-- Preserve current genre filter (and any other query params) --}}
                @foreach(request()->except('per_page') as $k=>$v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
            </form>
        </div>

        <div class="card-body">

            {{-- Channel Genre Filter --}}
            <form method="GET" action="{{ route('channel-reports.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Channel Genre</label>
                    <select name="channel_genre" class="form-select">
                        <option value="">-- All Genres --</option>
                        @foreach($genres as $g)
                            <option value="{{ $g }}" {{ request('channel_genre') === $g ? 'selected' : '' }}>
                                {{ $g }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('channel-reports.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>

                {{-- keep current per_page on search --}}
                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage) }}">
            </form>

            <form id="selectionForm" method="POST" target="_self">
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
                                <th>ID</th>
                                <th>Name</th>
                                <th>Broadcast</th>
                                <th>Genre</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($channels as $i => $ch)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-check" name="selected_ids[]" value="{{ $ch->id }}">
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $ch->id }}</span></td>
                                    <td>{{ $ch->channel_name }}</td>
                                    <td>{{ $ch->broadcast ?? '-' }}</td>
                                    <td>{{ $ch->channel_genre ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No channels found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination links --}}
                <div class="mt-3">
                    {{ $channels->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            id="btnViewSelected"
                            data-action="{{ route('channel-reports.preview') }}">
                        View Selected
                    </button>

                    <button type="button"
                            class="btn btn-dark"
                            id="btnDownloadSelected"
                            data-action="{{ route('channel-reports.download') }}">
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
    const btnView = document.getElementById('btnViewSelected');
    const btnDownload = document.getElementById('btnDownloadSelected');

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

    // Open PDF in new tab via fetch so the current page doesn't "load" forever
    function openInNewTab(actionUrl, isDownload = false) {
        const formData = new FormData(form);
        const newTab = window.open('', '_blank');

        fetch(actionUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.blob())
        .then(blob => {
            const fileURL = URL.createObjectURL(blob);
            if (isDownload) {
                const a = document.createElement('a');
                a.href = fileURL;
                a.download = 'channels_selected.pdf';
                a.click();
                newTab.close();
            } else {
                newTab.location.href = fileURL;
            }
        })
        .catch(err => {
            newTab.document.write('<p style="color:red;padding:1rem;">Error generating PDF.</p>');
            console.error(err);
        });
    }

    btnView?.addEventListener('click', (e) => {
        e.preventDefault();
        openInNewTab(btnView.dataset.action, false);
    });

    btnDownload?.addEventListener('click', (e) => {
        e.preventDefault();
        openInNewTab(btnDownload.dataset.action, true);
    });
});
</script>
@endsection
