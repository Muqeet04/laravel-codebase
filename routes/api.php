<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PushApiController;
use App\Http\Controllers\Api\DashboardApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::post('/login', [AuthController::class, 'login']);

// Python Local Engine Client Push Sync Endpoints (X-Push-Key Protected)
Route::middleware(['push.key'])->group(function () {
    Route::post('/push/event', [PushApiController::class, 'pushEvent']);
    Route::post('/push/sighting', [PushApiController::class, 'pushSighting']);
    Route::post('/push/snapshot', [PushApiController::class, 'pushSnapshot']);
    Route::post('/push/register-face', [PushApiController::class, 'pushRegisterFace']);
    Route::delete('/push/people/{name}', [PushApiController::class, 'pushDeletePerson']);
    Route::post('/push/cameras-config', [PushApiController::class, 'pushCamerasConfig']);
    Route::post('/push/shifts', [PushApiController::class, 'pushShift']);
    Route::delete('/push/shifts/{name}', [PushApiController::class, 'pushDeleteShift']);
});

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
});
