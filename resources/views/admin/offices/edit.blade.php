@extends('admin.layouts.app')

@section('content')

<h2>Edit Office</h2>

<form method="POST" action="{{ route('offices.update', $office->id) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control"
               value="{{ $office->name }}">
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control"
               value="{{ $office->email }}">
    </div>

    <div class="mb-3">
        <label>Address</label>
        <input type="text" name="address" class="form-control"
               value="{{ $office->address }}">
    </div>

    <div class="mb-3">
        <label>Municipality</label>
        <select name="municipality_id" class="form-control">
            @foreach($municipalities as $m)
                <option value="{{ $m->id }}"
                    {{ $office->municipality_id == $m->id ? 'selected' : '' }}>
                    {{ $m->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Office User</label>
        <select name="user_id" class="form-select">
    <option value="">Select Office User</option>

    @foreach($officeUsers as $user)
        <option value="{{ $user->id }}"
            {{ isset($office) && $office->user_id == $user->id ? 'selected' : '' }}>
            {{ $user->username }}
        </option>
    @endforeach
</select>
    </div>

    <button class="btn btn-success">Update</button>
    <a href="/admin/offices" class="btn btn-secondary">Back</a>
</form>

@endsection