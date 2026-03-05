<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\Web\UserWebController;
use App\Presentation\Http\Controllers\Web\ProductWebController;
use App\Presentation\Http\Controllers\Web\Auth\LoginController;
use App\Presentation\Http\Controllers\Web\Auth\LogoutController;

Route::get('/', fn() => redirect('/login'));

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', LogoutController::class);

Route::middleware('auth')->group(function () {
    Route::get('/users', [UserWebController::class, 'index']);
    Route::get('/users/create', [UserWebController::class, 'create']);
    Route::post('/users', [UserWebController::class, 'store']);
    Route::get('/users/{id}', [UserWebController::class, 'show']);
    Route::get('/users/{id}/edit', [UserWebController::class, 'edit']);
    Route::put('/users/{id}', [UserWebController::class, 'update']);
    Route::delete('/users/{id}', [UserWebController::class, 'destroy']);

    Route::get('/products', [ProductWebController::class, 'index']);
    Route::get('/products/create', [ProductWebController::class, 'create']);
    Route::post('/products', [ProductWebController::class, 'store']);
    Route::get('/products/{id}', [ProductWebController::class, 'show']);
    Route::get('/products/{id}/edit', [ProductWebController::class, 'edit']);
    Route::put('/products/{id}', [ProductWebController::class, 'update']);
    Route::delete('/products/{id}', [ProductWebController::class, 'destroy']);
});