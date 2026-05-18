@extends('users.layout')

@section('title', 'My Account')
@section('page-title', 'My Account')

@section('content')
<div class="row g-4">

    {{-- Left column: avatar + 2FA card --}}
    <div class="col-lg-4">

        {{-- Avatar / quick-info --}}
        <div class="card border-0 shadow-sm mb-4 text-center">
            <div class="card-body py-4">
                <div class="mb-3">
                    <div style="
                        width:80px; height:80px; border-radius:50%;
                        background:linear-gradient(135deg,#1e3a5f,#3b82f6);
                        display:inline-flex; align-items:center; justify-content:center;
                        font-size:2rem; color:#fff; font-weight:700;
                    ">
                        {{ strtoupper(substr($user->username, 0, 1)) }}
                    </div>
                </div>
                <h5 class="fw-bold mb-1">{{ $user->username }}</h5>
                <span class="badge
                    @if($user->role === 'admin') bg-danger
                    @elseif($user->role === 'office_user') bg-warning text-dark
                    @else bg-primary
                    @endif
                    rounded-pill px-3">
                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                </span>
                <p class="text-muted mt-2 mb-0" style="font-size:.85rem;">
                    <i class="bi bi-calendar3 me-1"></i>
                    Member since {{ $user->created_at->format('M Y') }}
                </p>
            </div>
        </div>

        {{-- Two-Factor Authentication --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0 pt-3 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-shield-lock-fill me-2 text-primary"></i>Two-Factor Authentication
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($user->two_factor_enabled)
                        <span class="badge bg-success rounded-pill px-3 py-2">
                            <i class="bi bi-shield-check me-1"></i>Enabled
                        </span>
                    @else
                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                            <i class="bi bi-shield-x me-1"></i>Disabled
                        </span>
                    @endif
                </div>

                <p class="text-muted mb-3" style="font-size:.85rem;">
                    @if($user->two_factor_enabled)
                        A one-time code is sent to your email every time you sign in. Your account is better protected.
                    @else
                        Enable 2FA to receive a verification code on your email at every login for extra security.
                    @endif
                </p>

                <form action="{{ route('user.account.toggle2fa') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="btn w-100 {{ $user->two_factor_enabled ? 'btn-outline-danger' : 'btn-primary' }}">
                        @if($user->two_factor_enabled)
                            <i class="bi bi-shield-x me-2"></i>Disable 2FA
                        @else
                            <i class="bi bi-shield-check me-2"></i>Enable 2FA
                        @endif
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- Right column: edit form --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-person-gear me-2 text-primary"></i>Account Information
                </h6>
                <p class="text-muted mb-0" style="font-size:.85rem;">Update your personal details below.</p>
            </div>
            <div class="card-body p-4">

                <form action="{{ route('user.account.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="text-uppercase text-muted fw-semibold mb-3"
                        style="font-size:.75rem; letter-spacing:.08em;">Personal Info</h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input type="text" name="username"
                                       class="form-control border-start-0 @error('username') is-invalid @enderror"
                                       value="{{ old('username', $user->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email"
                                       class="form-control border-start-0 @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-telephone text-muted"></i>
                                </span>
                                <input type="tel" name="tel"
                                       class="form-control border-start-0 @error('tel') is-invalid @enderror"
                                       value="{{ old('tel', $user->tel) }}" required>
                                @error('tel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="text-uppercase text-muted fw-semibold mb-1"
                        style="font-size:.75rem; letter-spacing:.08em;">Change Password</h6>
                    <p class="text-muted mb-3" style="font-size:.82rem;">
                        Leave all three fields blank to keep your current password.
                    </p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password" name="current_password"
                                       class="form-control border-start-0 @error('current_password') is-invalid @enderror"
                                       autocomplete="current-password">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock-fill text-muted"></i>
                                </span>
                                <input type="password" name="password"
                                       class="form-control border-start-0 @error('password') is-invalid @enderror"
                                       autocomplete="new-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock-fill text-muted"></i>
                                </span>
                                <input type="password" name="password_confirmation"
                                       class="form-control border-start-0"
                                       autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-floppy me-1"></i>Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>
@endsection