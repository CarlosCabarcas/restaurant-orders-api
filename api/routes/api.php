<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/orders',  [OrderController::class, 'index']);

Route::post('/orders', [OrderController::class, 'store']);

Route::post('/orders/{id}/advance', [OrderController::class, 'advance']);

Route::get('/orders/{id}', [OrderController::class, 'show']);
