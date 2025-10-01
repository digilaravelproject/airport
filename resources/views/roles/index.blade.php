@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Role Management</h4>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">Add New Role</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Role</th>
                        <th>Guard</th>
                        <th>Permissions</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td class="fw-semibold">{{ $role->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $role->guard_name }}</span></td>
                            <td>
                                @if($role->permissions->isEmpty())
                                    <span class="text-muted">— No permissions —</span>
                                @else
                                    @foreach($role->permissions as $perm)
                                        <span class="badge bg-secondary mb-1">{{ $perm->name }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                {{-- Delete (optional) --}}
                                <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                      onsubmit="return confirm('Delete role {{ $role->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="ti ti-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
