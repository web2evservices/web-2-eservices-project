@extends('admin.layouts.app')

@section('content')

<h2>Create Office</h2>

<form method="POST" action="/admin/offices">
@csrf

<input class="form-control mb-2" name="name" placeholder="Office Name" required>

<input class="form-control mb-2" name="email" placeholder="Email" required>

<input class="form-control mb-2" name="address" placeholder="Address" required>

<!-- MUNICIPALITY -->
<select class="form-control mb-2" name="municipality_id" required>
    <option value="" disabled selected>Select Municipality</option>

    @foreach($municipalities as $id => $name)
        <option value="{{ $id }}">{{ $name }}</option>
    @endforeach
</select>

<!-- OFFICE USER -->
<select name="user_id" class="form-control mb-3">
    <option value="">Select Office User (optional)</option>

    @foreach($officeUsers as $user)
        <option value="{{ $user->id }}">
            {{ $user->username }}
        </option>
    @endforeach
</select>

<button class="btn btn-success">Create</button>

</form>

@endsection