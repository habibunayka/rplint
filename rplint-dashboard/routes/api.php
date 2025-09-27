<?php

use App\Http\Controllers\Api\App\AppController;
use App\Http\Controllers\Api\App\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('app/login',[AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('app/home',[AppController::class, 'home']);
    Route::get('app/account',[AppController::class, 'account']);
    Route::get('app/recently', [AppController::class, 'recent']);
    Route::get('app/subject',[AppController::class, 'subject']);
    Route::post('app/logout', [AuthController::class, 'logout']);
});

Route::get('/students', [StudentController::class, 'index']);
Route::put('/students/{id}', [StudentController::class, 'update']);

Route::get('/students/attendances', [AttendanceController::class, 'index']);
Route::get('/students/fingerprint', [AttendanceController::class, 'getFingerprints']);
Route::post('/students/fingerprint', [AttendanceController::class, 'store']);

