@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')

{{-- Card Title --}}
<!-- <h3 class="text-center mb-4">Reset Password</h3> -->

{{-- Session Status --}}
@if (session('status'))
    <div class="alert alert-success text-center">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            value="{{ old('email', $request->email) }}" 
            class="form-control @error('email') is-invalid @enderror" 
            required 
            autofocus
        >
        @error('email')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-control @error('password') is-invalid @enderror" 
            required
        >
        @error('password')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input 
            type="password" 
            id="password_confirmation" 
            name="password_confirmation" 
            class="form-control @error('password_confirmation') is-invalid @enderror" 
            required
        >
        @error('password_confirmation')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- Submit -->
    <div class="d-flex justify-end mt-3">
        <button type="submit" class="btn btn-dark w-100">
            Reset Password
        </button>
    </div>
</form>
@endsection
