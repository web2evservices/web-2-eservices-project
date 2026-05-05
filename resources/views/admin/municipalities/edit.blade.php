@extends('admin.layouts.app')

@section('content')

<h2>Edit Municipality</h2>

<form method="POST" action="{{ route('municipalities.update', $municipality->id) }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control"
               value="{{ $municipality->name }}">
    </div>

    <div class="mb-3">
        <label>City</label>
        <input type="text" name="city" class="form-control"
               value="{{ $municipality->city }}">
    </div>

    <button class="btn btn-success">Update</button>
    <a href="/admin/municipalities" class="btn btn-secondary">Back</a>
</form>

@endsection