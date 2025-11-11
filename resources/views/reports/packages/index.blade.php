@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php $page_title = "Packages"; $sub_title = "Reports"; ?>

    @php
        /**
         * Sorting helpers (mirror the style you used in the Live view)
         */
        function nextDirPkg($col) {
            $currentSort = request('sort','id');
            $currentDir  = request('direction','asc'); // default asc for a natural first click
            if ($currentSort === $col) return $currentDir === 'asc' ? 'desc' : 'asc';
            return 'asc';
        }
        function sortIconPkg($col) {
            $currentSort = request('sort','id');
            $currentDir  = request('direction','asc');
            if ($currentSort !== $col) return 'fas fa-sort text-muted';
            return $currentDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
        function sortUrlPkg($col) {
            $params = request()->all();
            $params['sort'] = $col;
            $params['direction'] = nextDirPkg($col);
            return request()->fullUrlWithQuery($params);
        }
    @endphp

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
            <h5 class="mb-0">All Packages</h5>
            <div class="text-muted">Select rows and View/Download</div>
        </div>

        <div class="card-body">
            <form id="selectionForm" method="POST">
                @csrf
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label fw-semibold" for="selectAll">Select All Records (this page)</label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;"></th>
                                <th style="white-space:nowrap;">
                                    <a href="{{ sortUrlPkg('id') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        ID <i class="{{ sortIconPkg('id') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ sortUrlPkg('name') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                        Name <i class="{{ sortIconPkg('name') }}"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $i => $pkg)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-check" name="selected_ids[]" value="{{ $pkg->id }}">
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $pkg->id }}</span></td>
                                    <td>{{ $pkg->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No packages found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination (keeps your layout/design) --}}
                <div class="mt-3">
                    {{ $packages->appends(request()->except('page'))->links() }}
                </div>

                <div class="mt-3 d-flex gap-2">
                    {{-- These buttons are intercepted by JS to submit to a new tab without triggering any loader on this page --}}
                    <button type="button"
                            class="btn btn-outline-secondary js-open-in-newtab"
                            data-action="{{ route('package-reports.preview') }}"
                            data-method="POST">
                        View Selected
                    </button>

                    <button type="button"
                            class="btn btn-dark js-open-in-newtab"
                            data-action="{{ route('package-reports.download') }}"
                            data-method="POST">
                        Download Selected
                    </button>

                    <a href="{{ route('package-reports.index') }}" class="btn btn-light">Reset</a>
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

    /**
     * Submit to a NEW TAB without submitting the original form.
     * This prevents any page-level submit handlers/loader from running on the current page.
     * We create a temporary form with only the needed fields and POST it to target="_blank".
     */
    function submitToNewTab(actionUrl, method) {
        const temp = document.createElement('form');
        temp.style.display = 'none';
        temp.method = method || 'POST';
        temp.action = actionUrl;
        temp.target = '_blank';

        // CSRF token
        const csrf = form.querySelector('input[name="_token"]');
        if (csrf) {
            const t = document.createElement('input');
            t.type = 'hidden';
            t.name = '_token';
            t.value = csrf.value;
            temp.appendChild(t);
        }

        // Selected IDs
        form.querySelectorAll('input.row-check:checked').forEach(cb => {
            const h = document.createElement('input');
            h.type = 'hidden';
            h.name = 'selected_ids[]';
            h.value = cb.value;
            temp.appendChild(h);
        });

        document.body.appendChild(temp);
        temp.submit();
        setTimeout(() => temp.remove(), 1000);
    }

    // Intercept the two action buttons
    document.querySelectorAll('.js-open-in-newtab').forEach(btn => {
        btn.addEventListener('click', function () {
            const action = this.getAttribute('data-action');
            const method = this.getAttribute('data-method') || 'POST';

            // If nothing selected, let backend show the same validation error in the new tab.
            submitToNewTab(action, method);
        });
    });
});
</script>
@endsection
