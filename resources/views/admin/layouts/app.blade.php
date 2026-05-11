<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .navbar {
            background: #111827 !important;
        }

        .sidebar {
            min-height: 100vh;
            background: #1f2937;
            padding-top: 20px;
        }

        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 6px;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: #374151;
            color: #fff;
            transform: translateX(4px);
        }

        .sidebar a.active {
            background: #2563eb;
            color: white !important;
        }

        .content {
            padding: 25px;
        }

        .logout-btn {
            width: 100%;
            margin-top: 20px;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark px-3">
    <a class="navbar-brand fw-bold" href="/admin/dashboard">
        ⚙️ Admin Panel
    </a>
</nav>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-2 sidebar">

            <a href="/admin/dashboard"
               class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
                📊 Dashboard
            </a>

            <a href="/admin/municipalities"
               class="{{ request()->is('admin/municipalities') ? 'active' : '' }}">
                🏙 Municipalities
            </a>

            <a href="/admin/offices"
               class="{{ request()->is('admin/offices') ? 'active' : '' }}">
                🏢 Offices
            </a>

            <a href="/admin/users"
               class="{{ request()->is('admin/users') ? 'active' : '' }}">
                👤 Users
            </a>

            <a href="/admin/analytics"
               class="{{ request()->is('admin/analytics') ? 'active' : '' }}">
                📈 Analytics
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger logout-btn">
                    Logout
                </button>
            </form>

        </div>

        <div class="col-md-10 content">
            @yield('content')
        </div>

    </div>
</div>

</body>
</html>