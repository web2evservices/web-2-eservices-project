<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Citizen Portal') — Gov Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #1e3a5f; }
        .sidebar .nav-link { color: #adb5bd; padding: 10px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); border-radius: 6px; }
        .sidebar .nav-link i { margin-right: 8px; }
        .sidebar-brand { color: #fff; font-weight: 700; font-size: 1.1rem; padding: 20px; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .main-content { background: #f8f9fa; min-height: 100vh; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav class="sidebar d-flex flex-column" style="width: 240px; flex-shrink: 0;">
        <a class="sidebar-brand" href="{{ auth()->check() ? route('user.dashboard') : url('/') }}">
            <i class="bi bi-person-badge"></i> Citizen Portal
        </a>
        <ul class="nav flex-column mt-3 px-2 flex-grow-1">
            @auth
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}"
                       href="{{ route('user.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
            @endauth
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
                   href="{{ route('services.index') }}">
                    <i class="bi bi-grid-3x3-gap"></i> Browse Services
                </a>
            </li>
            @auth
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.requests.*') ? 'active' : '' }}"
                       href="{{ route('user.requests.index') }}">
                        <i class="bi bi-inbox"></i> My Requests
                    </a>
                </li>
            @endauth
        </ul>
        <div class="p-3 border-top border-secondary">
            @auth
                <small class="text-muted d-block mb-2">{{ auth()->user()->username }}</small>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm w-100 mb-2">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
                <a href="{{ url('/') }}" class="btn btn-link btn-sm text-light text-decoration-none p-0">
                    <i class="bi bi-house"></i> Home
                </a>
            @endauth
        </div>
    </nav>

    <div class="flex-grow-1 main-content p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
