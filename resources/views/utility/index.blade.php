@extends('layouts.app')

@section('content')

<style>
    .import-header { background-color: #0f172a !important; }
    .summary-card { cursor:pointer; transition: transform .05s ease-in; }
    .summary-card:hover { transform: translateY(-1px); }
    .summary-active { border-color:#0d6efd !important; box-shadow: 0 0 0 .1rem rgba(13,110,253,.15); }
    .table thead a { font-weight:600; }
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
                </div>
            </div>
        </div>
    </div>
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
                                <label for="file" class="form-label fw-semibold">Select File</label>
                                <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-upload me-1"></i> Import
                                </button>
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
    <!-- End Channel Import Section -->

</div>
@endsection
