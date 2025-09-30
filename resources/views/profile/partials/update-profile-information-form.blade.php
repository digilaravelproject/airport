@extends('layouts.app')

@section('title', 'Profile Information')

@section('content')
<div class="container">
    <!-- Page Header -->
    <header class="mb-4">
        <h2 class="h5 fw-bold">{{ __('Profile Information') }}</h2>
        <p class="text-muted">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <!-- Email Verification Form -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- Profile Update Form -->
    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus>
            @error('name')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-muted mb-1">
                        {{ __('Your email address is unverified.') }}
                    </p>
                    <button form="send-verification" class="btn btn-link p-0">{{ __('Click here to re-send the verification email.') }}</button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success mt-2">{{ __('A new verification link has been sent to your email address.') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Save Button -->
        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p class="text-muted mb-0" id="saved-msg">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</div>

<!-- Optional: Auto-hide "Saved" message -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const savedMsg = document.getElementById('saved-msg');
        if(savedMsg){
            setTimeout(() => savedMsg.style.display = 'none', 2000);
        }
    });
</script>
@endsection
