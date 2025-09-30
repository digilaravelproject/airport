@extends('layouts.guest')

@section('title', 'Confirm Password')

@section('content')

{{-- Card Title --}}
<!-- <h3 class="text-center mb-4">Confirm Password</h3> -->

{{-- Session Status --}}
@if (session('status'))
    <div class="alert alert-success text-center">
        {{ session('status') }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <p class="mb-4 text-muted">
            This is a secure area of the application. Please confirm your password before continuing.
        </p>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    required 
                    autocomplete="current-password"
                >
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-dark">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
