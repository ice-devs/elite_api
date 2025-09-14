<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

Route::get('/orders/{id?}', [OrderController::class, 'getOrders']);

Route::post('/orders', [OrderController::class, 'createOrder']);

// Route::post('/orders/{updateStatus}', [OrderController::class, 'updateStatus']);

// Route::post('/orders/{payBalance}', [OrderController::class, 'updateStatus']);


Route::get('/products/{id?}', [ProductController::class, 'getProducts']);

Route::post('/products', [ProductController::class, 'createProduct']);


?>
