@extends('admin.layouts.app')

@section('content')

<h2 class="mb-4">Create Municipality</h2>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/admin/municipalities">
    @csrf

    <div class="mb-3">
        <label class="form-label">Municipality Name</label>
        <input type="text" name="name" class="form-control" placeholder="Municipality Name" required>
    </div>

    <div class="mb-3">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" placeholder="Municipality City" required>
    </div>

    <button type="submit" class="btn btn-success">
        Create
    </button>

    <a href="/admin/municipalities" class="btn btn-secondary">
        Cancel
    </a>
</form>

@endsection