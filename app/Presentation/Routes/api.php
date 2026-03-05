<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\Api\UserApiController;
use App\Presentation\Http\Controllers\Api\ProductApiController;

Route::get('/users', [UserApiController::class, 'index']);
Route::post('/users', [UserApiController::class, 'store']);
Route::get('/users/{id}', [UserApiController::class, 'show']);
Route::put('/users/{id}', [UserApiController::class, 'update']);
Route::delete('/users/{id}', [UserApiController::class, 'destroy']);

Route::get('/products', [ProductApiController::class, 'index']);
Route::post('/products', [ProductApiController::class, 'store']);
Route::get('/products/{id}', [ProductApiController::class, 'show']);
Route::put('/products/{id}', [ProductApiController::class, 'update']);
Route::delete('/products/{id}', [ProductApiController::class, 'destroy']);