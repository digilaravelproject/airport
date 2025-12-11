@extends('layouts.app')
@section('content')
<style>
    #adbProgressModal.modal { z-index: 4000; }
    .modal-backdrop.adb-backdrop { z-index: 3990 !important; }
    .table thead a { font-weight: 600; }
    /* OR — explicitly target the last TD of each TR (more explicit) */
    .table tbody tr td:last-child {
        width: 20% !important;
    }
    /* Search form small tweaks to align with other inventory page look */
    .search-form .form-control-sm, .search-form .form-select-sm { height: calc(1.5em + .5rem + 2px); }
</style>

@php
    // Helpers for DataTables-like sort arrows
    function nextDirIPA($col) {
        $currentSort = request('sort','id');
        $currentDir  = request('direction','desc');
        if ($currentSort === $col) return $currentDir === 'asc' ? 'desc' : 'asc';
        return 'asc';
    }
    function sortIconIPA($col) {
        $currentSort = request('sort','id');
        $currentDir  = request('direction','desc');
        if ($currentSort !== $col) return 'fas fa-sort text-muted';
        return $currentDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }
    function sortUrlIPA($col) {
        $params = request()->all();
        $params['sort'] = $col;
        $params['direction'] = nextDirIPA($col);
        return request()->fullUrlWithQuery($params);
    }
@endphp

<div class="container-fluid">
    <?php
        $page_title = "Inventory Packages";
        $sub_title  = "Allocations";
    ?>
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

    <!-- Inventory Packages List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- Header with Search form (keeps original header text + new search UI) -->
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inventory Packages List</h5>

                    <!-- Search form (keeps sort/direction when submitting) -->
                    @php
                        // Reset URL: preserve sort/direction but drop search/field/page
                        $preserve = request()->except(['search','field','page']);
                        $resetUrl = url()->current() . (count($preserve) ? '?' . http_build_query($preserve) : '');
                    @endphp

                    <div class="d-flex align-items-center" style="gap:.5rem;">
                        <form method="GET" action="{{ url()->current() }}" class="d-flex search-form" style="gap:.5rem;">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search">

                            {{-- Preserve current sort in the search form --}}
                            <input type="hidden" name="sort" value="{{ request('sort','id') }}">
                            <input type="hidden" name="direction" value="{{ request('direction','desc') }}">

                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="{{ $resetUrl }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </form>

                        <!-- Bulk assign button -->
                        <button id="bulkAssignBtn" class="btn btn-sm btn-success" disabled>
                            <i class="las la-layer-group me-1"></i> Assign to selected
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        @php use Illuminate\Support\Facades\Storage; @endphp
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;">
                                        <input type="checkbox" id="select_all_rows" title="Select all">
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlIPA('box_id') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Box ID <i class="{{ sortIconIPA('box_id') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlIPA('box_model') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Box Model <i class="{{ sortIconIPA('box_model') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlIPA('box_serial_no') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Serial No <i class="{{ sortIconIPA('box_serial_no') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlIPA('box_mac') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Mac ID <i class="{{ sortIconIPA('box_mac') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlIPA('box_ip') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Box IP <i class="{{ sortIconIPA('box_ip') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlIPA('location') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Location <i class="{{ sortIconIPA('location') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlIPA('client_name') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Client Name <i class="{{ sortIconIPA('client_name') }}"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ sortUrlIPA('packages') }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1">
                                            Allocated Packages <i class="{{ sortIconIPA('packages') }}"></i>
                                        </a>
                                    </th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventories as $key => $inventory)
                                    @php
                                        $jsonFile = base_path($inventory->box_id . '.json');
                                        $jsonUrl = file_exists($jsonFile) ? url($inventory->box_id . '.json') : null;
                                        $invPayload = $inventory->toArray();
                                        $invPayload['json_url'] = $jsonUrl;
                                    @endphp
                                    <tr>
                                        <td class="align-middle text-center">
                                            <input type="checkbox"
                                                   class="row-select"
                                                   data-id="{{ $inventory->id }}"
                                                   data-boxid="{{ $inventory->box_id }}"
                                                   data-client="{{ $inventory->client->name ?? '' }}">
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $inventory->box_id }}</span></td>
                                        <td>{{ $inventory->box_model }}</td>
                                        <td>{{ $inventory->box_serial_no }}</td>
                                        <td>{{ $inventory->box_mac }}</td>
                                        <td>{{ $inventory->box_ip }}</td>
                                        <td>{{ $inventory->location ?? '-' }}</td>
                                        <td>{{ $inventory->client->name ?? '-' }}</td>
                                        <td>
                                            @if($inventory->packages->count())
                                                {{ $inventory->packages->pluck('name')->join(', ') }}
                                            @else
                                                <span class="text-muted">No packages</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button
                                                class="btn btn-sm btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#inventoryPackageModal"
                                                onclick='openForm("edit", {!! json_encode($invPayload, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) !!})'
                                            >
                                                <i class="las la-pen"></i> Edit
                                            </button>
                                            <button
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#inventoryPackageModal"
                                                onclick='openForm("view", {!! json_encode($invPayload, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) !!})'
                                            >
                                                <i class="las la-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($inventories->isEmpty())
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No inventories found.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $inventories->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal + scripts remain unchanged (same as your original code) but extended to support bulk mode --}}
<!-- Inventory Package Modal -->
<div class="modal fade" id="inventoryPackageModal" tabindex="-1" aria-labelledby="inventoryPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="inventoryPackageForm">
                @csrf
                <div id="formMethod"></div>

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="inventoryPackageModalLabel">Assign Packages</h5>
                    <a id="viewJsonLink" href="#" target="_blank" class="btn btn-outline-light btn-sm ms-2" style="display: none;">
                        <i class="las la-file-code me-1"></i> View JSON
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- Single inventory id (kept for legacy single-edit) --}}
                    <input type="hidden" id="inventory_id" class="form-control" />

                    {{-- For bulk mode we will inject inputs named inventory_ids[] dynamically into the form --}}
                    <div id="bulkInventoryInputs"></div>

                    <div class="mb-3">
                        <label class="form-label">Box ID</label>
                        <input type="text" id="box_id" class="form-control" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Box IP</label>
                        <input type="text" id="box_ip" class="form-control" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Box Model</label>
                        <input type="text" id="inventory_box_model" class="form-control" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Serial No</label>
                        <input type="text" id="inventory_serial" class="form-control" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <input type="text" id="inventory_client" class="form-control" readonly />
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Packages</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select_all_packages" disabled />
                                <label class="form-check-label" for="select_all_packages">Select All</label>
                            </div>
                        </div>

                        <div id="packagesWrapper" class="border rounded p-2 mt-2" style="max-height: 200px; overflow-y: auto;">
                            @foreach($packages as $pkg)
                            <div class="form-check">
                                <input class="form-check-input pkg-checkbox" type="checkbox" name="package_ids[]" value="{{ $pkg->id }}" id="pkg_{{ $pkg->id }}" data-name="{{ $pkg->name }}" disabled />
                                <label class="form-check-label" for="pkg_{{ $pkg->id }}">
                                    {{ $pkg->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>

                        <small class="text-muted d-block mt-1">
                            <span id="selectedCount">0</span> / <span id="totalCount">{{ count($packages) }}</span> selected
                        </small>

                        <div class="mt-2">
                            <span class="small text-muted me-2">Selected package:</span>
                            <span id="selectedPackageName" class="badge bg-secondary">None</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" id="saveBtn" class="btn btn-dark px-4" style="display: none;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADB Progress Modal -->
<div class="modal fade" id="adbProgressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="las la-terminal me-1"></i> Device Reboot Progress</h5>
            </div>
            <div class="modal-body">
                <div id="adbMessages" class="small" style="max-height: 250px; overflow-y: auto;">
                    <div class="text-muted">Starting process...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-dark" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    /* === your original JS (openForm, hideAnyLoader, showAdbProgress, showAdbResult, form submission) unchanged but extended === */
    function openForm(mode, data = null) {
        const modalTitle = document.getElementById("inventoryPackageModalLabel");
        const form = document.getElementById("inventoryPackageForm");
        const saveBtn = document.getElementById("saveBtn");
        const methodDiv = document.getElementById("formMethod");
        const viewJsonLink = document.getElementById("viewJsonLink");
        const selectAll = document.getElementById("select_all_packages");
        const selectedCountEl = document.getElementById("selectedCount");
        const totalCountEl = document.getElementById("totalCount");
        const selectedNameEl = document.getElementById("selectedPackageName");
        const bulkInventoryInputs = document.getElementById("bulkInventoryInputs");
        const pkgCheckboxes = () => Array.from(document.querySelectorAll("#packagesWrapper .pkg-checkbox"));

        function enforceSingleSelection(changedCb) {
            const boxes = pkgCheckboxes();
            if (changedCb && changedCb.checked) {
                boxes.forEach((cb) => { if (cb !== changedCb) cb.disabled = true; });
            } else {
                boxes.forEach((cb) => { cb.disabled = false; });
            }
        }
        function currentSelected() { return pkgCheckboxes().find((cb) => cb.checked) || null; }
        function updateSelectedName() {
            const cb = currentSelected();
            selectedNameEl.textContent = cb ? cb.dataset.name || "Selected" : "None";
            selectedNameEl.className = "badge " + (cb ? "bg-primary" : "bg-secondary");
        }
        function updateCounts() {
            const boxes = pkgCheckboxes();
            const total = boxes.length;
            const checked = boxes.filter((cb) => cb.checked).length;
            selectedCountEl.textContent = checked;
            totalCountEl.textContent = total;
            selectAll.indeterminate = false;
            selectAll.checked = false;
            updateSelectedName();
        }

        // Reset
        form.reset();
        bulkInventoryInputs.innerHTML = "";
        document.getElementById("inventory_id").value = "";
        document.getElementById("box_id").value = "";
        document.getElementById("box_ip").value = "";
        document.getElementById("inventory_box_model").value = "";
        document.getElementById("inventory_serial").value = "";
        document.getElementById("inventory_client").value = "";
        pkgCheckboxes().forEach((cb) => { cb.checked = false; cb.disabled = true; });
        methodDiv.innerHTML = "";
        saveBtn.style.display = "none";
        selectAll.disabled = true;
        updateCounts();
        viewJsonLink.style.display = "none";
        viewJsonLink.href = "#";

        pkgCheckboxes().forEach((cb) => {
            cb.onchange = () => {
                if (cb.checked) {
                    pkgCheckboxes().forEach((other) => { if (other !== cb) other.checked = false; });
                }
                enforceSingleSelection(cb);
                updateCounts();
            };
        });

        // SINGLE EDIT (existing behavior)
        if (mode === "edit" && data) {
            modalTitle.innerText = "Assign Packages";
            form.action = "/inventory-packages/" + data.id + "/assign";
            saveBtn.style.display = "inline-block";

            document.getElementById("inventory_id").value = data.id;
            document.getElementById("box_id").value = data.box_id;
            document.getElementById("box_ip").value = data.box_ip;
            document.getElementById("inventory_box_model").value = data.box_model;
            document.getElementById("inventory_serial").value = data.box_serial_no;
            document.getElementById("inventory_client").value = data.client ? data.client.id + " - " + data.client.name : "No client";
            pkgCheckboxes().forEach((cb) => (cb.disabled = false));

            let preselectId = null;
            if (Array.isArray(data.packages) && data.packages.length > 0) {
                preselectId = String(data.packages[0].id);
            }
            pkgCheckboxes().forEach((cb) => { cb.checked = preselectId !== null && cb.value === preselectId; });

            enforceSingleSelection(currentSelected());
            updateCounts();
            viewJsonLink.style.display = "none";
        }

        // VIEW mode (existing)
        if (mode === "view" && data) {
            modalTitle.innerText = "View Inventory Packages";
            document.getElementById("inventory_id").value = data.id;
            document.getElementById("box_id").value = data.box_id;
            document.getElementById("box_ip").value = data.box_ip;
            document.getElementById("inventory_box_model").value = data.box_model;
            document.getElementById("inventory_serial").value = data.box_serial_no;
            document.getElementById("inventory_client").value = data.client ? data.client.id + " - " + data.client.name : "No client";

            let selectedId = null;
            if (Array.isArray(data.packages) && data.packages.length > 0) {
                selectedId = String(data.packages[0].id);
            }
            pkgCheckboxes().forEach((cb) => { cb.checked = selectedId !== null && cb.value === selectedId; cb.disabled = true; });

            enforceSingleSelection(currentSelected());
            updateCounts();

            if (data.json_url) {
                viewJsonLink.href = data.json_url;
                viewJsonLink.style.display = "inline-block";
            } else {
                viewJsonLink.style.display = "none";
            }

            saveBtn.style.display = "none";
        }

        // BULK mode: data expected { ids: [...], boxIds: [...] }
        if ((mode === "bulk" || mode === "edit_bulk") && data) {
            const ids = Array.isArray(data.ids) ? data.ids : [];
            const boxIds = Array.isArray(data.boxIds) ? data.boxIds : [];

            modalTitle.innerText = `Assign Packages to ${ids.length} inventories`;
            form.action = "/inventory-packages/assign-multiple";
            saveBtn.style.display = "inline-block";

            // show aggregated info
            document.getElementById("inventory_id").value = "";
            document.getElementById("box_id").value = `Multiple selected (${boxIds.length})`;
            document.getElementById("box_ip").value = "-";
            document.getElementById("inventory_box_model").value = "-";
            document.getElementById("inventory_serial").value = "-";
            document.getElementById("inventory_client").value = "-";

            // inject hidden inputs inventory_ids[]
            bulkInventoryInputs.innerHTML = "";
            ids.forEach((iid) => {
                const inp = document.createElement("input");
                inp.type = "hidden";
                inp.name = "inventory_ids[]";
                inp.value = iid;
                bulkInventoryInputs.appendChild(inp);
            });

            pkgCheckboxes().forEach((cb) => (cb.disabled = false));
            enforceSingleSelection(currentSelected());
            updateCounts();
            viewJsonLink.style.display = "none";
        }
    }

    function hideAnyLoader() {
        const selectors = ["#globalLoader", "#preloader", "#loader", "#loading", ".loading", ".loading-overlay"];
        selectors.forEach((sel) => {
            const el = document.querySelector(sel);
            if (!el) return;
            el.classList.add("d-none");
            el.style.display = "none";
            el.style.visibility = "hidden";
        });
    }

    (function wireAdbModalZIndex() {
        const modalEl = document.getElementById("adbProgressModal");
        if (!modalEl) return;
        modalEl.addEventListener("shown.bs.modal", () => {
            document.querySelectorAll(".modal-backdrop").forEach((b) => b.classList.add("adb-backdrop"));
        });
    })();

    function showAdbProgress(messages) {
        hideAnyLoader();
        const modal = new bootstrap.Modal(document.getElementById("adbProgressModal"), { backdrop: true, keyboard: true });
        const msgBox = document.getElementById("adbMessages");
        msgBox.innerHTML = "";
        modal.show();
        let i = 0;
        function nextMsg() {
            if (i < messages.length) {
                const line = document.createElement("div");
                line.textContent = messages[i];
                msgBox.appendChild(line);
                msgBox.scrollTop = msgBox.scrollHeight;
                i++;
                setTimeout(nextMsg, 800);
            }
        }
        nextMsg();
    }

    /* === NEW: wrapper that shows success/error popup and reloads after sequence === */
    function showAdbResult(messages, isSuccess) {
        const list = (Array.isArray(messages) && messages.length) ? messages : [isSuccess ? "Process completed" : "Process failed"];
        showAdbProgress(list);

        // after all lines are printed (~800ms per line), append final status and reload
        const total = list.length * 800 + 200;
        setTimeout(() => {
            const msgBox = document.getElementById("adbMessages");
            const final = document.createElement("div");
            final.textContent = isSuccess ? "✅ Completed." : "❌ Failed.";
            msgBox.appendChild(final);
            msgBox.scrollTop = msgBox.scrollHeight;
        }, total);

        setTimeout(() => {
            window.location.reload();
        }, total + 800);
    }
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("inventoryPackageForm");
  if (!form) return;

  // Bulk selection controls
  const selectAllRows = document.getElementById("select_all_rows");
  const rowSelects = () => Array.from(document.querySelectorAll(".row-select"));
  const bulkBtn = document.getElementById("bulkAssignBtn");

  function updateBulkBtnState() {
      const selected = rowSelects().filter(ch => ch.checked);
      bulkBtn.disabled = selected.length === 0;
  }

  // individual row checkbox change
  document.addEventListener("change", function(e) {
      if (e.target && e.target.matches(".row-select")) {
          updateBulkBtnState();
          // update select_all_rows indeterminate/checked
          const rows = rowSelects();
          const selected = rows.filter(r => r.checked).length;
          if (selected === 0) {
              selectAllRows.checked = false;
              selectAllRows.indeterminate = false;
          } else if (selected === rows.length) {
              selectAllRows.checked = true;
              selectAllRows.indeterminate = false;
          } else {
              selectAllRows.checked = false;
              selectAllRows.indeterminate = true;
          }
      }
  });

  // header select all
  if (selectAllRows) {
      selectAllRows.addEventListener("change", function() {
          const v = this.checked;
          rowSelects().forEach(r => r.checked = v);
          updateBulkBtnState();
      });
  }

  // Bulk assign button click -> open modal in bulk mode
  if (bulkBtn) {
      bulkBtn.addEventListener("click", function() {
          const selected = rowSelects().filter(ch => ch.checked);
          const ids = selected.map(s => s.dataset.id);
          const boxIds = selected.map(s => s.dataset.boxid);
          // call existing openForm with new mode 'bulk'
          openForm("bulk", { ids: ids, boxIds: boxIds });
          // show modal explicitly (if button not using data-bs-toggle)
          const mb = new bootstrap.Modal(document.getElementById("inventoryPackageModal"));
          mb.show();
      });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    const action = form.action;

    // fallback reload guard - server-driven flow will show the ADB modal + reload
    setTimeout(() => {
        location.reload();
    }, 10000);

    fetch(action, {
      method: "POST",
      body: formData,
      headers: {
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": formData.get("_token") || "",
      },
      credentials: "same-origin",
    })
      .then(async (res) => {
        const contentType = res.headers.get("content-type") || "";
        const raw = await res.text(); // read once

        if (!res.ok) {
          // Propagate server message
          throw new Error(`HTTP ${res.status}: ${raw.slice(0, 800)}`);
        }

        if (!raw.trim()) {
          throw new Error("Empty response (expected JSON).");
        }

        if (contentType.includes("application/json") || /^[\[{]/.test(raw.trim())) {
          try {
            return JSON.parse(raw);
          } catch (e) {
            throw new Error("JSON parse failed: " + raw.slice(0, 800));
          }
        } else {
          throw new Error("Expected JSON, got: " + raw.slice(0, 800));
        }
      })
      .then((data) => {
        hideAnyLoader?.();
        if (data.success) {
          // SUCCESS POPUP + AUTO RELOAD
          const msgs = Array.isArray(data.messages) ? data.messages : ["Process completed"];
          showAdbResult(msgs, true);
        } else {
          // ERROR POPUP + AUTO RELOAD
          const msgs = [];
          if (data.messages && Array.isArray(data.messages)) msgs.push(...data.messages);
          if (data.message) msgs.push(String(data.message));
          if (!msgs.length) msgs.push("Failed to assign packages.");
          showAdbResult(msgs, false);
        }
      })
      .catch((err) => {
        hideAnyLoader?.();
        // NETWORK/UNEXPECTED ERROR POPUP + AUTO RELOAD
        showAdbResult([ "Error: " + (err.message || "Unknown error") ], false);
      });
  });
});
</script>
@endsection
