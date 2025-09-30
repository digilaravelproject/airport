@extends('layouts.guest')

@section('title', 'Email Verification')

@section('content')

{{-- Notification --}}
<div class="mb-4 text-center text-muted">
    {{ __('Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent. If you didn\'t receive the email, we will gladly send you another.') }}
</div>

{{-- Verification link sent status --}}
@if (session('status') == 'verification-link-sent')
    <div class="alert alert-success text-center">
        {{ __('A new verification link has been sent to your email address.') }}
    </div>
@endif

{{-- Actions --}}
<div class="mt-4 d-flex justify-content-between">

    {{-- Resend Verification Email --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-dark">
            {{ __('Resend Verification Email') }}
        </button>
    </form>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link text-decoration-underline text-muted">
            {{ __('Log Out') }}
        </button>
    </form>

</div>

@endsection
