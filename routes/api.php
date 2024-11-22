<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Learning\LearningController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Force all routes in this file to be JSON responses
Route::middleware('api')->group(function () {

    // Auth routes
    Route::post('login', [LoginController::class, 'login'])->name('api.login');

    // Device management
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('devices')->group(function () {
            Route::get('/', [DeviceController::class, 'index']);
            Route::post('/register', [DeviceController::class, 'register']);
            Route::delete('/{deviceId}', [DeviceController::class, 'remove']);
        });
    });

    // Learning module routes
    Route::middleware(['auth:sanctum', 'device.verify'])->group(function () {
        Route::prefix('learning')->group(function () {
            Route::get('/check-access', [LearningController::class, 'checkAccess']);
            Route::get('/courses', [LearningController::class, 'listCourses']);
            Route::get('/courses/{courseId}', [LearningController::class, 'getCourse']);
        });
    });
});