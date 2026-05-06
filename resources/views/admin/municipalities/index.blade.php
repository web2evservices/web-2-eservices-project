@extends('admin.layouts.app')

@section('content')

<h2>Municipalities</h2>

<a href="{{ route('municipalities.create') }}" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">
    Create Municipality
</a>

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>City</th>
        <th>Actions</th>
    </tr>

    @foreach($municipalities as $m)
    <tr>
        <td>{{ $m->id }}</td>
        <td>{{ $m->name }}</td>
        <td>{{ $m->city }}</td>
        <td>
            <a href="{{ route('municipalities.edit', $m->id) }}" class="btn btn-warning btn-sm">Edit</a>
            <form method="POST" action="/admin/municipalities/{{ $m->id }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm">Delete</button>
            </form>
        </td>
    </tr>
    @endforeach

</table>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">

        <form method="POST" action="/admin/municipalities">
            @csrf

            <div class="mb-2">
                <input class="form-control" name="name" placeholder="Name" required>
            </div>

            <div class="mb-2">
                <input class="form-control" name="city" placeholder="City" required>
            </div>

            <button class="btn btn-success w-100">Save</button>
        </form>

    </div>
  </div>
</div>

@endsection