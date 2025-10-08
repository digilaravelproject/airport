@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container-fluid">
    <?php $page_title = "Manage Users"; $sub_title = "Roles & Accounts"; ?>

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
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Users</h5>
            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ route('users.index') }}" class="d-flex">

                    <input type="text" class="form-control form-control-sm me-2" name="search"
                           value="{{ $search }}" placeholder="Search...">
                    <!-- NEW: Dropdown for field selection -->
                    <select name="field" class="form-select form-select-sm me-2" style="width:150px;">
                        <option value="all" {{ request('field','all')=='all' ? 'selected' : '' }}>All Fields</option>
                        <option value="id" {{ request('field')=='id' ? 'selected' : '' }}>User ID</option>
                        <option value="name" {{ request('field')=='name' ? 'selected' : '' }}>Name</option>
                        <option value="email" {{ request('field')=='email' ? 'selected' : '' }}>Email</option>
                        <option value="role" {{ request('field')=='role' ? 'selected' : '' }}>Role</option>
                    </select>
                    <button class="btn btn-sm btn-primary me-2">Search</button>
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </form>
                <a href="{{ route('users.create') }}" class="btn btn-sm btn-success">Add User</a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td>{{ $u->id }}</td>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    @forelse($u->roles as $r)
                                        <span class="badge bg-secondary">{{ $r->name }}</span>
                                    @empty
                                        <span class="text-muted">No role</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if(!$u->hasRole('Admin'))
                                        <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this user?')">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
