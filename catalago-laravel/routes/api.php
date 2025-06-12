<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PedidoController; // Asegúrate de que esta línea esté presente
use App\Http\Controllers\DireccionController;

//Routas Publicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//Productos
Route::get('productos', [ProductoController::class, 'index']);
Route::get('productos/{id}', [ProductoController::class, 'show']);
Route::get('categorias', [CategoriaController::class, 'index']);

//Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas de administración de Productos (solo para administradores)
    Route::post('productos', [ProductoController::class, 'store'])->middleware(IsAdmin::class);
    Route::put('/productos/{id}', [ProductoController::class, 'update'])->middleware(IsAdmin::class);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->middleware(IsAdmin::class);

    // Rutas del Carrito
    Route::post('/carrito/add', [CarritoController::class, 'addToCart']);
    Route::get('/carrito', [CarritoController::class, 'getCarrito']);
    Route::put('/carrito/update', [CarritoController::class, 'updateCartItem']);
    Route::delete('/carrito/remove', [CarritoController::class, 'removeCartItem']);

    // NUEVAS RUTAS: Para Pedidos (ahora usando apiResource para incluir POST a /pedidos)
    // Esto reemplaza las dos líneas GET que tenías para pedidos, y añade el POST
    Route::apiResource('pedidos', PedidoController::class)->only(['index', 'show', 'store', 'destroy']);

    // Listar todos los pedidos para el admin
    Route::get('/admin/pedidos', [PedidoController::class, 'indexAdmin'])->middleware(IsAdmin::class);
    // Actualizar el estado de un pedido (PUT/PATCH a /pedidos/{id}/estado)
    Route::put('/admin/pedidos/{pedido}/estado', [PedidoController::class, 'updateEstado'])->middleware(IsAdmin::class);

    // Rutas para Direcciones
    Route::apiResource('direcciones', DireccionController::class);
});

