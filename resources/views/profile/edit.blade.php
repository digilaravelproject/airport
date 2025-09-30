@extends('layouts.app')

@section('title', 'Profile')

@section('content')
  <!-- Page Header -->
  <header class="header mb-4">
      <div class="container">
          <h2 class="font-semibold text-xl text-dark">
              Profile
          </h2>
      </div>
  </header>

  <div class="container main-content">
      <div class="row g-4">
          <!-- Update Profile Information -->
          <div class="col-12">
              <div class="card shadow-sm">
                  <div class="card-body">
                      @include('profile.partials.update-profile-information-form')
                  </div>
              </div>
          </div>

          <!-- Update Password -->
          <div class="col-12">
              <div class="card shadow-sm">
                  <div class="card-body">
                      @include('profile.partials.update-password-form')
                  </div>
              </div>
          </div>

          <!-- Delete User -->
          <div class="col-12">
              <div class="card shadow-sm">
                  <div class="card-body">
                      @include('profile.partials.delete-user-form')
                  </div>
              </div>
          </div>
      </div>
  </div>
@endsection
