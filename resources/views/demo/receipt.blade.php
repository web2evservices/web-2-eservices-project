@extends('users.layout')
@section('title','Demo Payment Receipt')
@section('content')
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body text-center">
            <h3 class="mb-2">Payment Demo Receipt</h3>
            <p class="text-success">Payment succeeded (demo).</p>
            <p class="text-muted">This page is a demo receipt — no real transaction recorded.</p>
            <a href="/demo/payment" class="btn btn-outline-primary">Back to demo</a>
        </div>
    </div>
</div>
@endsection
