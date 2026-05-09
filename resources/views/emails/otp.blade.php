<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px; }
        .card { background: white; max-width: 480px; margin: auto; padding: 40px;
                border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .otp  { font-size: 42px; font-weight: bold; letter-spacing: 10px;
                color: #512da8; margin: 30px 0; }
        .note { color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Login Verification</h2>
        <p>Use the code below to complete your login. It expires in <strong>10 minutes</strong>.</p>
        <div class="otp">{{ $otp }}</div>
        <p class="note">If you did not attempt to log in, ignore this email.</p>
    </div>
</body>
</html>