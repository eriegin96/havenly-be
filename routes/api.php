<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\RoomTypeController;
use App\Http\Controllers\Api\QueryController;

// Admin Controllers
use App\Http\Controllers\Api\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Api\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Api\Admin\RoomTypeController as AdminRoomTypeController;
use App\Http\Controllers\Api\Admin\RoomImageController as AdminRoomImageController;
use App\Http\Controllers\Api\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Admin\QueryController as AdminQueryController;

// User Controllers
use App\Http\Controllers\Api\User\BookingController as UserBookingController;



// Public routes
Route::prefix('auth')->group(function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);
});

// Public routes for facilities, features, and room types
Route::get('/facilities', [FacilityController::class, 'index']);
Route::get('/facilities/{id}', [FacilityController::class, 'show']);
Route::get('/features', [FeatureController::class, 'index']);
Route::get('/features/{id}', [FeatureController::class, 'show']);
Route::get('/room-types', [RoomTypeController::class, 'index']);
Route::get('/room-types/{id}', [RoomTypeController::class, 'show']);

// Public contact/query route
Route::post('/contact', [QueryController::class, 'store']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
  // Authentication routes
  Route::prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
  });

  // User routes (for regular users)
  Route::middleware('role:user')->prefix('user')->group(function () {
    // Booking management
    Route::get('/bookings', [UserBookingController::class, 'index']);
    Route::post('/bookings', [UserBookingController::class, 'store']);
    Route::get('/bookings/statistics', [UserBookingController::class, 'statistics']);
    Route::get('/bookings/{id}', [UserBookingController::class, 'show']);
    Route::post('/bookings/{id}/cancel', [UserBookingController::class, 'cancel']);
  });
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
  // Facilities management
  Route::get('/facilities', [AdminFacilityController::class, 'index']);
  Route::post('/facilities', [AdminFacilityController::class, 'store']);
  Route::get('/facilities/{id}', [AdminFacilityController::class, 'show']);
  Route::put('/facilities/{id}', [AdminFacilityController::class, 'update']);
  Route::delete('/facilities/{id}', [AdminFacilityController::class, 'destroy']);

  // Features management
  Route::get('/features', [AdminFeatureController::class, 'index']);
  Route::post('/features', [AdminFeatureController::class, 'store']);
  Route::get('/features/{id}', [AdminFeatureController::class, 'show']);
  Route::put('/features/{id}', [AdminFeatureController::class, 'update']);
  Route::delete('/features/{id}', [AdminFeatureController::class, 'destroy']);

  // Room Types management
  Route::get('/room-types', [AdminRoomTypeController::class, 'index']);
  Route::post('/room-types', [AdminRoomTypeController::class, 'store']);
  Route::get('/room-types/{id}', [AdminRoomTypeController::class, 'show']);
  Route::put('/room-types/{id}', [AdminRoomTypeController::class, 'update']);
  Route::delete('/room-types/{id}', [AdminRoomTypeController::class, 'destroy']);

  // Image Upload & Management
  Route::post('/images/upload', [AdminRoomImageController::class, 'uploadImage']);
  Route::get('/images', [AdminRoomImageController::class, 'getAllImages']);
  Route::delete('/images', [AdminRoomImageController::class, 'deleteImageFile']);

  // Room Images management
  Route::get('/room-images', [AdminRoomImageController::class, 'index']);
  Route::post('/room-images', [AdminRoomImageController::class, 'store']);
  Route::get('/room-images/room-type/{roomTypeId}', [AdminRoomImageController::class, 'getByRoomType']);
  Route::get('/room-images/{id}', [AdminRoomImageController::class, 'show']);
  Route::put('/room-images/{id}', [AdminRoomImageController::class, 'update']);
  Route::delete('/room-images/{id}', [AdminRoomImageController::class, 'destroy']);
  Route::post('/room-images/{id}/set-thumbnail', [AdminRoomImageController::class, 'setThumbnail']);

  // Rooms management
  Route::get('/rooms', [AdminRoomController::class, 'index']);
  Route::post('/rooms', [AdminRoomController::class, 'store']);
  Route::get('/rooms/available', [AdminRoomController::class, 'getAvailableRooms']);
  Route::get('/rooms/room-type/{roomTypeId}', [AdminRoomController::class, 'getByRoomType']);
  Route::get('/rooms/{id}', [AdminRoomController::class, 'show']);
  Route::put('/rooms/{id}', [AdminRoomController::class, 'update']);
  Route::delete('/rooms/{id}', [AdminRoomController::class, 'destroy']);

  // Booking management
  Route::get('/bookings', [AdminBookingController::class, 'index']);
  Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
  Route::put('/bookings/{id}', [AdminBookingController::class, 'update']);
  Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy']);
  Route::post('/bookings/{id}/assign-room', [AdminBookingController::class, 'assignRoom']);

  // Query management
  Route::get('/queries', [AdminQueryController::class, 'index']);
  Route::get('/queries/{id}', [AdminQueryController::class, 'show']);
  Route::delete('/queries/{id}', [AdminQueryController::class, 'destroy']);
  Route::delete('/queries', [AdminQueryController::class, 'destroyMultiple']);
  Route::put('/queries/status', [AdminQueryController::class, 'updateReadStatusMultiple']);
  Route::put('/queries/{id}/status', [AdminQueryController::class, 'updateReadStatus']);
});
