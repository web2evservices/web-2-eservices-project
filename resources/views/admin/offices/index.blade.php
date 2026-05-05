@extends('admin.layouts.app')

@section('content')

<h2>Offices</h2>

<a href="/admin/offices/create" class="btn btn-primary mb-3">Create Office</a>

<table class="table table-bordered">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Municipality</th>
    <th>Email</th>
    <th>Address</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

@foreach($offices as $o)
<tr>
    <td>{{$o->id}}</td>
    <td>{{ $o->name }}</td>
    <td>{{ $o->municipality->name }}</td>
    <td>{{ $o->email }}</td>
    <td>{{$o->address}}</td>
    <td>
        @if($o->is_active)
            <span class="text-success">Active</span>
        @else
            <span class="text-danger">Inactive</span>
        @endif
    </td>
    <td>
        <a href="{{ route('offices.edit', $o->id) }}" class="btn btn-warning btn-sm">Edit</a>
        <form method="POST" action="/admin/offices/{{ $o->id }}">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">Delete</button>
        </form>
    </td>
</tr>
@endforeach

</table>

@endsection