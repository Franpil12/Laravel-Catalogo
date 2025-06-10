<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\CategoriaController;


//Routas Publicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//Productos
Route::get('productos', [ProductoController::class, 'index']);
Route::get('productos/{id}', [ProductoController::class, 'show']);
Route::get('categorias', [CategoriaController::class, 'index']);

//Rutasn protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('productos', [ProductoController::class, 'store']) ->middleware(IsAdmin::class);
    Route::put('/productos/{id}', [ProductoController::class, 'update']) ->middleware(IsAdmin::class);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']) ->middleware(IsAdmin::class);
});

