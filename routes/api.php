<?php
namespace App\Http\Controllers;

use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

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



Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    Artisan::call('optimize');
    return 'Caches cleared';
});

Route::get('/migrate-db-fresh', function() {
    // Run your app migrations first
    Artisan::call('migrate:fresh', [
        '--force' => true,
    ]);

    // Then run Sanctum migrations
    Artisan::call('migrate', [
        '--path' => 'vendor/laravel/sanctum/database/migrations',
        '--force' => true,
    ]);

    return Artisan::output();
});

Route::get('/migrate-db', function() {
    Artisan::call('migrate', [
        '--force' => true,
    ]);

    return 'Database migrated';
});


Route::post('/user/login', [LoginController::class, 'store'])->name('user-login.store');
Route::post('/user/login-admin', [LoginController::class, 'adminLogin'])->name('user-login.admin');
Route::post('/user/signup', [UserController::class, 'signup'])->name('user.signup');
// Route::get('/create-sqlite', function () {
//     $path = database_path('database.sqlite'); // points to database/database.sqlite

//     if (!file_exists($path)) {
//         // Create empty file
//         file_put_contents($path, '');
//         return 'SQLite database created successfully!';
//     }

//     return 'Database already exists.';
// });

Route::get('/create-sqlite', function () {
    // Use /tmp for ephemeral writable storage on Render Free
    $path = env('DB_DATABASE', '/tmp/database.sqlite');
    $dir = dirname($path);

    try {
        // Ensure directory exists
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Create the SQLite file if it doesn't exist
        if (!file_exists($path)) {
            file_put_contents($path, '');
            return response()->json([
                'status' => 'success',
                'message' => "SQLite database created successfully at: $path"
            ]);
        }

        return response()->json([
            'status' => 'info',
            'message' => "Database already exists at: $path"
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => "Failed to create database: " . $e->getMessage()
        ], 500);
    }
});
Route::post('/user/send-otp', [UserController::class, 'sendOtp'])->name('user.send-otp');
Route::post('/user/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');

Route::middleware('auth:sanctum')->group(function () {

   Route::get('/user/info', [UserController::class, 'getInfo'])->name('user.info');
   Route::get('/user/latest-order', [UserController::class, 'getLatestOrderInfo'])->name('user.latest-order');
   Route::get('/user/favorites', [UserController::class, 'getFavorites'])->name('user.favorites');
   Route::delete('/user/favorites-remove/{id}', [UserController::class, 'removeFavorite'])->name('user.favorites.remove');
   Route::get('/user/profile-settings', [UserController::class, 'getProfileSettings'])->name('user.profile-settings');
   Route::post('/user/update-profile', [UserController::class, 'updateProfile'])->name('user.update-profile');

   Route::get('/order/get-pending', [OrderController::class, 'getPendingOrders'])->name('order.get-pending');
   Route::get('/order/get-booking-details/{booking_id}', [OrderController::class, 'getPendingOrdersDetails'])->name('order.get-pending-details');
   Route::get('/order/get-confirmed-bookings', [OrderController::class, 'getConfirmedOrders'])->name('order.get-confirmed-bookings');
   Route::put('/order/cancel-order/{id}', [OrderController::class, 'cancelOrder'])->name('order.cancel-order');

   Route::get('/booking/get-pendings', [BookingController::class, 'getPendings'])->name('booking.get-pendings');
   Route::post('/booking/upload-proof-of-payment', [BookingController::class, 'uploadProofOfPayment'])->name('payment.upload');
   Route::post('/booking/get-filtered-bookings', [BookingController::class, 'getFilteredBookings'])->name('booking.get-filtered-bookings');

   Route::get('/items/get-items/{id}', [ItemController::class,'getItemsByCategory'])->name('get-items');
   Route::get('/items/get-reviews/{id}', [ItemController::class,'getItemReviews'])->name('get-item-reviews');
   Route::post('/item/add-review/{id}', [ItemController::class, 'addReview'])->name('item.add-review');
   Route::post('/item/add-to-favorite', [ItemController::class, 'addToFavorite'])->name('item.add-to-favorite');

   Route::resources([
      'items' => ItemController::class,
      'user' => UserController::class,
      'category' => CategoryController::class,
      'order' => OrderController::class,
      'login' => LoginController::class,
      'cart' => CartController::class,
      'booking' => BookingController::class,
      'payment-accounts' => PaymentAccountController::class,
   ]);

});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });



