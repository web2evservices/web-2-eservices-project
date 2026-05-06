@extends('admin.layouts.app')

@section('content')
<h2>Admin Dashboard</h2>

<div class="row mt-4">

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Users</h5>
            <h3>{{ $users }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Offices</h5>
            <h3>{{ $offices }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Municipalities</h5>
            <h3>{{ $municipalities }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Active Offices</h5>
            <h3>{{ $activeOffices }}</h3>
        </div>
    </div>

</div>
@endsection