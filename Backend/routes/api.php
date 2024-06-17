<?php

use App\Http\Controllers\api\ReportUserController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\ReportPropertyController;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::delete('/{id}', [UserController::class, 'delete']);
});
Route::prefix('report-users')->group(function(){
    Route::get('/',[ReportUserController::class,'index']);
    Route::delete('deleteReport/{id}',[ReportUserController::class,'deleteReport']);
    Route::delete('deleteLandlord/{id}',[ReportUserController::class,'deleteUser']);
});

Route::prefix('report-properties')->group(function(){
    Route::get('/',[ReportPropertyController::class,'index']);
    Route::delete('deleteReport/{id}',[ReportPropertyController::class,'deleteReport']);
    Route::delete('deleteProperty/{id}',[ReportPropertyController::class,'deleteProperty']);
});

Route::prefix('dashboard')->group(function () {
    Route::get('/counts', [DashboardController::class, 'getCounts']);
});