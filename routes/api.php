<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/products', [ProductController::class, 'index']);
Route::get('/cart', [CartController::class, 'index']);
Route::put('/cart', [CartController::class, 'update']);
Route::post('/cart/checkout', [CartController::class, 'checkout']);
Route::post('/order/payment', [OrderController::class, 'paymentOrder']);
Route::post('/order/request-cancel', [OrderController::class, 'cancelOrder']);