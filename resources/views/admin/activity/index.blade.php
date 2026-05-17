@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Activity Monitoring</h2>
        <small class="text-muted">User actions on services, appointments &amp; requests</small>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total']) }}</div>
                    <div class="text-muted small">All activities</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-info">{{ number_format($stats['today']) }}</div>
                    <div class="text-muted small">Today</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-success">{{ number_format($stats['services']) }}</div>
                    <div class="text-muted small">Services</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-primary">{{ number_format($stats['appointments']) }}</div>
                    <div class="text-muted small">Appointments</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-warning">{{ number_format($stats['requests']) }}</div>
                    <div class="text-muted small">Service requests</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.activity') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Entity</label>
                    <select name="subject_type" class="form-select form-select-sm">
                        <option value="">All types</option>
                        <option value="service" @selected(request('subject_type') === 'service')>Service</option>
                        <option value="appointment" @selected(request('subject_type') === 'appointment')>Appointment</option>
                        <option value="service_request" @selected(request('subject_type') === 'service_request')>Service Request</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All actions</option>
                        <option value="created" @selected(request('action') === 'created')>Created</option>
                        <option value="updated" @selected(request('action') === 'updated')>Updated</option>
                        <option value="deleted" @selected(request('action') === 'deleted')>Deleted</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">User</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                {{ $u->username }} ({{ $u->role }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Description or user…" value="{{ request('q') }}">
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.activity') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>When</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Description</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td class="text-nowrap small">
                                    {{ $activity->created_at->format('M d, Y') }}<br>
                                    <span class="text-muted">{{ $activity->created_at->format('H:i') }}</span>
                                </td>
                                <td>
                                    @if($activity->user)
                                        <strong>{{ $activity->user->username }}</strong><br>
                                        <span class="text-muted small">{{ $activity->user->email }}</span><br>
                                        <span class="badge bg-secondary">{{ $activity->user->role }}</span>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $activity->actionBadgeClass() }}">
                                        {{ ucfirst($activity->action) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $activity->subjectTypeLabel() }}
                                    </span>
                                    @if($activity->subject_id)
                                        <br><code class="small">#{{ $activity->subject_id }}</code>
                                    @endif
                                </td>
                                <td>{{ $activity->description }}</td>
                                <td class="small">
                                    @if($activity->properties)
                                        @if(!empty($activity->properties['old']) || !empty($activity->properties['new']))
                                            @foreach($activity->properties['old'] ?? [] as $key => $oldVal)
                                                @php $newVal = $activity->properties['new'][$key] ?? null; @endphp
                                                @if($oldVal != $newVal)
                                                    <span class="text-muted">{{ str_replace('_', ' ', $key) }}:</span>
                                                    <span class="text-decoration-line-through">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</span>
                                                    → <strong>{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</strong><br>
                                                @endif
                                            @endforeach
                                            @foreach($activity->properties['new'] ?? [] as $key => $newVal)
                                                @if(!array_key_exists($key, $activity->properties['old'] ?? []))
                                                    <span class="text-muted">{{ str_replace('_', ' ', $key) }}:</span>
                                                    <strong>{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</strong><br>
                                                @endif
                                            @endforeach
                                        @elseif(!empty($activity->properties['attributes']))
                                            @foreach($activity->properties['attributes'] as $key => $val)
                                                <span class="text-muted">{{ str_replace('_', ' ', $key) }}:</span>
                                                {{ is_array($val) ? json_encode($val) : $val }}<br>
                                            @endforeach
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    No activity recorded yet. Actions will appear here when users create, update, or delete services, appointments, or service requests.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($activities->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
