<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PushApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

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
