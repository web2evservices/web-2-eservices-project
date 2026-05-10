@extends('office.layouts.app')
@section('title', 'Create Category')
@section('content')

<div class="mb-4">
    <a href="{{ route('office.categories.index') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Back to Categories
    </a>
    <h3 class="mt-2">Create New Category</h3>
</div>

<div class="card border-0 shadow-sm" style="max-width: 520px;">
    <div class="card-body p-4">
        <form action="{{ route('office.categories.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="e.g. Civil Documents" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Optional description...">{{ old('description') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Category
                </button>
                <a href="{{ route('office.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection