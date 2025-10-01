@extends('layouts.app')

@section('title', 'Manage Permissions')

@section('content')
<div class="container-fluid">
    <?php
        $page_title = "Manage Permissions";
        $sub_title = "Roles & Access";
    ?>

    <!-- Page-Title -->
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
    <!-- End Page Title -->

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Add New Permission -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Add New Permission</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('permissions.store') }}" method="POST" class="row g-2">
                @csrf
                <div class="col-md-6">
                    <input type="text" name="name" class="form-control" placeholder="Permission name" required>
                    @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success">Add Permission</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Roles with Permissions -->
    <div class="row">
        @foreach($roles as $role)
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $role->name }}</h5>
                        <small class="text-muted">Assign Permissions</small>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('permissions.update') }}" method="POST" class="role-permission-form">
                            @csrf
                            <input type="hidden" name="role_id" value="{{ $role->id }}">

                            <!-- Select All Checkbox -->
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input select-all" id="selectAll-{{ $role->id }}">
                                <label class="form-check-label fw-bold" for="selectAll-{{ $role->id }}">
                                    Select All Permissions
                                </label>
                            </div>
                            
                            <div class="row">
                                @foreach($permissions as $permission)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox" 
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.role-permission-form').forEach(function(form) {
        const selectAll = form.querySelector('.select-all');
        const checkboxes = form.querySelectorAll('.permission-checkbox');

        function refreshSelectAll() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            selectAll.checked = allChecked;
        }

        // Initial state
        refreshSelectAll();

        // When Select All clicked
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });

        // When any permission clicked
        checkboxes.forEach(cb => {
            cb.addEventListener('change', refreshSelectAll);
        });
    });
});
</script>
@endsection
