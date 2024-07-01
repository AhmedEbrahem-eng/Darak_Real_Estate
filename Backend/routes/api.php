<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReportUserController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReportPropertyController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PropertyTypeController;
use App\Http\Controllers\Api\AmenityController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TourController;
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

// Registration routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Forget password routes
Route::post('forget-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('reset-password', [ResetPasswordController::class, 'reset']);

// Social login routes
Route::get('/auth/google', [SocialLoginController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialLoginController::class, 'handleGoogleCallback']);
Route::get('/auth/facebook', [SocialLoginController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialLoginController::class, 'handleFacebookCallback']);

Route::group([
    'middleware'=> ['auth:sanctum', 'checkTokenExpiry']
], function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::get('logout', [AuthController::class, 'logout']);
});

Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::delete('/', [UserController::class, 'delete']);
    Route::get('/details',[UserController::class, 'show']);
    Route::put('/updatePassword', [UserController::class, 'updatePassword']);
    Route::put('/updateName', [UserController::class, 'updateName']);
    Route::put('/updatePhone', [UserController::class, 'updatePhone']);
    Route::put('/updateAvatar', [UserController::class, 'updateAvatar']);
});
Route::prefix('report-users')->group(function(){
    Route::get('/',[ReportUserController::class,'index']);
    Route::delete('deleteReport/{id}',[ReportUserController::class,'deleteReport']);
    Route::delete('deleteLandlord/{id}',[ReportUserController::class,'deleteUser']);
    Route::post('/', [ReportUserController::class, 'store']);
});

Route::prefix('report-properties')->group(function(){
    Route::get('/',[ReportPropertyController::class,'index']);
    Route::delete('deleteReport/{id}',[ReportPropertyController::class,'deleteReport']);
    Route::delete('deleteProperty/{id}',[ReportPropertyController::class,'deleteProperty']);
    Route::post('/', [ReportPropertyController::class, 'store']);
});

Route::apiResource('property-types', PropertyTypeController::class);

Route::prefix('properties')->group(function(){
    Route::get('/',[PropertyController::class,'index']);
    Route::get('/user-properties',[PropertyController::class,'showUserProperties'])->middleware('auth:sanctum');
    Route::get('/{slug}',[PropertyController::class,'show']);
    Route::get('latest-rent/{typeId}',[PropertyController::class,'showLatestRent']);
    Route::get('latest-sell/{typeId}',[PropertyController::class,'showLatestSell']);
    Route::post('/',[PropertyController::class,'store'])->middleware('auth:sanctum');
    Route::get('/search/filter',[PropertyController::class,'search']);
    Route::put('/{id}',[PropertyController::class,'update']);
    Route::delete('/{id}',[PropertyController::class,'deleteProperty']);

});
Route::delete('images/{imageId}', [ImageController::class, 'deleteImage']);

Route::prefix('notifications')->middleware(['auth:sanctum', 'checkTokenExpiry'])->group(function(){
    Route::get('/landlord',[NotificationController::class,'showLandlordNotifications']);
    Route::get('/renter',[NotificationController::class,'showRenterNotifications']);
    Route::put('/{id}/type',[NotificationController::class,'updateType']);
    Route::delete('/{id}',[NotificationController::class,'deleteNotification']);
});
Route::prefix('wishlist')->middleware(['auth:sanctum', 'checkTokenExpiry'])->group(function(){
    Route::get('',[WishlistController::class,'show']);
    Route::post('',[WishlistController::class,'store']);
    Route::delete('/{id}',[WishlistController::class,'delete']);
});

// Overview Admin Page ROUTE
Route::prefix('dashboard')->group(function () {
    Route::get('/home', [DashboardController::class, 'overview']);
});

// Amenity Admin Page ROUTE
Route::prefix('amenities')->group(function () {
    Route::get('/', [AmenityController::class, 'index']);
    Route::post('/', [AmenityController::class, 'store']);
    Route::get('/{slug}', [AmenityController::class, 'show']);
    Route::put('/{slug}', [AmenityController::class, 'update']);
    Route::delete('/{slug}', [AmenityController::class, 'destroy']);
});

Route::get('reviews', [ReviewController::class, 'show']);

Route::prefix('tour')->middleware(['auth:sanctum', 'checkTokenExpiry'])->group(function () {
    Route::post('/', [TourController::class, 'send_request']);
    Route::get('/', [TourController::class, 'getUserTours']);
    Route::get('/{status}', [TourController::class, 'getToursByStatus']);
    Route::delete('/{tourId}', [TourController::class, 'deleteTour']);
});
Route::post('/tours/{id}/approve', [TourController::class, 'approveTour']);
Route::post('/tours/{id}/decline', [TourController::class, 'declineTour']);

Route::get('locations',[LocationController::class,'index']);

// Admin Dashboard Routes
Route::group([
    'middleware'=> ['auth:sanctum', 'checkTokenExpiry', 'admin']
], function () {
    Route::prefix('admin')->group(function () {

    });

});


