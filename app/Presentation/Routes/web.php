<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\Web\UserWebController;

Route::get('/', fn() => redirect('/users'));

Route::get('/users', [UserWebController::class, 'index']);
Route::get('/users/create', [UserWebController::class, 'create']);
Route::post('/users', [UserWebController::class, 'store']);
Route::get('/users/{id}', [UserWebController::class, 'show']);
Route::get('/users/{id}/edit', [UserWebController::class, 'edit']);
Route::put('/users/{id}', [UserWebController::class, 'update']);
Route::delete('/users/{id}', [UserWebController::class, 'destroy']);