<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PushApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public health check route
Route::get('/health', [PushApiController::class, 'health']);

// Python Local Engine Client Push Sync Endpoints (X-Push-Key Protected)
Route::middleware(['push.key'])->group(function () {
    Route::post('/push/event', [PushApiController::class, 'pushEvent']);
    Route::post('/push/sighting', [PushApiController::class, 'pushSighting']);
    Route::post('/push/snapshot', [PushApiController::class, 'pushSnapshot']);
    Route::post('/push/register-face', [PushApiController::class, 'pushRegisterFace']);
    Route::delete('/push/people/{name}', [PushApiController::class, 'pushDeletePerson']);
    Route::post('/push/cameras-config', [PushApiController::class, 'pushCamerasConfig']);
    Route::get('/push/cameras-config', [PushApiController::class, 'getPushCameraConfigs']);
    Route::post('/push/shifts', [PushApiController::class, 'pushShift']);
    Route::get('/push/shifts', [PushApiController::class, 'getPushShifts']);
    Route::delete('/push/shifts/{name}', [PushApiController::class, 'pushDeleteShift']);
    
    // Face registration synchronization protocol
    Route::get('/push/pending-registrations', [PushApiController::class, 'getPendingRegistrations']);
    Route::post('/push/complete-registration', [PushApiController::class, 'completeRegistration']);
    Route::post('/push/fail-registration', [PushApiController::class, 'failRegistration']);
    Route::get('/push/active-people', [PushApiController::class, 'getActivePeople']);
});
