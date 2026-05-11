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
    <form method="POST" action="/admin/users/{{ $u->id }}/toggle" class="d-inline">
        @csrf
        @method('PATCH')
        <button class="btn btn-warning btn-sm">
            Toggle
        </button>
    </form>

    <form method="POST" action="{{ route('admin.users.role', $u->id) }}" class="d-inline">
    @csrf
    @method('PATCH')
    <select name="role" class="form-select form-select-sm d-inline w-auto">
        <option value="citizen" {{ $u->role == 'citizen' ? 'selected' : '' }}>
            Citizen
        </option>

        <option value="office_user" {{ $u->role == 'office_user' ? 'selected' : '' }}>
            Office User
        </option>

        <option value="admin" {{ $u->role == 'admin' ? 'selected' : '' }}>
            Admin
        </option>
    </select>
    <button class="btn btn-primary btn-sm">
        Update Role
    </button>
</form>
 <form method="POST" action="{{ route('admin.users.delete', $u->id) }}" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger btn-sm">
                Delete
            </button>
</form>
</td>
</tr>
@endforeach

</table>

@endsection