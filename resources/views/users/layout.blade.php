<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Citizen Portal') — Gov Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #1e3a5f; }
        .sidebar .nav-link { color: #adb5bd; padding: 10px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); border-radius: 6px; }
        .sidebar .nav-link i { margin-right: 8px; }
        .sidebar-brand { color: #fff; font-weight: 700; font-size: 1.1rem; padding: 20px; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .main-content { background: #f8f9fa; min-height: 100vh; }
        /* Notification Bell */
#notif-bell-btn {
    position: relative; color: #adb5bd; border: none;
    background: transparent; font-size: 1.35rem;
    padding: 4px 10px; cursor: pointer; transition: color .2s;
}
#notif-bell-btn:hover { color: #fff; }
#notif-badge {
    position: absolute; top: 0; right: 4px;
    min-width: 18px; height: 18px; font-size: 10px;
    line-height: 18px; text-align: center; border-radius: 9px;
    background: #dc3545; color: #fff; font-weight: 700; display: none;
}
#notif-dropdown {
    position: absolute; top: 58px; right: 16px;
    width: 360px; max-height: 480px; overflow-y: auto;
    background: #fff; border: 1px solid #dee2e6;
    border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
    z-index: 1050; display: none;
}
.notif-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 16px; border-bottom: 1px solid #dee2e6;
    font-weight: 600; font-size: .9rem;
    position: sticky; top: 0; background: #fff;
}
.notif-item {
    display: flex; gap: 12px; align-items: flex-start;
    padding: 12px 16px; border-bottom: 1px solid #f0f0f0;
    cursor: pointer; transition: background .15s;
}
.notif-item:hover { background: #f8f9fa; }
.notif-item.unread { background: #eef4ff; }
.notif-icon {
    flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
}
.notif-icon.service_request       { background:#dbeafe; color:#2563eb; }
.notif-icon.request_status        { background:#fef3c7; color:#d97706; }
.notif-icon.feedback              { background:#dcfce7; color:#16a34a; }
.notif-icon.chat_message          { background:#e0f2fe; color:#0284c7; }
.notif-icon.appointment_reminder  { background:#fce7f3; color:#db2777; }
.notif-body  { flex:1; min-width:0; }
.notif-title { font-size:.85rem; font-weight:600; margin-bottom:2px; }
.notif-msg   { font-size:.8rem; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.notif-time  { font-size:.72rem; color:#999; margin-top:4px; }
.notif-empty { padding:24px; text-align:center; color:#999; font-size:.85rem; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav class="sidebar d-flex flex-column" style="width: 240px; flex-shrink: 0;">
        <a class="sidebar-brand" href="{{ auth()->check() ? route('user.dashboard') : url('/') }}">
            <i class="bi bi-person-badge"></i> Citizen Portal
        </a>
        <ul class="nav flex-column mt-3 px-2 flex-grow-1">
            @auth
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}"
                       href="{{ route('user.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
            @endauth
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
                   href="{{ route('services.index') }}">
                    <i class="bi bi-grid-3x3-gap"></i> Browse Services
                </a>
            </li>
            @auth
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.requests.*') ? 'active' : '' }}"
                       href="{{ route('user.requests.index') }}">
                        <i class="bi bi-inbox"></i> My Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.appointments.*') ? 'active' : '' }}"
                       href="{{ route('user.appointments.index') }}">
                        <i class="bi bi-calendar-event"></i> Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.chat.*') ? 'active' : '' }}"
                       href="{{ route('user.chat.index') }}">
                        <i class="bi bi-chat-dots"></i> Messages
                    </a>
                </li>
            @endauth
        </ul>
        <div class="p-3 border-top border-secondary">
            @auth
                <small class="text-muted d-block mb-2">{{ auth()->user()->username }}</small>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm w-100 mb-2">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
                <a href="{{ url('/') }}" class="btn btn-link btn-sm text-light text-decoration-none p-0">
                    <i class="bi bi-house"></i> Home
                </a>
            @endauth
        </div>
    </nav>

    <div class="flex-grow-1 d-flex flex-column" style="min-height: 100vh;">
        @auth
        {{-- Top bar with notification bell --}}
        <div style="background: #1e3a5f; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; position: relative;">
            <span style="color: #fff; font-weight: 600;">@yield('page-title', 'Citizen Portal')</span>
            <div style="position: relative;">
                <button id="notif-bell-btn" title="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    <span id="notif-badge">0</span>
                </button>
                <div id="notif-dropdown">
                    <div class="notif-header">
                        <span>Notifications</span>
                        <button id="notif-mark-all" class="btn btn-link btn-sm p-0" style="font-size:.8rem;">
                            Mark all as read
                        </button>
                    </div>
                    <div id="notif-list">
                        <div class="notif-empty">Loading…</div>
                    </div>
                    <a href="{{ route('user.notifications.index') }}"
                       style="display:block; text-align:center; padding:10px; font-size:.82rem; color:#2563eb; border-top:1px solid #dee2e6;">
                        See all notifications
                    </a>
                </div>
            </div>
        </div>
        @endauth

        <div class="flex-grow-1 main-content p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@stack('scripts')
<script>
@auth
(function () {
    const bell       = document.getElementById('notif-bell-btn');
    const badge      = document.getElementById('notif-badge');
    const dropdown   = document.getElementById('notif-dropdown');
    const list       = document.getElementById('notif-list');
    const markAllBtn = document.getElementById('notif-mark-all');
    const csrf       = document.querySelector('meta[name="csrf-token"]').content;

    const iconMap = {
        service_request:      { icon: 'bi-inbox-fill',           css: 'service_request' },
        request_status:       { icon: 'bi-arrow-repeat',         css: 'request_status' },
        feedback:             { icon: 'bi-star-fill',            css: 'feedback' },
        chat_message:         { icon: 'bi-chat-dots-fill',       css: 'chat_message' },
        appointment_reminder: { icon: 'bi-calendar-check-fill',  css: 'appointment_reminder' },
    };

    function updateBadge(count) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }

    function fetchCount() {
        fetch('{{ route("user.notifications.count") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => updateBadge(d.unread_count)).catch(() => {});
    }

    function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function timeAgo(dateStr) {
        const mins = Math.floor((Date.now() - new Date(dateStr)) / 60000);
        if (mins < 1)  return 'Just now';
        if (mins < 60) return `${mins}m ago`;
        const hrs = Math.floor(mins / 60);
        if (hrs < 24)  return `${hrs}h ago`;
        return `${Math.floor(hrs / 24)}d ago`;
    }

    function renderItem(n) {
        const meta = iconMap[n.type] || { icon: 'bi-bell-fill', css: 'service_request' };
        const div  = document.createElement('div');
        div.className = 'notif-item' + (n.is_read ? '' : ' unread');
        div.dataset.id = n.id;
        div.innerHTML = `
            <div class="notif-icon ${meta.css}"><i class="bi ${meta.icon}"></i></div>
            <div class="notif-body">
                <div class="notif-title">${esc(n.title)}</div>
                <div class="notif-msg">${esc(n.message)}</div>
                <div class="notif-time">${timeAgo(n.created_at)}</div>
            </div>`;
        div.addEventListener('click', () => {
            fetch(`{{ url('user/notifications') }}/${n.id}/read`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
            }).then(() => { div.classList.remove('unread'); fetchCount(); });
        });
        return div;
    }

    function loadNotifications() {
        list.innerHTML = '<div class="notif-empty">Loading…</div>';
        fetch('{{ route("user.notifications.index") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            list.innerHTML = '';
            updateBadge(d.unread_count);
            if (!d.data || d.data.length === 0) {
                list.innerHTML = '<div class="notif-empty">No notifications yet.</div>';
                return;
            }
            d.data.forEach(n => list.appendChild(renderItem(n)));
        }).catch(() => {
            list.innerHTML = '<div class="notif-empty text-danger">Could not load notifications.</div>';
        });
    }

    bell.addEventListener('click', e => {
        e.stopPropagation();
        const open = dropdown.style.display === 'block';
        dropdown.style.display = open ? 'none' : 'block';
        if (!open) loadNotifications();
    });

    document.addEventListener('click', e => {
        if (!dropdown.contains(e.target) && e.target !== bell)
            dropdown.style.display = 'none';
    });

    markAllBtn.addEventListener('click', () => {
        fetch('{{ route("user.notifications.mark-all-read") }}', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(() => {
            list.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
            updateBadge(0);
        });
    });

    // --- PUSHER REAL-TIME INTEGRATION ---
    const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
        cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
        encrypted: true
    });

    const channel = pusher.subscribe(`user.{{ Auth::id() }}`);
    
    channel.bind('notification.created', function(data) {
        const newNotif = {
            id: data.id,
            title: data.title,
            message: data.message,
            type: data.type,
            is_read: false,
            created_at: data.created_at
        };
        
        const existingList = list.querySelector('.notif-empty');
        if (existingList) {
            list.innerHTML = '';
        }
        
        list.insertBefore(renderItem(newNotif), list.firstChild);
        fetchCount();
    });

    fetchCount();
    setInterval(fetchCount, 30000);
})();
@endauth
</script>
