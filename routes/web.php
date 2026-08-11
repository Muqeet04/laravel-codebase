<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Login view route
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');

// Protected main Dashboard view route
Route::get('/', function () {
    return view('dashboard');
})->middleware('auth.dashboard');

