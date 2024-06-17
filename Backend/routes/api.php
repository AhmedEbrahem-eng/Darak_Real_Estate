<?php

use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\PropertyController;
use App\Http\Controllers\api\ReportUserController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\ReportPropertyController;
use App\Http\Controllers\api\WishlistController;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyTypeController;

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

Route::apiResource('property-types', PropertyTypeController::class);

Route::prefix('properties')->group(function(){
    Route::get('/',[PropertyController::class,'index']);
    Route::get('/{id}',[PropertyController::class,'show']);
    Route::get('latest-rent/{typeId}',[PropertyController::class,'showLatestRent']);
    Route::get('latest-sell/{typeId}',[PropertyController::class,'showLatestSell']);
    Route::post('/',[PropertyController::class,'store']);
    Route::get('/search/filter',[PropertyController::class,'search']);
});
Route::prefix('notifications')->group(function(){
    Route::get('/landlord/{landlordId}',[NotificationController::class,'showLandlordNotifications']);
    Route::get('/renter/{renterId}',[NotificationController::class,'showRenterNotifications']);
    Route::put('/{id}/type',[NotificationController::class,'updateType']);
});
Route::prefix('wishlist')->group(function(){
    Route::get('/{id}',[WishlistController::class,'show']);
    Route::post('/{id}',[WishlistController::class,'store']);
});
