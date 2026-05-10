<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Office Portal') — Gov Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #1e3a5f; }
        .sidebar .nav-link { color: #adb5bd; padding: 10px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); border-radius: 6px; }
        .sidebar .nav-link i { margin-right: 8px; }
        .sidebar-brand { color: #fff; font-weight: 700; font-size: 1.1rem; padding: 20px; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .main-content { background: #f8f9fa; min-height: 100vh; }
        .top-navbar { background: #1e3a5f; }
    </style>
</head>
<body>

<div class="d-flex">
    {{-- Sidebar --}}
    <nav class="sidebar d-flex flex-column" style="width: 240px; flex-shrink: 0;">
        <a class="sidebar-brand" href="{{ route('office.dashboard') }}">
            <i class="bi bi-building-fill"></i> Office Portal
        </a>
        <ul class="nav flex-column mt-3 px-2 flex-grow-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('office.dashboard') ? 'active' : '' }}"
                   href="{{ route('office.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('office.services.*') ? 'active' : '' }}"
                   href="{{ route('office.services.index') }}">
                    <i class="bi bi-list-task"></i> Services
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('office.categories.*') ? 'active' : '' }}"
                   href="{{ route('office.categories.index') }}">
                    <i class="bi bi-tags-fill"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('office.profile.*') ? 'active' : '' }}"
                   href="{{ route('office.profile.edit') }}">
                    <i class="bi bi-building-gear"></i> Office Profile
                </a>
            </li>
        </ul>
        <div class="p-3 border-top border-secondary">
            <small class="text-muted d-block mb-2">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</small>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm w-100">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="flex-grow-1 main-content p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
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
@yield('scripts')
</body>
</html>