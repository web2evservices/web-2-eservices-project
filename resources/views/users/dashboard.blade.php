@extends('admin.layouts.app')

@section('content')

<div class="container mt-4">

    <h2 class="mb-4">Citizen Dashboard</h2>

    <!-- USER INFO -->
    <div class="card mb-4 p-3">
        <h5>Welcome, {{ auth()->user()->username }}</h5>
        <p>Email: {{ auth()->user()->email }}</p>
        <p>Role: {{ auth()->user()->role }}</p>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h5>Browse Services</h5>
                <p>Explore available government services</p>
                <a href="#" class="btn btn-primary btn-sm">View Services</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h5>My Requests</h5>
                <p>Track your submitted requests</p>
                <a href="#" class="btn btn-success btn-sm">View Requests</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h5>Appointments</h5>
                <p>Manage your bookings</p>
                <a href="#" class="btn btn-warning btn-sm">View Appointments</a>
            </div>
        </div>

    </div>

    <!-- REQUEST HISTORY (PLACEHOLDER) -->
    <div class="card p-3">
        <h5>Recent Activity</h5>

        <p class="text-muted">
            Your request history will appear here once the Request Management module is connected.
        </p>

        <ul>
            <li>Request submitted - Status: Pending</li>
            <li>Document uploaded - Status: In Review</li>
        </ul>
    </div>

</div>

@endsection