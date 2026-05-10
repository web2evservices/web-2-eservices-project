<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function LoginView()
    {
        return view('login');
    }

    // FIX 3: Renamed from home() to match the route which calls 'dashboard'
    public function dashboard()
    {
        return view('users.dashboard');
    }

    public function create(Request $request)
    {
        $request->validate([
            'username'         => 'required|string|unique:users,username',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|min:6',
            'confirm-password' => 'required|same:password',
            'tel'              => 'required|min:8|unique:users,tel',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'tel'      => $request->tel,
        ]);

        Auth::login($user);
        return redirect()->route('user.dashboard');
    }

    
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // FIX 1: Use the $user fetched from DB — do NOT call Auth::user() before logging in
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'Email or password incorrect']);
        }

        // FIX 1: Check 2FA first — if enabled, send OTP and hold off on logging in
        if ($user->two_factor_enabled) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            session([
                '2fa_user_id' => $user->id,
                '2fa_otp'     => bcrypt($otp),
                '2fa_expires' => now()->addMinutes(10)->timestamp,
            ]);

            Mail::to($user->email)->send(new OtpMail($otp));

            return redirect('/otp-verify')->with('status', 'A verification code was sent to your email.');
        }

        // No 2FA — log in directly and redirect based on role
        Auth::login($user, $request->boolean('remember'));

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'office_user') {
        return redirect()->route('office.dashboard');
    } else {
        return redirect()->route('user.dashboard');

    }
    }
    
    public function otpView()
    {
        if (!session('2fa_user_id')) {
            return redirect('/login');
        }
        return view('otp-verify');
    }

    public function otpVerify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $userId  = session('2fa_user_id');
        $otpHash = session('2fa_otp');
        $expires = session('2fa_expires');

        if (!$userId) {
            return redirect('/login')->withErrors(['login' => 'Session expired. Please log in again.']);
        }

        if (now()->timestamp > $expires) {
            session()->forget(['2fa_user_id', '2fa_otp', '2fa_expires']);
            return redirect('/login')->withErrors(['login' => 'OTP expired. Please log in again.']);
        }

        if (!Hash::check($request->otp, $otpHash)) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        $user = User::findOrFail($userId);
        session()->forget(['2fa_user_id', '2fa_otp', '2fa_expires']);

        // FIX 2: Actually log the user in and redirect by role
        Auth::login($user);

           
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'office_user') {
        return redirect()->route('office.dashboard');
    } else {
        return redirect()->route('user.dashboard');
    }
    }

    public function otpResend()
    {
        $userId = session('2fa_user_id');

        if (!$userId) {
            return redirect('/login');
        }

        $user = User::findOrFail($userId);
        $otp  = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            '2fa_otp'     => bcrypt($otp),
            '2fa_expires' => now()->addMinutes(10)->timestamp,
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        return redirect('/otp-verify')->with('status', 'A new code was sent to your email.');
    }


    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}