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
<div class="row mt-4">
    <div class="col-12">
        <div class="card p-3">
            <h5>Recent Admin Activity</h5>
            <ul class="list-group list-group-flush mt-3">
                @forelse($adminActivities as $activity)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $activity->title }}</strong>
                                <p class="mb-1">{{ $activity->message }}</p>
                            </div>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No recent activity yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection