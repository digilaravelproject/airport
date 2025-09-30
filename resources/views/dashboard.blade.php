@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <!-- Page Header -->
  <header class="header mb-2">
      <div class="container">
          <h2 class="font-semibold text-xl text-dark">
              Dashboard
          </h2>
      </div>
  </header>

  <!-- Main Content -->
  <div class="container main-content">
      <div class="card shadow-sm mb-4">
          <div class="card-body">
              <p class="text-dark">
                  {{ __("You're logged in!") }}
              </p>
          </div>
      </div>
  </div>
@endsection
