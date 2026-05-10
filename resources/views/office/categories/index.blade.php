@extends('office.layouts.app')
@section('title', 'Service Categories')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-tags-fill me-2 text-primary"></i>Service Categories</h3>
    <a href="{{ route('office.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Category
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Services</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td>{{ $cat->id }}</td>
                    <td><strong>{{ $cat->name }}</strong></td>
                    <td>{{ $cat->description ?? '—' }}</td>
                    <td><span class="badge bg-primary rounded-pill">{{ $cat->services_count }}</span></td>
                    <td>
                        <a href="{{ route('office.categories.edit', $cat->id) }}"
                           class="btn btn-sm btn-outline-warning me-1">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('office.categories.destroy', $cat->id) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Delete category \'{{ $cat->name }}\'? This may affect services using it.')">
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
                    <td colspan="5" class="text-center text-muted py-4">
                        No categories yet. <a href="{{ route('office.categories.create') }}">Create one.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection