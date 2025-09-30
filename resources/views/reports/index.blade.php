@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <?php
        $page_title = "Generate Inventory Reports";
        $sub_title = "Reports";
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
    <!-- End Page Title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Generate Inventory Reports</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('reports.generate') }}">
                        @csrf
                        <div class="row g-3">
                            <!-- Status -->
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <!-- Client -->
                            <div class="col-md-3">
                                <label class="form-label">Client</label>
                                <select name="client_id" class="form-select">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->id }} - {{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Package -->
                            <div class="col-md-3">
                                <label class="form-label">Package</label>
                                <select name="package_id" class="form-select">
                                    <option value="">All Packages</option>
                                    @foreach($packages as $pkg)
                                        <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Warranty Date -->
                            <div class="col-md-3">
                                <label class="form-label">Warranty Before</label>
                                <input type="date" name="warranty_before" class="form-control">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-dark px-4">Download PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
