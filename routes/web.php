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


use App\Http\Controllers\Office\OfficeDashboardController;
use App\Http\Controllers\Office\ServiceCategoryController;
use App\Http\Controllers\Office\ServiceController;
use App\Http\Controllers\Office\OfficeProfileController;
use App\Http\Controllers\Office\QrCodeController;
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


Route::get('/otp-verify',  [UserController::class, 'otpView']);
Route::post('/otp-verify', [UserController::class, 'otpVerify']);
Route::post('/otp-resend', [UserController::class, 'otpResend']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/forget-password', [ResetPassController::class, 'forgotView']);
Route::post('/forget-password', [ResetPassController::class, 'sendResetLink']);

Route::get('/reset-password/{token}', [ResetPassController::class, 'resetView'])
->name('password.reset');

Route::post('/reset-password', [ResetPassController::class, 'resetPassword'])
->name('password.update');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('oauth.callback');
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('oauth.redirect');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    Route::resource('municipalities', MunicipalityController::class);
    Route::resource('offices', OfficeController::class);
    Route::get('users', [AdminUserController::class, 'index'])
        ->name('admin.users.index');
    Route::patch('users/{id}/toggle', [AdminUserController::class, 'toggle'])
        ->name('admin.users.toggle');
    Route::patch('users/{id}/role', [UserController::class, 'updateRole'])
    ->name('admin.users.role');
    Route::delete('/users/{id}/delete', [UserController::class, 'destroy'])
        ->name('admin.users.delete');
    Route::get('analytics', [AnalyticsController::class, 'index'])
        ->name('admin.analytics');
});
Route::middleware(['auth'])->group(function () {

    Route::get('/user/dashboard', [UserController::class, 'dashboard'])
        ->name('user.dashboard');
});


// =============================================
// PERSON 3: Government Office Routes
// =============================================
Route::prefix('office')->middleware(['auth', 'office'])->name('office.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [OfficeDashboardController::class, 'index'])
        ->name('dashboard');

    // Office Profile
    Route::get('/profile/edit', [OfficeProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profile', [OfficeProfileController::class, 'update'])
        ->name('profile.update');

    // Service Categories (manual routes to avoid model binding issues with Service_Categories)
    Route::get('/categories',          [ServiceCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create',   [ServiceCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories',         [ServiceCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit',[ServiceCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}',     [ServiceCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}',  [ServiceCategoryController::class, 'destroy'])->name('categories.destroy');

    // Services
    Route::get('/services',          [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create',   [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services',         [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{id}/edit',[ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}',     [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}',  [ServiceController::class, 'destroy'])->name('services.destroy');

    // QR Codes
    Route::get('/qr/{requestId}',          [QrCodeController::class, 'show'])->name('qr.show');
    Route::get('/qr/{requestId}/download', [QrCodeController::class, 'download'])->name('qr.download');
});

// Public QR tracking page — no login required
Route::get('/track/{qrCode}', function ($qrCode) {
    $request = \App\Models\ServiceRequests::where('qr_code', $qrCode)
        ->with(['service.office', 'citizen'])
        ->firstOrFail();
    return view('office.public.track', compact('request'));
})->name('requests.track');
