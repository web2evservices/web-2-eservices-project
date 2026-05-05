@extends('admin.layouts.app')

@section('content')

<h2>Create Office</h2>

<form method="POST" action="/admin/offices">
@csrf

<input class="form-control mb-2" name="name" placeholder="Office Name" required>

<input class="form-control mb-2" name="email" placeholder="Email" required>

<input class="form-control mb-2" name="address" placeholder="Address" required>

<select class="form-control mb-2" name="municipality_id">
<option value="" disabled selected hidden>Select Municipality</option>>
    @foreach($municipalities as $id => $name)
        <option value="{{ $id }}">{{ $name }}</option>
    @endforeach
</select>

<button class="btn btn-success">Create</button>

</form>

@endsection