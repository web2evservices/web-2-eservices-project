<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard">Admin Panel</a>
        <div class="text-white">
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-2 bg-light min-vh-100 p-3">
            <a href="/admin/dashboard" class="d-block mb-2">Dashboard</a>
            <a href="/admin/municipalities" class="d-block mb-2">Municipalities</a>
            <a href="/admin/offices" class="d-block mb-2">Offices</a>
            <a href="/admin/users" class="d-block mb-2">Users</a>
            <a href="/admin/analytics" class="d-block mb-2">Analytics</a>
            <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-danger">
        Logout
    </button>
</form>
        </div>

        <!-- Content -->
        <div class="col-md-10 p-4">
            @yield('content')
        </div>

    </div>
</div>

</body>
</html>