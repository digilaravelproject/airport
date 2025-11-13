<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_new.jpg') }}">

    <title>{{ config('app.name', 'Setop Box Management') }}</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        body {
            background-color: #f8fafc;
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-top: 3rem;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">

        <!-- Auth Card -->
        <div class="auth-card">
            <!-- Logo -->
            <div class="text-center">
                <a href="/">
                    <img src="{{ asset('assets/images/logo_new.jpg') }}" alt="Logo" class="logo">
                </a>
            </div>
            @yield('content')
        </div>

    </div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="{{ asset('assets/css/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
