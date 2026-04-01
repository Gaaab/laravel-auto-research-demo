<?php

/*
|--------------------------------------------------------------------------
| API Routes — DO NOT MODIFY
|--------------------------------------------------------------------------
| The endpoint signature is fixed. The agent can only change the controller.
|
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index']);
