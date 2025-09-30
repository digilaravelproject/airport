@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')

{{-- Description --}}
<p class="text-center mb-4 text-muted">
    Forgot your password? No problem. Just enter your email below and we will email you a password reset link.
</p>

{{-- Session Status --}}
@if (session('status'))
    <div class="alert alert-success text-center">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            value="{{ old('email') }}" 
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

    <!-- Submit Button -->
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-dark">
            Email Password Reset Link
        </button>
    </div>
</form>
@endsection
