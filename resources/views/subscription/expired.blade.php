@extends('layouts.guest')

@section('content')
  <div class="container text-center py-5">
      <h1 class="text-danger">🚫 Access Denied</h1>
      <p>Your subscription expired. Allowed year: {{ env('SUBSCRIPTION_YEAR') }}.</p>
      <a href="{{ url('/') }}" class="btn btn-primary mt-3">Renew Subscription</a>
  </div>
@endsection
