@extends('admin.layouts.app')

@section('content')

<h2>Users</h2>

<table class="table table-bordered">

<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Office</th>
    <th>Status</th>
    <th>Action</th>
</tr>

@foreach($users as $u)
<tr>
    <td>{{$u->id}}
    <td>{{ $u->username }}</td>
    <td>{{ $u->email }}</td>
    <td>{{ $u->role }}</td>
    <td>{{ $u->office->name ?? '-' }}</td>
    <td>
        @if($u->is_active)
            Active
        @else
            Disabled
        @endif
    </td>
    <td>
        <form method="POST" action="/admin/users/{{ $u->id }}/toggle">
            @csrf
            @method('PATCH')
            <button class="btn btn-warning btn-sm">
                Toggle
            </button>
        </form>
    </td>
</tr>
@endforeach

</table>

@endsection