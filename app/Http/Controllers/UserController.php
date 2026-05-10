<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function LoginView()
    {
        return view('login');
    }

    public function dashboard()
    {
        return view('users.dashboard');
    }

    public function create(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirm-password' => 'required|same:password',
            'tel' => 'required|min:8|unique:users,tel'
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tel' => $request->tel
        ]);

        Auth::login($user);
        return redirect()->route('user.dashboard');
    }

    
public function login(Request $request)
{
    if (!Auth::attempt($request->only('email', 'password'))) {
        return back()->withErrors([
            'login' => 'Email or password incorrect',
        ]);
    }

    $user = Auth::user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'office_user') {
        return redirect()->route('office.dashboard');
    } else {
        return redirect()->route('user.dashboard');
    }
}

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}