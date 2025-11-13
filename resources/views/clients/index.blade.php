@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <?php
        $page_title = "Clients";
        $sub_title = "Subscribers";
        // Helper: get next direction for a given column
        function nextDir($col, $currentSort, $currentDir) {
            if ($currentSort === $col) {
                return $currentDir === 'asc' ? 'desc' : 'asc';
            }
            return 'asc';
        }
        // Helper: render sort icon class
        function sortIcon($col, $currentSort, $currentDir) {
            if ($currentSort !== $col) return 'fas fa-sort text-muted';
            return $currentDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }
        // Helper: build the URL preserving existing filters
        function sortUrl($col) {
            $params = request()->all();
            $params['sort'] = $col;
            $params['direction'] = nextDir($col, request('sort','id'), request('direction','desc'));
            return request()->fullUrlWithQuery($params);
        }
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
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Clients List</h5>
                    <form method="GET" action="{{ route('clients.index') }}" class="d-flex">
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-sm me-2" placeholder="Search">

                        <select name="field" class="form-select form-select-sm me-2" style="width:150px;">
                            <option value="all" {{ request('field') == 'all' ? 'selected' : '' }}>All Fields</option>
                            <option value="id" {{ request('field') == 'id' ? 'selected' : '' }}>Client ID</option>
                            <option value="name" {{ request('field') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="contact_person" {{ request('field') == 'contact_person' ? 'selected' : '' }}>Contact Person</option>
                            <option value="contact_no" {{ request('field') == 'contact_no' ? 'selected' : '' }}>Mobile</option>
                            <option value="city" {{ request('field') == 'city' ? 'selected' : '' }}>City</option>
                            <option value="email" {{ request('field') == 'email' ? 'selected' : '' }}>Email</option>
                        </select>

                        {{-- Preserve current sort in the search form so it stays after searching --}}
                        <input type="hidden" name="sort" value="{{ request('sort','id') }}">
                        <input type="hidden" name="direction" value="{{ request('direction','desc') }}">

                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <a href="{{ sortUrl('id') }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
                                            Client ID
                                            <i class="{{ sortIcon('id', request('sort','id'), request('direction','desc')) }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrl('name') }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
                                            Name
                                            <i class="{{ sortIcon('name', request('sort','id'), request('direction','desc')) }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrl('contact_person') }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
                                            Contact Person
                                            <i class="{{ sortIcon('contact_person', request('sort','id'), request('direction','desc')) }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrl('contact_no') }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
                                            Mobile
                                            <i class="{{ sortIcon('contact_no', request('sort','id'), request('direction','desc')) }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrl('city') }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
                                            City
                                            <i class="{{ sortIcon('city', request('sort','id'), request('direction','desc')) }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrl('created_at') }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
                                            Joined On
                                            <i class="{{ sortIcon('created_at', request('sort','id'), request('direction','desc')) }}"></i>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clients as $key => $client)
                                    <tr onclick="window.location='?{{ http_build_query(array_merge(request()->except('client_id'), ['client_id' => $client->id])) }}'" style="cursor: pointer;">
                                        <td><span class="badge bg-secondary">{{ $client->id }}</span></td>
                                        <td>{{ $client->name }}</td>
                                        <td>{{ $client->contact_person }}</td>
                                        <td>{{ $client->contact_no }}</td>
                                        <td>{{ $client->city }}</td>
                                        <td>{{ $client->created_at ? $client->created_at->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                @endforeach
                                @if($clients->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No clients found.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Subscriber Details -->
        <div class="col-md-3">
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
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" disabled>
                                <option value="Paid" {{ old('type', $selectedClient->type ?? '')=='Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Free" {{ old('type', $selectedClient->type ?? '')=='Free' ? 'selected' : '' }}>Free</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $selectedClient->contact_person ?? '') }}" readonly>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $selectedClient->contact_no ?? '') }}" readonly>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $selectedClient->email ?? '') }}" readonly>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Billing Address</label>
                            <textarea name="address" class="form-control" rows="2" readonly>{{ old('address', $selectedClient->address ?? '') }}</textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $selectedClient->city ?? '') }}" readonly>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIN</label>
                                <input type="text" name="pin" class="form-control" value="{{ old('pin', $selectedClient->pin ?? '') }}" readonly>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GST No</label>
                                <input type="text" name="gst_no" class="form-control" value="{{ old('gst_no', $selectedClient->gst_no ?? '') }}" readonly>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $selectedClient->state ?? '') }}" readonly>
                            <div class="invalid-feedback"></div>
                        </div>

                        <hr>
                        <h6 class="text-muted mb-2">Boxes</h6>
                        <div class="inventory-panel">
                            <div class="inventory-scroll table-responsive">
                                <table class="table table-sm table-bordered inventory-table mb-0" id="inventoryTable">
                                    <thead class="table-light inventory-head">
                                        <tr>
                                            <th style="width:72px;">
                                                <button type="button" class="btn btn-link p-0 sort-inv" data-col="0">ID <i class="fas fa-sort text-muted"></i></button>
                                            </th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 sort-inv" data-col="1">IP <i class="fas fa-sort text-muted"></i></button>
                                            </th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 sort-inv" data-col="3">LOCATION <i class="fas fa-sort text-muted"></i></button>
                                            </th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 sort-inv" data-col="2">MAC <i class="fas fa-sort text-muted"></i></button>
                                            </th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 sort-inv" data-col="4">PACKAGE <i class="fas fa-sort text-muted"></i></button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $inventories = $selectedClient
                                                ? $selectedClient->inventories()->orderBy('box_id', 'asc')->get()
                                                : collect();
                                        @endphp

                                        @forelse($inventories as $inv)
                                            <tr onclick="window.location='{{ route('inventories.show', $inv->id) }}'" style="cursor:pointer;">
                                                <td>{{ $inv->box_id }}</td>
                                                <td class="text-monospace">{{ $inv->box_ip }}</td>
                                                <td>{{ $inv->location ?? '-' }}</td>
                                                <td class="text-monospace">{{ $inv->box_mac }}</td>
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
    .inventory-head th { position: sticky; top: 0; z-index: 1; background:#f8f9fa; }
    .text-monospace { font-family: monospace; }
    .inventory-table thead button { font-weight: 600; color: inherit; }
    .inventory-table thead i { line-height: 1; }
    /* Keep header links subtle */
    .table thead a { font-weight:600; }

    /* Safety: when body gets .no-loader, force-hide common overlays (doesn't affect valid flow) */
    body.no-loader #loader,
    body.no-loader #global-loader,
    body.no-loader .page-loader,
    body.no-loader .loading-overlay,
    body.no-loader .preloader,
    body.no-loader .ajax-loader,
    body.no-loader .blockUI,
    body.no-loader .blockOverlay,
    body.no-loader .blockMsg { display:none !important; opacity:0 !important; visibility:hidden !important; }
</style>

{{-- jQuery + jQuery Validate (uses public CDNs; remove if already loaded globally) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3.../Y=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js" integrity="sha384-2e..." crossorigin="anonymous"></script>

<script>
function enableForm(mode) {
    let form = document.getElementById('clientForm');
    let inputs = form.querySelectorAll('input, select, textarea');
    let saveBtn = document.getElementById('saveBtn');

    // Reset validation UI when switching modes
    if (window.clientValidator) {
        window.clientValidator.resetForm();
    }
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

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
        let methodInput = form.querySelector('input[name="_method"]');
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

/* Lightweight client-side sort for the Inventory table (right panel) */
(function(){
    const table = document.getElementById('inventoryTable');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const state = { col: null, dir: 'asc' }; // asc | desc

    function compare(a, b, numeric) {
        if (numeric) {
            const na = parseFloat(a.replace(/[^\d.\-]/g,'')) || 0;
            const nb = parseFloat(b.replace(/[^\d.\-]/g,'')) || 0;
            return na - nb;
        }
        return a.localeCompare(b, undefined, { sensitivity: 'base' });
    }

    function setIcons(colIdx, dir) {
        table.querySelectorAll('thead .sort-inv i').forEach(i => i.className = 'fas fa-sort text-muted');
        const btn = table.querySelector('thead .sort-inv[data-col="'+colIdx+'"] i');
        if (!btn) return;
        btn.className = dir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }

    table.querySelectorAll('thead .sort-inv').forEach(btn => {
        btn.addEventListener('click', function(e){
            e.preventDefault();
            const colIdx = parseInt(this.getAttribute('data-col'),10);
            const numeric = colIdx === 0; // ID column numeric
            state.dir = (state.col === colIdx && state.dir === 'asc') ? 'desc' : 'asc';
            state.col = colIdx;

            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelectorAll('td').length);
            rows.sort((r1, r2) => {
                const t1 = r1.children[colIdx].innerText.trim();
                const t2 = r2.children[colIdx].innerText.trim();
                let res = compare(t1, t2, numeric);
                return state.dir === 'asc' ? res : -res;
            });
            rows.forEach(r => tbody.appendChild(r));
            setIcons(colIdx, state.dir);
        });
    });
})();

/* -------- Utilities to ensure loader does NOT show on invalid submit -------- */
function haltLoaderOnInvalid() {
    // Hide common global loader APIs if present
    if (typeof window.hideLoader === 'function') { try { window.hideLoader(); } catch(e){} }
    if (typeof window.hideGlobalLoader === 'function') { try { window.hideGlobalLoader(); } catch(e){} }
    if (typeof window.App !== 'undefined' && typeof window.App.hideLoader === 'function') { try { window.App.hideLoader(); } catch(e){} }
    if (typeof window.$ !== 'undefined' && typeof $.unblockUI === 'function') { try { $.unblockUI(); } catch(e){} }

    // Force-hide typical DOM loaders
    document.body.classList.add('no-loader');
    document.body.classList.remove('loading','is-loading');

    ['#loader','#global-loader'].forEach(sel => { const n = document.querySelector(sel); if (n) n.style.display='none'; });
    const overlays = document.querySelectorAll('.page-loader, .loading-overlay, .preloader, .ajax-loader, .blockUI, .blockOverlay, .blockMsg');
    overlays.forEach(el => el.style.display='none');
}

/* -------- jQuery Validation for clientForm (Bootstrap-friendly) -------- */
(function($){
    // Custom validators
    $.validator.addMethod("indianMobile", function(value, element) {
        if (this.optional(element)) return true;
        const cleaned = value.replace(/\D/g,'');
        return /^[6-9]\d{9}$/.test(cleaned);
    }, "Enter a valid 10-digit mobile starting with 6-9.");

    $.validator.addMethod("pincodeIN", function(value, element) {
        if (this.optional(element)) return true;
        const cleaned = value.replace(/\D/g,'');
        return /^\d{6}$/.test(cleaned);
    }, "Enter a valid 6-digit PIN code.");

    $.validator.addMethod("gstin", function(value, element) {
        if (this.optional(element)) return true;
        return /^[0-9]{2}[A-Z0-9]{10}[0-9A-Z]{3}$/i.test(value.trim());
    }, "Enter a valid GSTIN (15 characters).");

    $(function(){
        const form = $("#clientForm");

        // 1) Intercept Save button click first—if invalid, stop and kill loader.
        document.getElementById('saveBtn')?.addEventListener('click', function(e){
            if (!window.clientValidator) return;
            if (!form.valid()) {
                e.preventDefault();
                e.stopImmediatePropagation();
                haltLoaderOnInvalid();
                return false;
            }
        }, true); // capture phase beats any global loader show

        // 2) Guard form submit as well (fallback)
        form.on('submit', function(e){
            if (!window.clientValidator) return;
            if (!form.valid()) {
                e.preventDefault();
                e.stopImmediatePropagation();
                haltLoaderOnInvalid();
                return false;
            }
        });

        window.clientValidator = form.validate({
            ignore: ":hidden, [disabled], [readonly]",
            errorClass: "is-invalid",
            validClass: "is-valid",
            errorElement: "div",
            errorPlacement: function(error, element) {
                let fb = element.closest('.mb-3, .col-md-6').find('.invalid-feedback').first();
                if (fb.length) {
                    fb.empty().append(error);
                } else {
                    error.addClass('invalid-feedback');
                    element.after(error);
                }
            },
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
                let fb = $(element).closest('.mb-3, .col-md-6').find('.invalid-feedback').first();
                if (fb.length) fb.text('');
            },

            rules: {
                name: { required: true, minlength: 2, maxlength: 150 },
                type: { required: true },
                contact_no: { required: true, indianMobile: true },
                email: { email: true, maxlength: 190 },
                pin: { required: true, pincodeIN: true },
            },

            messages: {
                name: { required: "Please enter the name." },
                type: { required: "Please select a subscription type." },
                contact_no: { required: "Please enter the mobile number." },
                email: { email: "Please enter a valid email address." },
                pin: { required: "Please enter the PIN code." }
            },

            // 3) When invalid, hide any loader that might have been triggered globally
            invalidHandler: function(event, validator) {
                if (!validator.numberOfInvalids()) return;
                haltLoaderOnInvalid();
                const first = $(validator.errorList[0].element);
                $('html, body').animate({ scrollTop: first.offset().top - 120 }, 300);
            }
        });

        // Validate on interaction for enabled fields
        form.on('change blur keyup', 'input, select, textarea', function(){
            if (!this.disabled && !this.readOnly) {
                $(this).valid();
            }
        });

        // Safety: if the page renders with errors, ensure no loader is visible
        haltLoaderOnInvalid();
    });
})(jQuery);
</script>
@endsection
