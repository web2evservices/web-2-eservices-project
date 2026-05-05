<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AnalyticsController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {

    Route::get('dashboard', [AdminDashboardController::class,'index']);

    Route::resource('municipalities', MunicipalityController::class);
    Route::get('municipalities/create', [MunicipalityController::class, 'create'])->name('municipalities.create');
    Route::get('/admin/municipalities/{municipality}/edit', [MunicipalityController::class, 'edit'])->name('municipalities.edit');
    Route::put('/admin/municipalities/{municipality}', [MunicipalityController::class, 'update'])->name('municipalities.update');
    Route::resource('offices', OfficeController::class);
    Route::get('/admin/offices/{office}/edit', [OfficeController::class, 'edit'])->name('offices.edit');
    Route::put('/admin/offices/{office}', [OfficeController::class, 'update'])->name('offices.update');
    Route::get('users', [AdminUserController::class,'index']);
    Route::patch('users/{id}/toggle', [AdminUserController::class,'toggle']);
    Route::get('analytics', [AnalyticsController::class,'index']);
});
