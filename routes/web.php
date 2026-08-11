<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DashboardApiController;

// Login view route
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');

// Protected main Dashboard view route
Route::get('/', function () {
    return view('dashboard');
})->middleware('auth.dashboard');

// Session-based API routes (defined here in web.php to support sessions)
Route::prefix('api')->group(function () {
    // Public Authentication
    Route::post('/login', [AuthController::class, 'login']);

    // Dashboard Frontend AJAX Requests (Session Protected)
    Route::middleware(['auth.dashboard'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Attendance Queries
        Route::get('/attendance/today', [DashboardApiController::class, 'todayRecords']);
        Route::get('/attendance/inside', [DashboardApiController::class, 'currentlyInside']);
        Route::get('/attendance/history', [DashboardApiController::class, 'history']);
        Route::get('/attendance/person/{name}', [DashboardApiController::class, 'personHistory']);

        // RTSP Camera Configs
        Route::get('/cameras/config', [DashboardApiController::class, 'getCameras']);
        Route::post('/cameras/config', [DashboardApiController::class, 'addCamera']);
        Route::delete('/cameras/config/{id}', [DashboardApiController::class, 'deleteCamera']);

        // Shifts
        Route::get('/shifts', [DashboardApiController::class, 'getShifts']);
        Route::post('/shifts', [DashboardApiController::class, 'saveShift']);
        Route::delete('/shifts/{name}', [DashboardApiController::class, 'deleteShift']);

        // Face Registration & Deletion
        Route::get('/people', [DashboardApiController::class, 'getPeople']);
        Route::post('/register', [DashboardApiController::class, 'registerFace']);
        Route::delete('/people/{name}', [DashboardApiController::class, 'deletePerson']);

        // 30-Day Attendance Report
        Route::get('/report/{name}', [DashboardApiController::class, 'getReport']);
    });
});

