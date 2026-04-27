<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="/css/log.css">
</head>
<body>
    <div class="container">
        <div class="form-container sign-in">
            <form action="/reset-password" method="POST">
                @csrf
                <h1>New Password</h1>

                <input type="hidden" name="token" value="{{ $token }}">

                <input type="email" name="email" value="{{ $email }}" readonly cursor: not-allowed;">
                @error('email')
                    <span style="color: red; font-weight: 600">{{ $message }}</span>
                @enderror

                <input type="password" name="password"
                       placeholder="New Password" required>
                @error('password')
                    <span style="color: red; font-weight: 600">{{ $message }}</span>
                @enderror

                {{-- Must be named password_confirmation for Laravel's confirmed rule --}}
                <input type="password" name="password_confirmation"
                       placeholder="Confirm New Password" required>

                <button type="submit">Reset Password</button>
            </form>
        </div>
        <div class="toggle-container">
        <div class="toggle"></div>
        <div class="toggle-panel toggle-right">
                <h1></h1>
        </div>
        </div>
        </div>
    </div>
</body>
</html>