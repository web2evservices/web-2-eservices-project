<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="/css/log.css">
</head>
<body>
    <div class="container">
        <div class="form-container sign-in">
            <form action="/otp-verify" method="POST">
                @csrf
                <h1>Two-Factor Auth</h1>
                <span>Enter the 6-digit code sent to your email</span>

                @if(session('status'))
                    <span style="color: green; font-weight: 600">{{ session('status') }}</span>
                @endif

                @error('otp')
                    <span style="color: red; font-weight: 600">{{ $message }}</span>
                @enderror

                <input type="text" name="otp" placeholder="_ _ _ _ _ _"
                       maxlength="6" style="letter-spacing: 8px; font-size: 22px;
                       text-align: center;" required autofocus>

                <button type="submit">Verify</button>
                <form action="/otp-resend" method="POST" style="margin-top: 10px">
                    @csrf
                    <button type="submit"
                        style="background: none; border: none; color: #512da8;
                               cursor: pointer; font-size: 14px; text-decoration: underline;">
                        Resend Code
                    </button>
                </form>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle"></div>
            <div class="toggle-panel toggle-right"><h1></h1></div>
        </div>
    </div>
</body>
</html>