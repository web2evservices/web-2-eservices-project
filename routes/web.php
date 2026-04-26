<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',[UserController::class,'LoginView']);
route::post('/login',[UserController::class,'Login']);
route::post('/create',[UserController::class,'create']);
route::get('/home',[UserController::class,'home']);