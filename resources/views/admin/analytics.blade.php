@extends('admin.layouts.app')

@section('content')

<h2>Analytics</h2>

<h4>Revenue</h4>
<p class="fs-3">${{ $revenue }}</p>

<h4>Requests per Office</h4>

<table class="table table-bordered">
<tr>
    <th>Office ID</th>
    <th>Total Requests</th>
</tr>

@foreach($requestsPerOffice as $r)
<tr>
    <td>{{ $r->office_id }}</td>
    <td>{{ $r->total }}</td>
</tr>
@endforeach

</table>

@endsection