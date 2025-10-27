@extends('layouts.app')

@section('content')
<style>
    .import-header { background-color: #0f172a !important; }
    .summary-card { cursor:pointer; transition: transform .05s ease-in; }
    .summary-card:hover { transform: translateY(-1px); }
    .summary-active { border-color:#0d6efd !important; box-shadow: 0 0 0 .1rem rgba(13,110,253,.15); }
</style>
<div class="container-fluid">
    <?php $page_title = "Inventories"; $sub_title = "Setup Boxes"; ?>

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

    <!-- Inventory Import Section -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header import-header text-white d-flex justify-content-between align-items-center">
                    <h6 class="text-light">
                        <i class="fas fa-file-import me-2"></i>Import Inventory Data
                    </h6>
                    <small class="text-light">Upload Excel (.xlsx, .xls, .csv)</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventories.import') }}" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <div class="row align-items-end g-3">
                            <div class="col-md-6">
                                <label for="file" class="form-label fw-semibold">Select File</label>
                                <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-upload me-1"></i> Import
                                </button>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="{{ asset('sample/Inventory_Import_Format.xlsx') }}"
                                   class="btn btn-outline-secondary w-100" download>
                                    <i class="fas fa-download me-1"></i> Sample File
                                </a>
                            </div>
                        </div>
                    </form>

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

                </div>
            </div>
        </div>
    </div>

    <!-- Summary Tabs -->
    @php $assign = request('assign','all'); @endphp
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <a class="text-decoration-none"
               href="{{ route('inventories.index', array_merge(request()->except('page'), ['assign'=>'all'])) }}">
                <div class="card border summary-card {{ $assign==='all' ? 'summary-active' : '' }}">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total Boxes</div>
                            <div class="h5 mb-0">{{ $totalBoxes }}</div>
                        </div>
                        <span class="badge bg-secondary">All</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a class="text-decoration-none"
               href="{{ route('inventories.index', array_merge(request()->except('page'), ['assign'=>'assigned'])) }}">
                <div class="card border summary-card {{ $assign==='assigned' ? 'summary-active' : '' }}">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Assigned Boxes</div>
                            <div class="h5 mb-0">{{ $assignedBoxes }}</div>
                        </div>
                        <span class="badge bg-success">Assigned</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a class="text-decoration-none"
               href="{{ route('inventories.index', array_merge(request()->except('page'), ['assign'=>'unassigned'])) }}">
                <div class="card border summary-card {{ $assign==='unassigned' ? 'summary-active' : '' }}">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Non-assigned Boxes</div>
                            <div class="h5 mb-0">{{ $unassignedBoxes }}</div>
                        </div>
                        <span class="badge bg-warning text-dark">None</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left: Inventories Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inventories</h5>
                    <form method="GET" action="{{ route('inventories.index') }}" class="d-flex">
                        <input type="hidden" name="assign" value="{{ request('assign','all') }}">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm me-2" placeholder="Search">
                        <!-- Search field dropdown -->
                        <select name="field" class="form-select form-select-sm me-2" style="width:160px;">
                            <option value="all" {{ request('field','all')=='all' ? 'selected' : '' }}>All Fields</option>
                            <option value="box_id" {{ request('field')=='box_id' ? 'selected' : '' }}>Box ID</option>
                            <option value="box_ip" {{ request('field')=='box_ip' ? 'selected' : '' }}>Box IP</option>
                            <option value="box_model" {{ request('field')=='box_model' ? 'selected' : '' }}>Model</option>
                            <option value="box_serial_no" {{ request('field')=='box_serial_no' ? 'selected' : '' }}>Serial No</option>
                            <option value="box_mac" {{ request('field')=='box_mac' ? 'selected' : '' }}>MAC</option>
                            <option value="box_fw" {{ request('field')=='box_fw' ? 'selected' : '' }}>Firmware</option>
                            <option value="client_name" {{ request('field')=='client_name' ? 'selected' : '' }}>Client Name</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('inventories.index', ['assign'=>request('assign','all')]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Box ID</th>
                                    <th>Box IP</th>
                                    <th>Establishment</th>
                                    <th>MAC</th>
                                    <th>Client</th>
                                    <th>Model</th>
                                    <th>Serial No</th>
                                    <th>Firmware</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventories as $key => $inventory)
                                    <tr onclick="window.location='?inventory_id={{ $inventory->id }}'" style="cursor:pointer;">
                                        <td>{{ ($inventories->firstItem() ?? 1) + $key }}</td>
                                        <td><span class="badge bg-secondary">{{ $inventory->box_id }}</span></td>
                                        <td>{{ $inventory->box_ip }}</td>
                                        <td>{{ $inventory->location }}</td>
                                        <td>{{ $inventory->box_mac }}</td>
                                        <td>{{ $inventory->client?->name }}</td>
                                        <td>{{ $inventory->box_model }}</td>
                                        <td>{{ $inventory->box_serial_no }}</td>
                                        <td>{{ $inventory->box_fw }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $inventories->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Box Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Box Details</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" onclick="enableForm('add')">Add</button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="enableForm('edit')" {{ !$selectedInventory ? 'disabled' : '' }}>Edit</button>
                        <button type="button" class="btn btn-sm btn-info" onclick="enableForm('view')" {{ !$selectedInventory ? 'disabled' : '' }}>View</button>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" id="inventoryForm" enctype="multipart/form-data"
                          action="{{ $selectedInventory ? route('inventories.update',$selectedInventory->id) : route('inventories.store') }}">
                        @csrf
                        @if($selectedInventory) @method('PUT') @endif

                        <div class="mb-3">
                            <label class="form-label">Box ID</label>
                            <input type="text" name="box_id" class="form-control"
                                   value="{{ old('box_id', $selectedInventory->box_id ?? '') }}" readonly>
                            @error('box_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="box_model" class="form-control" value="{{ old('box_model', $selectedInventory->box_model ?? '') }}" readonly>
                            @error('box_model') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">MAC ID</label>
                            <input type="text" name="box_mac" class="form-control" value="{{ old('box_mac', $selectedInventory->box_mac ?? '') }}" readonly>
                            @error('box_mac') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Serial No</label>
                            <input type="text" name="box_serial_no" class="form-control" value="{{ old('box_serial_no', $selectedInventory->box_serial_no ?? '') }}" readonly>
                            @error('box_serial_no') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Firmware</label>
                            <input type="text" name="box_fw" class="form-control" value="{{ old('box_fw', $selectedInventory->box_fw ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">RCU Model</label>
                            <input type="text" name="box_remote_model" class="form-control" value="{{ old('box_remote_model', $selectedInventory->box_remote_model ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Warranty Date</label>
                            <input type="date" name="warranty_date" class="form-control"
                                   value="{{ old('warranty_date', isset($selectedInventory->warranty_date) ? $selectedInventory->warranty_date->format('Y-m-d') : '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Client</label>
                            <select name="client_id" class="form-select" disabled>
                                <option value="">-- Select --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id', $selectedInventory->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Establishment</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $selectedInventory->location ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Terminal</label>
                            <input type="text" name="terminal" class="form-control" value="{{ old('terminal', $selectedInventory->terminal ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Level</label>
                            <input type="text" name="level" class="form-control" value="{{ old('level', $selectedInventory->level ?? '') }}" readonly>
                        </div>

                        {{-- Management fields --}}
                        <div class="mb-3">
                            <label class="form-label">Management URL (API base)</label>
                            <input type="text" name="mgmt_url" class="form-control"
                                   placeholder="http://<HOST>:PORT/api/v2"
                                   value="{{ old('mgmt_url', $selectedInventory->mgmt_url ?? '') }}" readonly>
                            <small class="text-muted">Example: http://api.aminocom.com:8090/api/v2</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Management Token (optional)</label>
                            <input type="text" name="mgmt_token" class="form-control" value="{{ old('mgmt_token', $selectedInventory->mgmt_token ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Box IP (optional ICMP fallback)</label>
                            <input type="text" name="box_ip" class="form-control" placeholder="192.168.1.50"
                                   value="{{ old('box_ip', $selectedInventory->box_ip ?? '') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <div id="previewImage" class="mb-2">
                                @if(isset($selectedInventory->photo))
                                    <img src="{{ asset('storage/'.$selectedInventory->photo) }}" class="img-thumbnail" width="120">
                                @endif
                            </div>
                            <input type="file" name="photo" id="photoInput" class="form-control" {{ !$selectedInventory ? '' : 'disabled' }}>
                            @error('photo') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <!-- Actions -->
                        <div class="mb-2 d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-dark" id="btnPing" {{ !$selectedInventory ? 'disabled' : '' }}>Ping</button>
                            <button type="button" class="btn btn-dark" id="btnReboot" {{ !$selectedInventory ? 'disabled' : '' }}>Reboot</button>
                            <button type="button" class="btn btn-dark" id="btnScreenshot" {{ !$selectedInventory ? 'disabled' : '' }}>Screenshot</button>
                            <span id="actionStatus" class="ms-2 small text-muted"></span>
                        </div>
                        <div id="screenshotArea" class="mt-2"></div>

                        <div class="text-end">
                            <button type="submit" id="saveBtn" class="btn btn-dark px-4" style="display:none;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enable/disable modes
function enableForm(mode) {
    let form = document.getElementById('inventoryForm');
    let inputs = form.querySelectorAll('input, select');
    let saveBtn = document.getElementById('saveBtn');

    if (mode === 'add') {
        inputs.forEach(el => {
            if (el.name && el.type !== "hidden") { el.value = ''; el.readOnly = false; el.disabled = false; }
        });
        document.getElementById('previewImage').innerHTML = '';
        saveBtn.style.display = 'inline-block';
        form.action = "{{ route('inventories.store') }}";
        let methodInput = form.querySelector('input[name=\"_method\"]'); if (methodInput) methodInput.remove();
    }

    if (mode === 'edit') {
        inputs.forEach(el => {
            if (el.tagName.toLowerCase() === 'select' || el.type === "file") el.disabled = false;
            else if (el.type !== "hidden") el.readOnly = false;
        });
        saveBtn.style.display = 'inline-block';
    }

    if (mode === 'view') {
        inputs.forEach(el => {
            if (el.tagName.toLowerCase() === 'select' || el.type === "file") el.disabled = true;
            else if (el.type !== "hidden") el.readOnly = true;
        });
        saveBtn.style.display = 'none';
    }
}

// Image preview
document.getElementById('photoInput')?.addEventListener('change', function(event) {
    let preview = document.getElementById('previewImage');
    preview.innerHTML = '';
    let file = event.target.files[0];
    if (file) {
        let reader = new FileReader();
        reader.onload = function(e) {
            let img = document.createElement('img');
            img.src = e.target.result;
            img.classList.add('img-thumbnail');
            img.width = 120;
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});

// AJAX helpers
const actionStatus = document.getElementById('actionStatus');

async function postJSON(url) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    });
    let json = null; try { json = await res.json(); } catch {}
    if (!res.ok && json?.message) throw new Error(json.message);
    return json ?? {};
}

document.getElementById('btnPing')?.addEventListener('click', async () => {
    const pingBtn   = document.getElementById('btnPing');
    const rebootBtn = document.getElementById('btnReboot');
    pingBtn.disabled = true; rebootBtn.disabled = true;
    actionStatus.textContent = 'Pinging...'; actionStatus.className = 'ms-2 small text-muted';
    try {
        const data = await postJSON("{{ $selectedInventory ? route('inventories.ping', $selectedInventory->id) : '#' }}");
        if (data.success) {
            actionStatus.textContent = `Online (${data.method})`;
            actionStatus.className = 'ms-2 small text-success';
        } else {
            const extra = data.code ? ` (HTTP ${data.code})` : '';
            actionStatus.textContent = (data.message || 'Offline') + extra;
            console.warn('Ping details:', data.raw);
            actionStatus.className = 'ms-2 small text-danger';
        }
    } catch (e) {
        actionStatus.textContent = e.message || 'Ping failed';
        actionStatus.className = 'ms-2 small text-danger';
    } finally {
        pingBtn.disabled = false; rebootBtn.disabled = false;
    }
});

document.getElementById('btnReboot')?.addEventListener('click', async () => {
    if (!confirm('Send reboot command to this device?')) return;
    const pingBtn   = document.getElementById('btnPing');
    const rebootBtn = document.getElementById('btnReboot');
    pingBtn.disabled = true; rebootBtn.disabled = true;
    actionStatus.textContent = 'Rebooting...'; actionStatus.className = 'ms-2 small text-muted';
    try {
        const data = await postJSON("{{ $selectedInventory ? route('inventories.reboot', $selectedInventory->id) : '#' }}");
        if (data.success) {
            actionStatus.textContent = data.message || 'Reboot command sent.';
            actionStatus.className = 'ms-2 small text-warning';
        } else {
            const extra = data.code ? ` (HTTP ${data.code})` : '';
            actionStatus.textContent = (data.message || 'Reboot failed') + extra;
            console.warn('Reboot error details:', data.details || data.raw || data.error);
            actionStatus.className = 'ms-2 small text-danger';
        }
    } catch (e) {
        actionStatus.textContent = e.message || 'Reboot failed';
        actionStatus.className = 'ms-2 small text-danger';
    } finally {
        pingBtn.disabled = false; rebootBtn.disabled = false;
    }
});
</script>
<script>
document.getElementById('btnScreenshot')?.addEventListener('click', async () => {
    const pingBtn   = document.getElementById('btnPing');
    const rebootBtn = document.getElementById('btnReboot');
    const shotBtn   = document.getElementById('btnScreenshot');
    const preview   = document.getElementById('previewImage');
    const below     = document.getElementById('screenshotArea');

    pingBtn.disabled = true; rebootBtn.disabled = true; shotBtn.disabled = true;
    actionStatus.textContent = 'Capturing screenshot...';
    actionStatus.className = 'ms-2 small text-muted';

    try {
        const data = await postJSON("{{ $selectedInventory ? route('inventories.screenshot', $selectedInventory->id) : '#' }}");
        if (data.success && data.path) {
            if (preview) {
                preview.innerHTML = '';
                const img = document.createElement('img');
                img.src = data.path;
                img.className = 'img-thumbnail';
                img.width = 120;
                preview.appendChild(img);
            }
            if (below) {
                below.innerHTML = `
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-image me-2"></i>
                                <strong>Latest Screenshot</strong>
                            </div>
                            <img src="${data.path}" alt="Screenshot" class="img-fluid rounded">
                        </div>
                    </div>
                `;
            }
            actionStatus.textContent = data.message || 'Screenshot captured.';
            actionStatus.className = 'ms-2 small text-success';
        } else {
            const extra = data?.code ? ` (HTTP ${data.code})` : '';
            actionStatus.textContent = (data?.message || 'Screenshot failed') + extra;
            actionStatus.className = 'ms-2 small text-danger';
            console.warn('Screenshot error details:', data?.error || data?.raw);
        }
    } catch (e) {
        actionStatus.textContent = e.message || 'Screenshot failed';
        actionStatus.className = 'ms-2 small text-danger';
    } finally {
        pingBtn.disabled = false; rebootBtn.disabled = false; shotBtn.disabled = false;
    }
});
</script>
@endsection
