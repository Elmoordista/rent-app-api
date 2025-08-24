<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
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


Route::resources([
   'items' => ItemController::class,
   'user' => UserController::class,
   'category' => CategoryController::class,
   'order' => OrderController::class,
]);

    

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });



