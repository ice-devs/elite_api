<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

Route::get('/orders/{id?}', [OrderController::class, 'getOrders']);

Route::post('/orders', [OrderController::class, 'createOrder']);

Route::post('/orders/{updateStatus}', [OrderController::class, 'updateStatus']);

Route::post('/orders/{payBalance}', [OrderController::class, 'payBalance']);

Route::post('/orders/{customerAction}', [OrderController::class, 'customerAction']);


Route::get('/products/{id?}', [ProductController::class, 'getProducts']);
Route::post('/products', [ProductController::class, 'createProduct']);
Route::post('/products/{updateProduct}', [ProductController::class, 'updateProduct']);
Route::post('/products/{updateStock}', [ProductController::class, 'updateStock']);
Route::post('/products/{updateStatus}', [ProductController::class, 'updateStatus']);


?>
