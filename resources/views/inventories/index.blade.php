@extends('layouts.app')

@section('content')
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

    <div class="row">
        <!-- Left: Inventories Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inventories</h5>
                    <form method="GET" action="{{ route('inventories.index') }}" class="d-flex">

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
                        <a href="{{ route('inventories.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                                    <th>Model</th>
                                    <th>Serial No</th>
                                    <th>MAC</th>
                                    <th>Firmware</th>
                                    <th>Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventories as $key => $inventory)
                                    <tr onclick="window.location='?inventory_id={{ $inventory->id }}'" style="cursor:pointer;">
                                        <td>{{ $key+1 }}</td>
                                        <td><span class="badge bg-secondary">{{ $inventory->box_id }}</span></td>
                                        <td>{{ $inventory->box_ip }}</td>
                                        <td>{{ $inventory->box_model }}</td>
                                        <td>{{ $inventory->box_serial_no }}</td>
                                        <td>{{ $inventory->box_mac }}</td>
                                        <td>{{ $inventory->box_fw }}</td>
                                        <td>{{ $inventory->client?->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- Add pagination if needed --}}
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
                            @error('box_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
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
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $selectedInventory->location ?? '') }}" readonly>
                        </div>

                        {{-- Management fields --}}
                        <div class="mb-3">
                            <label class="form-label">Management URL (API base)</label>
                            <input type="text" name="mgmt_url" class="form-control"
                                   placeholder="http://<HOST>:PORT/api/v2"
                                   value="{{ old('mgmt_url', $selectedInventory->mgmt_url ?? '') }}" readonly>
                            <small class="text-muted">Example: http://192.168.1.50:8090/api/v2</small>
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
                            <span id="actionStatus" class="ms-2 small text-muted"></span>
                        </div>

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
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
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
@endsection
