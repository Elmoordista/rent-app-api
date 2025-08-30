<?php
namespace App\Http\Controllers;

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

Route::get('/items/get-items/{id}', [ItemController::class,'getItemsByCategory'])->name('get-items');


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
    Artisan::call('migrate:fresh');
    return 'Database migrated fresh';
});

Route::get('/migrate-db', function() {
    Artisan::call('migrate');
    return 'Database migrated';
});


Route::resources([
   'items' => ItemController::class,
   'user' => UserController::class,
   'category' => CategoryController::class,
   'order' => OrderController::class,
]);

    

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });



