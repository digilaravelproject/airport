@extends('layouts.guest')

@section('title', 'Login')

@section('content')

{{-- Card Title --}}
<!-- <h3 class="text-center mb-4">Login</h3> -->

{{-- Session Status --}}
@if (session('status'))
    <div class="alert alert-success text-center">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
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

    <!-- Remember Me -->
    <div class="form-check mb-3">
        <input type="checkbox" name="remember" id="remember_me" class="form-check-input">
        <label for="remember_me" class="form-check-label">Remember me</label>
    </div>

    <!-- Forgot Password & Submit -->
    <div class="d-flex justify-content-between align-items-center">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-decoration-underline small">
                Forgot your password?
            </a>
        @endif
        <button type="submit" class="btn btn-dark">
            Log in
        </button>
    </div>
    <hr>
    <!-- Not Registered? -->
    <div class="text-center mt-3">
        @if (Route::has('register'))
            <p class="small">
                Not registered? 
                <a href="{{ route('register') }}" class="text-decoration-underline">Register here</a>
            </p>
        @endif
    </div>
</form>
@endsection
