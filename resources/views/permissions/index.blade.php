@extends('layouts.app')

@section('title', 'Manage Permissions')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <?php
        $page_title = "Manage Permissions";
        $sub_title = "Roles & Access";
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @foreach($roles as $role)
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $role->name }}</h5>
                        <small class="text-muted">Assign Permissions</small>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('permissions.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="role_id" value="{{ $role->id }}">
                            
                            <div class="row">
                                @foreach($permissions as $permission)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="permissions[]" value="{{ $permission->name }}"
                                                   {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                            <label class="form-check-label">{{ $permission->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary px-4">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
