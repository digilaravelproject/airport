@extends('layouts.app')

@section('title', 'Update Password')

@section('content')
<div class="permission-card">
    <!-- Page Header -->
    <header class="mb-3">
        <h2 class="h5 fw-bold">
            {{ __('Update Password') }}
        </h2>
        <p class="text-muted">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <!-- Update Password Form -->
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
            <input type="password" name="current_password" id="update_password_current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
            @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
            <input type="password" name="password" id="update_password_password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" name="password_confirmation" id="update_password_password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password">
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if(session('status') === 'password-updated')
                <p class="text-success mb-0">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</div>
@endsection
