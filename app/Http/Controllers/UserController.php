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
    public function home()
    {
        return view('home');
    }
    public function create(Request $request)
    {
        $request ->validate([
        'username' => 'required|string|unique:users,username',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'confirm-password' => 'required|same:password',
        'tel' => 'required|min:8'
        ]);
        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password'=>Hash::make($request->password),
            'tel'=>$request->tel
        ]);
        return redirect('/home'); //or wtver the 'home' will be
    }
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
        return back()->withErrors([
            'login' => 'Email or password incorrect',
        ]);
    }

    return redirect('/home'); //or wtver the 'home' will be
    }
}
