<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\CarritoProducto;
use App\Models\Pedidos; // <--- ¡IMPORTACIÓN CORREGIDA! Usa el modelo Pedidos (plural)
use App\Models\PedidosProductos; // Tu modelo para los ítems del pedido
use App\Models\Direcciones; // <--- ¡IMPORTACIÓN CORREGIDA! Usa el modelo Direcciones (plural)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarritoController extends Controller
{
    public function getCarrito(Request $request)
    {
        $usuario = $request->user();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $carrito = Carrito::where('usuario_id', $usuario->id)
                            ->where('estado', 'activo')
                            ->first();

        if (!$carrito) {
            return response()->json(['message' => 'Tu carrito está vacío.', 'items' => [], 'total' => 0.0], 200);
        }

        $items = $carrito->productos()->with('producto')->get()->map(function ($carritoProducto) {
            $producto = $carritoProducto->producto;
            return [
                'id' => $producto->id,
                'titulo' => $producto->titulo,
                'imagen' => $producto->imagen,
                'precio' => (float) $producto->precio,
                'cantidad_en_carrito' => $carritoProducto->cantidad,
                'stock_disponible' => $producto->stock,
                'subtotal' => (float) $producto->precio * $carritoProducto->cantidad,
            ];
        });

        $total = $items->sum('subtotal');

        return response()->json([
            'message' => 'Carrito cargado con éxito',
            'items' => $items,
            'total' => (float) $total,
        ]);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $usuario = $request->user();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $producto = Producto::find($request->producto_id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $carrito = Carrito::firstOrCreate(
            ['usuario_id' => $usuario->id, 'estado' => 'activo'],
            ['estado' => 'activo']
        );

        $carritoProducto = CarritoProducto::where('carrito_id', $carrito->id)
                                          ->where('producto_id', $producto->id)
                                          ->first();

        $cantidadToAdd = $request->cantidad;
        $currentQuantityInCart = $carritoProducto ? $carritoProducto->cantidad : 0;
        $totalQuantityAfterAdd = $currentQuantityInCart + $cantidadToAdd;

        if ($producto->stock < $totalQuantityAfterAdd) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible para la cantidad solicitada. Stock actual: ' . $producto->stock . '. Cantidad en carrito: ' . $currentQuantityInCart,
                'stock_disponible' => $producto->stock,
                'cantidad_en_carrito' => $currentQuantityInCart
            ], 400);
        }

        if ($carritoProducto) {
            $carritoProducto->cantidad = $totalQuantityAfterAdd;
            $carritoProducto->save();
            $message = 'Cantidad del producto actualizada en el carrito.';
        } else {
            CarritoProducto::create([
                'carrito_id' => $carrito->id,
                'producto_id' => $producto->id,
                'cantidad' => $cantidadToAdd,
            ]);
            $message = 'Producto añadido al carrito.';
        }

        return response()->json([
            'message' => $message,
            'nuevo_stock' => $producto->stock,
        ]);
    }

    public function updateCartItem(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:0',
        ]);

        $usuario = $request->user();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $carrito = Carrito::where('usuario_id', $usuario->id)
                            ->where('estado', 'activo')
                            ->first();

        if (!$carrito) {
            return response()->json(['message' => 'Carrito no encontrado.'], 404);
        }

        $carritoProducto = CarritoProducto::where('carrito_id', $carrito->id)
                                          ->where('producto_id', $request->producto_id)
                                          ->first();

        if (!$carritoProducto) {
            return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
        }

        $producto = Producto::find($request->producto_id);

        if ($request->cantidad === 0) {
            $carritoProducto->delete();
            return response()->json(['message' => 'Producto eliminado del carrito.']);
        }

        if ($producto->stock < $request->cantidad) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible para esta cantidad. Stock actual: ' . $producto->stock,
                'stock_disponible' => $producto->stock,
            ], 400);
        }

        $carritoProducto->cantidad = $request->cantidad;
        $carritoProducto->save();

        return response()->json([
            'message' => 'Cantidad del producto actualizada.',
            'nueva_cantidad' => $carritoProducto->cantidad,
            'stock_disponible' => $producto->stock,
        ]);
    }

    public function removeCartItem(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
        ]);

        $usuario = $request->user();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $carrito = Carrito::where('usuario_id', $usuario->id)
                            ->where('estado', 'activo')
                            ->first();

        if (!$carrito) {
            return response()->json(['message' => 'Carrito no encontrado.'], 404);
        }

        $carritoProducto = CarritoProducto::where('carrito_id', $carrito->id)
                                          ->where('producto_id', $request->producto_id)
                                          ->first();

        if (!$carritoProducto) {
            return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
        }

        $carritoProducto->delete();

        return response()->json(['message' => 'Producto eliminado del carrito.']);
    }

    /**
     * Procesa el pago y convierte el carrito en un pedido.
     */
    public function processPayment(Request $request)
    {
        $usuario = $request->user();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $request->validate([
            // Asegúrate de que la tabla de direcciones sea 'direcciones'
            'direccion_id' => 'required|exists:direcciones,id',
        ]);

        // Usa el modelo 'Direcciones' (plural) aquí
        $direccion = Direcciones::find($request->direccion_id);

        if (!$direccion || $direccion->usuario_id !== $usuario->id) {
            return response()->json(['message' => 'Dirección de envío no válida o no encontrada.', 'status' => 'invalid_address'], 400);
        }

        $carrito = Carrito::where('usuario_id', $usuario->id)
                            ->where('estado', 'activo')
                            ->first();

        if (!$carrito) {
            return response()->json(['message' => 'Tu carrito está vacío.', 'status' => 'empty_cart'], 400);
        }

        $itemsEnCarrito = $carrito->productos()->with('producto')->get();

        if ($itemsEnCarrito->isEmpty()) {
            return response()->json(['message' => 'Tu carrito está vacío.', 'status' => 'empty_cart'], 400);
        }

        $totalPedido = 0;
        $erroresStock = [];

        DB::beginTransaction();

        try {
            foreach ($itemsEnCarrito as $item) {
                $producto = Producto::lockForUpdate()->find($item->producto_id);

                if (!$producto) {
                    $erroresStock[] = ['producto_id' => $item->producto_id, 'titulo' => 'Desconocido', 'error' => 'Producto no encontrado.'];
                    continue;
                }

                if ($producto->stock < $item->cantidad) {
                    $erroresStock[] = ['producto_id' => $producto->id, 'titulo' => $producto->titulo, 'stock_disponible' => $producto->stock, 'cantidad_requerida' => $item->cantidad];
                }
                $totalPedido += $producto->precio * $item->cantidad;
            }

            if (!empty($erroresStock)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No hay suficiente stock para algunos productos en tu carrito.',
                    'stock_errors' => $erroresStock,
                    'status' => 'stock_error'
                ], 400);
            }

            // --- ¡CAMBIO CRÍTICO AQUÍ! ---
            // Usa el modelo 'Pedidos' (plural) para crear el pedido
            $pedido = Pedidos::create([
                'usuario_id' => $usuario->id,
                'direccion_id' => $direccion->id,
                'total' => $totalPedido,
                'estado' => 'pendiente',
                'fecha_pedido' => now(),
            ]);

            foreach ($itemsEnCarrito as $item) {
                $producto = Producto::find($item->producto_id);

                // Usa el modelo 'PedidoProductos' (plural) para los ítems del pedido
                PedidosProductos::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $producto->precio * $item->cantidad,
                ]);

                $producto->stock -= $item->cantidad;
                $producto->save();
            }

            $carrito->estado = 'completado';
            $carrito->save();

            DB::commit();

            return response()->json([
                'message' => 'Compra realizada con éxito. Tu pedido ha sido generado.',
                'pedido_id' => $pedido->id,
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar el pago: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Hubo un error al procesar tu compra. Por favor, intenta de nuevo.', 'error' => $e->getMessage()], 500);
        }
    }
}