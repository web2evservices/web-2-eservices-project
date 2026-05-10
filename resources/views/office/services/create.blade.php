@extends('office.layouts.app')
@section('title', 'New Service')
@section('content')

<div class="mb-4">
    <a href="{{ route('office.services.index') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Back to Services
    </a>
    <h3 class="mt-2">Create New Service</h3>
</div>

<div class="card border-0 shadow-sm" style="max-width: 640px;">
    <div class="card-body p-4">
        <form action="{{ route('office.services.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required placeholder="e.g. Birth Certificate">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                    <option value="">-- Select a Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Price (USD) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" name="price"
                               class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price') }}" required>
                    </div>
                    @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Duration <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" min="1" name="duration"
                               class="form-control @error('duration') is-invalid @enderror"
                               value="{{ old('duration') }}" required>
                        <span class="input-group-text">minutes</span>
                    </div>
                    @error('duration')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Required Documents</label>
                <div id="docs-container">
                    @if(old('required_documents'))
                        @foreach(old('required_documents') as $doc)
                        <div class="input-group mb-2">
                            <input type="text" name="required_documents[]"
                                   class="form-control" value="{{ $doc }}"
                                   placeholder="e.g. National ID">
                            <button type="button" class="btn btn-outline-danger"
                                    onclick="removeDoc(this)">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        @endforeach
                    @else
                    <div class="input-group mb-2">
                        <input type="text" name="required_documents[]"
                               class="form-control" placeholder="e.g. National ID">
                        <button type="button" class="btn btn-outline-danger"
                                onclick="removeDoc(this)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addDoc()">
                    <i class="bi bi-plus-lg me-1"></i> Add Document
                </button>
                <small class="text-muted d-block mt-1">List each document citizens need to submit.</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Service
                </button>
                <a href="{{ route('office.services.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function addDoc() {
    const container = document.getElementById('docs-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="required_documents[]" class="form-control" placeholder="Document name">
        <button type="button" class="btn btn-outline-danger" onclick="removeDoc(this)">
            <i class="bi bi-x-lg"></i>
        </button>`;
    container.appendChild(div);
}
function removeDoc(btn) {
    const container = document.getElementById('docs-container');
    // Keep at least one field
    if (container.querySelectorAll('.input-group').length > 1) {
        btn.closest('.input-group').remove();
    }
}
</script>
@endsection