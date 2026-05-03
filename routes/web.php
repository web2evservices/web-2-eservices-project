<?php

use App\Http\Controllers\ResetPassController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',[UserController::class,'LoginView']);
route::post('/login',[UserController::class,'Login']);
route::post('/create',[UserController::class,'create']);
route::get('/home',[UserController::class,'home']);

Route::get('/forget-password',[ResetPassController::class, 'forgotView']);
Route::post('/forget-password',[ResetPassController::class, 'sendResetLink']);
Route::get('/reset-password/{token}',[ResetPassController::class, 'resetView'])->name('password.reset');
Route::post('/reset-password',[ResetPassController::class, 'resetPassword'])->name('password.update');

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('oauth.redirect');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('oauth.callback');