<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/log.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
</head>
<body>
    <div class="container" id="container">
    <div class="form-container sign-up">
    <form action="/create" method="POST">
        @csrf
        <h1>Create Account</h1>
        <div class="social-icons">
           <a href="{{ route('oauth.redirect', 'google') }}" class="icon"><i class="fa-brands fa-google"></i></a>
            <a href="{{ route('oauth.redirect', 'github') }}" class="icon"><i class="fa-brands fa-github"></i></a>
        </div>
        <span> or use your email for registeration </span>
        <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" required>
        @error('username')
        <span style="color: red; font-weight: 600">{{ $message }}</span>
        @enderror
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
        @error('email')
        <span style="color: red; font-weight: 600">{{ $message }}</span>
        @enderror
        <input type="password" name="password"  placeholder="Password" required>
        @error('password')
        <span style="color: red; font-weight: 600">{{ $message }}</span>
        @enderror
        <input type="password" name="confirm-password" placeholder="Confirm Password" required>
        @error('confirm-password')
         <span style="color: red; font-weight: 600">{{$message}}</span>       
        @enderror
        <input type="tel" name="tel" value="{{ old('tel') }}" placeholder="Phone Number" required>
        @error('tel')
        <span style="color: red; font-weight: 600">The telephone number already exist</span>        
        @enderror
        <button type="submit">Sign Up</button>
    </form>
    </div>
    <div class="form-container sign-in">
    <form action="/login" method="POST">
        @csrf
        <h1>Log In</h1>
        <div class="social-icons">
            <a href="{{ route('oauth.redirect', 'google') }}" class="icon"><i class="fa-brands fa-google"></i></a>
            <a href="{{ route('oauth.redirect', 'github') }}" class="icon"><i class="fa-brands fa-github"></i></a>
        </div>
        <span> or use your email for logging in </span>
        @error('login')
        <span style="color: red ; font-weight: 600;">{{ $message }}</span>
        @enderror
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
        <input type="password" name="password" value="{{ old('password') }}" placeholder="Password" required>
        @error('login')
        <a href="/forget-password" style="color: blue;">Forgot password?</a>
        @enderror
        <button type="submit">Log In</button>
    </form>
    </div>
    <div class="toggle-container">
        <div class="toggle">
            <div class="toggle-panel toggle-left">
                <h1>Welcome Back!</h1>
                <p>Enter your informations to login</p>
                <button class="hidden" id="login">Log In</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Hello!</h1>
                <p>Register with your informations</p>
                <button class="hidden" id="register">Sign Up</button>
            </div>
        </div>
    </div>
    </div>
    <script src="js/log.js"></script>
</body>
</html>