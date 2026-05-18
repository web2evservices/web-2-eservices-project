<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="/css/log.css">
</head>
<body>
    <div class="container">
        <div class="form-container sign-in">
            <form action="/forget-password" method="POST">
                @csrf
                <h1>Reset Password</h1>
                <span>Enter your email and we'll send you a reset link</span>

                @if (session('status'))
                    <span style="color: green; font-weight: 600">{{ session('status') }}</span>
                @endif

                <input type="email" name="email" placeholder="Email" required>
                @error('email')
                    <span style="color: red; font-weight: 600">{{ $message }}</span>
                @enderror

                <button type="submit">Send Reset Link</button>
                <a href="/login" style="border: 5px solid #512da8; border-radius:10px; color:white; background-color: #512da8; font-weight:600; font-size: small; padding:5px 25px 5px 25px">Go back to Login page</a>
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