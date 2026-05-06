<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ResetPassController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AnalyticsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [UserController::class, 'LoginView']);
Route::post('/login', [UserController::class, 'Login'])->name('login');

Route::post('/create', [UserController::class, 'create']);

Route::post('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/forget-password', [ResetPassController::class, 'forgotView']);
Route::post('/forget-password', [ResetPassController::class, 'sendResetLink']);

Route::get('/reset-password/{token}', [ResetPassController::class, 'resetView'])
    ->name('password.reset');

Route::post('/reset-password', [ResetPassController::class, 'resetPassword'])
    ->name('password.update');

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('oauth.redirect');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('oauth.callback');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    Route::resource('municipalities', MunicipalityController::class);
    Route::resource('offices', OfficeController::class);
    Route::get('users', [AdminUserController::class, 'index'])
        ->name('admin.users.index');
    Route::patch('users/{id}/toggle', [AdminUserController::class, 'toggle'])
        ->name('admin.users.toggle');
    Route::get('analytics', [AnalyticsController::class, 'index'])
        ->name('admin.analytics');
});
Route::middleware(['auth'])->group(function () {

    Route::get('/user/dashboard', [UserController::class, 'dashboard'])
        ->name('user.dashboard');
});