<?php
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MeetingRoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/my-bookings', [BookingController::class, 'index']);
    Route::post('/book-room', [BookingController::class, 'store']); 
    Route::delete('/bookings/{id}', [BookingController::class, 'cancelBooking']);

    Route::get('/meeting-rooms', [MeetingRoomController::class, 'index']); 
    Route::get('/available-rooms', [MeetingRoomController::class, 'availableRooms']); 
  
    Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index']);
    Route::get('/current-subscription', [SubscriptionPlanController::class, 'currentSubscription']);
   
    Route::post('/payment', [PaymentController::class, 'store']);
    // Route::get('/payments', [PaymentController::class, 'getUserPayments']);
});
