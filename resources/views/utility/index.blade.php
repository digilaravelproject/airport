
@extends('layouts.app')

@section('content')

<style>
    .import-header { background-color: #0f172a !important; }
    .summary-card { cursor:pointer; transition: transform .05s ease-in; }
    .summary-card:hover { transform: translateY(-1px); }
    .summary-active { border-color:#0d6efd !important; box-shadow: 0 0 0 .1rem rgba(13,110,253,.15); }
    .table thead a { font-weight:600; }

    /* preview area styling */
    #filePreviewArea { max-height:60vh; overflow:auto; }
    #filePreviewArea pre { white-space:pre-wrap; word-break:break-word; font-size:.9rem; }
    #filePreviewArea table { width:100%; border-collapse:collapse; }
    #filePreviewArea table th, #filePreviewArea table td { border:1px solid #ddd; padding:.35rem .5rem; text-align:left; font-size:.86rem; }

    /* backup link area */
    .backup-result { margin-top: .75rem; }
    .backup-file-link { word-break: break-all; display:block; }
</style>

<div class="container-fluid">
    <?php $page_title = "Utilities"; $sub_title = "Setop Boxes"; ?>

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
        <div class="alert alert-success alert-dismissible fade show mt-3 mb-2">
            <i class="fas fa-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mt-3 mb-2 alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <hr>
    <!-- Start Inventory Import Section -->
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
                                <label for="file_inventory" class="form-label fw-semibold">Select File</label>
                                <!-- id changed to file_inventory -->
                                <input type="file" name="file" id="file_inventory" accept=".xlsx,.xls,.csv,.txt,.json" class="form-control import-file-input" data-form-id="importForm" required>
                            </div>
                            <div class="col-md-3">
                                <!-- Import button removed per request -->
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="{{ asset('sample/Inventory_Import_Format.xlsx') }}"
                                   class="btn btn-outline-secondary w-100" download>
                                    <i class="fas fa-download me-1"></i> Sample File
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <!-- End Inventory Import Section -->

    <!-- Start Channel Import Section -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color:#0f172a;">
                    <h6 class="text-light mb-0">
                        <i class="fas fa-file-import me-2"></i>Import Channel Data
                    </h6>
                    <small class="text-light">Upload Excel (.xlsx, .xls, .csv)</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('channels.import') }}" enctype="multipart/form-data" id="channelImportForm">
                        @csrf
                        <div class="row align-items-end g-3">
                            <div class="col-md-6">
                                <label for="file_channel" class="form-label fw-semibold">Select File</label>
                                <!-- id changed to file_channel -->
                                <input type="file" name="file" id="file_channel" accept=".xlsx,.xls,.csv,.txt,.json" class="form-control import-file-input" data-form-id="channelImportForm" required>
                            </div>
                            <div class="col-md-3">
                                <!-- Import button removed -->
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="{{ asset('sample/Channel_Import_Format.xlsx') }}"
                                   class="btn btn-outline-secondary w-100" download>
                                    <i class="fas fa-download me-1"></i> Sample File
                                </a>
                            </div>
                        </div>
                    </form>

                    @if ($errors->has('file'))
                        <div class="text-danger small mt-2">{{ $errors->first('file') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <hr>
    <!-- End Channel Import Section -->

    <!-- Start Backup & Restore Section (ADDED) -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color:#0f172a;">
                    <h6 class="text-light mb-0">
                        <i class="fas fa-database me-2"></i>Backup & Restore Database
                    </h6>
                    <small class="text-light">Create DB backup or restore from a .sql file</small>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <!-- Backup button (runs the script) -->
                            <form method="POST" action="{{ route('utilities.backup') }}">
                                @csrf
                                <label class="form-label fw-semibold"></label>
                                <button type="submit" class="btn btn-primary w-100" id="backupBtn">
                                    <i class="fas fa-upload me-1"></i> Backup
                                </button>
                            </form>

                            <!-- Here we show the latest backup link (if available) -->
                            <div class="backup-result">
                                @if(session('backup_file'))
                                    @php $bf = session('backup_file'); @endphp
                                    <div class="small text-muted">Latest backup:</div>
                                    <a href="{{ $bf['url'] }}" class="btn btn-outline-success btn-sm backup-file-link mt-1" target="_blank" rel="noopener noreferrer" >
                                        <i class="fas fa-file-download me-1"></i> {{ $bf['name'] }}
                                    </a>
                                @elseif(isset($latestBackup) && $latestBackup)
                                    {{-- $latestBackup passed from controller --}}
                                    <div class="small text-muted">Latest backup:</div>
                                    <a href="{{ $latestBackup['url'] }}" class="btn btn-outline-success btn-sm backup-file-link mt-1" target="_blank" rel="noopener noreferrer" >
                                        <i class="fas fa-file-download me-1"></i> {{ $latestBackup['name'] }}
                                    </a>
                                @else
                                    <div class="small text-muted">No recent backup found (after triggering, check this area again).</div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-8">
                            <!-- Restore form -->
                            <form method="POST" action="{{ route('utilities.restore') }}" enctype="multipart/form-data" id="restoreForm">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label for="sql_file" class="form-label fw-semibold">Select .sql File</label>
                                        <input type="file" name="sql_file" id="sql_file" accept=".sql" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('This will DROP existing tables and import the uploaded SQL file. Are you sure?')">
                                            <i class="fas fa-download me-1"></i> Restore
                                        </button>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <small class="text-muted">select the latest SQL file from the backup location</small>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if ($errors->has('sql_file'))
                        <div class="text-danger small mt-2">{{ $errors->first('sql_file') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <hr>
    <!-- End Backup & Restore Section -->

</div>

<!-- Shared File Preview / Confirm Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filePreviewModalLabel">File ready to import</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">File selected:</p>
        <p class="small">
            <a href="#" id="previewFileLink" target="_blank" rel="noopener noreferrer" class="fw-semibold text-decoration-underline"></a>
        </p>
        <div class="text-muted small" id="fileInfo"></div>

        <hr>

        <div id="filePreviewArea" class="mt-2">
            <!-- Preview will be injected here (text or table) -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="cancelImportBtn" class="btn btn-outline-secondary">Cancel</button>
        <button type="button" id="confirmImportBtn" class="btn btn-success">Import</button>
      </div>
    </div>
  </div>
</div>

<!-- SheetJS (for xlsx preview) -->
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Keep state of selected file and the form to submit
    let currentFile = null;
    let currentForm = null;
    let currentBlobUrl = null;
    const modalEl = document.getElementById('filePreviewModal');
    const previewLink = document.getElementById('previewFileLink');
    const fileInfo = document.getElementById('fileInfo');
    const confirmBtn = document.getElementById('confirmImportBtn');
    const cancelBtn = document.getElementById('cancelImportBtn');
    const previewArea = document.getElementById('filePreviewArea');

    // bootstrap modal instance
    const bsModal = new bootstrap.Modal(modalEl);

    // helper: extension
    function extFromName(name) {
        if (!name) return '';
        const m = name.split('.').pop().toLowerCase();
        return m || '';
    }

    // attach change handlers to all file inputs with class .import-file-input
    document.querySelectorAll('.import-file-input').forEach(input => {
        input.addEventListener('change', async function (e) {
            const files = input.files;
            if (!files || files.length === 0) return;

            currentFile = files[0];
            const formId = input.dataset.formId || input.closest('form')?.id;
            currentForm = formId ? document.getElementById(formId) : input.closest('form');

            // revoke previous blob if any
            if (currentBlobUrl) {
                URL.revokeObjectURL(currentBlobUrl);
                currentBlobUrl = null;
            }

            // create a blob url for direct open (works for text/csv types)
            try {
                currentBlobUrl = URL.createObjectURL(currentFile);
            } catch (err) {
                currentBlobUrl = null;
            }

            // populate modal base info
            previewLink.textContent = currentFile.name;
            previewLink.href = currentBlobUrl || '#';
            fileInfo.textContent = [ currentFile.type || 'unknown type', Math.round(currentFile.size / 1024) + ' KB' ].join(' • ');

            // clear preview area
            previewArea.innerHTML = '';

            const ext = extFromName(currentFile.name);

            // handle by extension
            if (ext === 'csv' || ext === 'txt' || ext === 'json') {
                // read as text and show in modal + provide "Open in new tab" button
                const txt = await currentFile.text().catch(()=>null);
                if (txt !== null) {
                    const pre = document.createElement('pre');
                    pre.textContent = txt;
                    previewArea.appendChild(pre);

                    // ensure link opens in new tab; for text types browser usually opens inline
                    previewLink.textContent = currentFile.name + ' (Open in new tab)';
                    previewLink.href = currentBlobUrl;
                    previewLink.target = '_blank';
                } else {
                    // fallback
                    previewArea.innerHTML = '<div class="small text-muted">Preview not available. You can open the file in a new tab.</div>';
                }

            } else if (ext === 'xlsx' || ext === 'xls') {
                // parse using SheetJS and render first sheet as table
                try {
                    const arrayBuffer = await currentFile.arrayBuffer();
                    const workbook = XLSX.read(arrayBuffer, { type: 'array' });

                    // render all sheets (or only first)
                    const firstSheetName = workbook.SheetNames[0];
                    const sheet = workbook.Sheets[firstSheetName];
                    const html = XLSX.utils.sheet_to_html(sheet);

                    // sheet_to_html includes a <table> markup — inject into previewArea
                    previewArea.innerHTML = html;

                    // change preview link text: clicking will scroll to preview (not open)
                    previewLink.textContent = currentFile.name + ' (Preview below)';
                    previewLink.href = '#';
                    previewLink.removeAttribute('target');
                    previewLink.onclick = (ev) => {
                        ev.preventDefault();
                        // scroll modal content to preview area
                        previewArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    };
                } catch (err) {
                    console.error('SheetJS parse error', err);
                    previewArea.innerHTML = '<div class="small text-danger">Unable to preview spreadsheet in browser. You can download/open the file locally.</div>';
                    previewLink.textContent = currentFile.name;
                    previewLink.href = currentBlobUrl || '#';
                    previewLink.target = '_blank';
                }
            } else {
                // unknown binary — show helpful message and link to download/open
                previewArea.innerHTML = '<div class="small text-muted">Browser cannot preview this file type inline. Use the link above to open/download with your local app.</div>';
                previewLink.textContent = currentFile.name + ' (Open / Download)';
                previewLink.href = currentBlobUrl || '#';
                previewLink.target = '_blank';
            }

            // show the modal
            bsModal.show();
        });
    });

    // Cancel -> reload page to reset input state
    cancelBtn.addEventListener('click', function () {
        bsModal.hide();
        // slight delay to ensure modal closed, then reload
        setTimeout(() => { location.reload(); }, 150);
    });

    // Confirm -> submit the associated form
    confirmBtn.addEventListener('click', function () {
        if (!currentForm) {
            alert('Form not found.'); return;
        }
        bsModal.hide();
        setTimeout(() => { currentForm.submit(); }, 120);
    });

    // cleanup blob URL when modal closed
    modalEl.addEventListener('hidden.bs.modal', function () {
        if (currentBlobUrl) {
            URL.revokeObjectURL(currentBlobUrl);
            currentBlobUrl = null;
        }
        // reset preview link onclick behavior
        previewLink.onclick = null;
        previewLink.removeAttribute('target');
    });

    // defensive: if user navigates away revoke any blob
    window.addEventListener('beforeunload', function () {
        if (currentBlobUrl) URL.revokeObjectURL(currentBlobUrl);
    });
});
</script>

@endsection
