@extends('users.layout')

@section('title', 'Messages')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-dark fw-bold"><i class="bi bi-chat-dots text-primary me-2"></i>My Messages</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if($conversations->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 text-center p-5">
                    <div class="card-body">
                        <i class="bi bi-chat-square-text text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3 text-dark fw-bold">No Messages Yet</h4>
                        <p class="text-muted">You haven't started any conversations with office staff yet. Messages related to your requests will appear here.</p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="list-group list-group-flush">
                        @foreach($conversations as $conv)
                            @php
                                $isSender = $conv->sender_id === auth()->id();
                                $otherUser = $isSender ? $conv->receiver : $conv->sender;
                                $office = \App\Models\Government_Offices::where('user_id', $otherUser->id)->first();
                                $displayName = $office ? $office->name : $otherUser->username;
                            @endphp
                            <a href="{{ route('user.chat.show', $otherUser->id) }}" class="list-group-item list-group-item-action p-4 border-bottom">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 50px; height: 50px; font-size: 1.2rem; font-weight: bold;">
                                            {{ strtoupper(substr($displayName, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark">{{ $displayName }}</h6>
                                            <p class="mb-0 text-muted small text-truncate" style="max-width: 300px;">
                                                @if($isSender) <span class="text-secondary">You:</span> @endif
                                                {{ $conv->message }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">{{ $conv->created_at->diffForHumans() }}</small>
                                        <span class="badge bg-primary rounded-pill mt-1 opacity-0">0</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
