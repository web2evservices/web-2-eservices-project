@extends('office.layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-bell-fill me-2 text-primary"></i>All Notifications</h4>
    @if($unreadCount > 0)
        <button id="mark-all-read-btn" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-check2-all me-1"></i>Mark all as read ({{ $unreadCount }})
        </button>
    @endif
</div>

@if($notifications->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-bell-slash" style="font-size:3rem;"></i>
            <p class="mt-3 mb-0">No notifications yet.</p>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <ul class="list-group list-group-flush">
            @foreach($notifications as $notif)
                @php
                    $icons = [
                        'service_request'      => ['bi-inbox-fill',          'text-primary', 'bg-primary bg-opacity-10'],
                        'request_status'       => ['bi-arrow-repeat',        'text-warning', 'bg-warning bg-opacity-10'],
                        'feedback'             => ['bi-star-fill',           'text-success', 'bg-success bg-opacity-10'],
                        'chat_message'         => ['bi-chat-dots-fill',      'text-info',    'bg-info bg-opacity-10'],
                        'appointment_reminder' => ['bi-calendar-check-fill', 'text-danger',  'bg-danger bg-opacity-10'],
                    ];
                    $icon = $icons[$notif->type] ?? ['bi-bell-fill', 'text-secondary', 'bg-secondary bg-opacity-10'];
                @endphp
                <li class="list-group-item notif-row {{ $notif->is_read ? '' : 'bg-light' }}"
                    data-id="{{ $notif->id }}" style="cursor:pointer;">
                    <div class="d-flex gap-3 align-items-start py-1">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $icon[2] }}"
                             style="width:42px;height:42px;">
                            <i class="bi {{ $icon[0] }} {{ $icon[1] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong class="small">{{ $notif->title }}</strong>
                                <small class="text-muted ms-3 text-nowrap">{{ $notif->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 small text-muted">{{ $notif->message }}</p>
                        </div>
                        @if(!$notif->is_read)
                            <span class="badge bg-primary rounded-pill align-self-center"
                                  style="width:10px;height:10px;padding:0;"></span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="mt-3">{{ $notifications->links() }}</div>
@endif
@endsection

@section('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    document.querySelectorAll('.notif-row').forEach(row => {
        row.addEventListener('click', () => {
            fetch(`/office/notifications/${row.dataset.id}/read`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
            }).then(() => {
                row.classList.remove('bg-light');
                const dot = row.querySelector('.badge.bg-primary');
                if (dot) dot.remove();
            });
        });
    });
    const btn = document.getElementById('mark-all-read-btn');
    if (btn) {
        btn.addEventListener('click', () => {
            fetch('/office/notifications/mark-all-read', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
            }).then(() => location.reload());
        });
    }
})();
</script>
@endsection