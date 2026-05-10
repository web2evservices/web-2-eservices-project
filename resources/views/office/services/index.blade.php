@extends('office.layouts.app')
@section('title', 'Services')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-list-task me-2 text-primary"></i>Services</h3>
    <a href="{{ route('office.services.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Service
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Required Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $svc)
                <tr>
                    <td><strong>{{ $svc->name }}</strong></td>
                    <td>
                        <span class="badge bg-secondary">{{ $svc->category->name ?? '—' }}</span>
                    </td>
                    <td>${{ number_format($svc->price, 2) }}</td>
                    <td>{{ $svc->duration }} min</td>
                    <td>
                        @if($svc->required_documents && count($svc->required_documents))
                            {{ implode(', ', $svc->required_documents) }}
                        @else
                            <span class="text-muted">None</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('office.services.edit', $svc->id) }}"
                           class="btn btn-sm btn-outline-warning me-1">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('office.services.destroy', $svc->id) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Delete service \'{{ $svc->name }}\'?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No services yet. <a href="{{ route('office.services.create') }}">Create one.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection