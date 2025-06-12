<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\Pedidos; // Tu modelo de Pedido
use App\Models\PedidosProductos; // Tu modelo para la tabla pivote de productos en pedidos
use App\Models\Direcciones;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // Añadir esta línea para usar Auth::id()

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource (Pedidos del usuario).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $pedidos = Pedidos::where('usuario_id', $user->id)
                         ->with('direccion', 'productos.producto')
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json(['pedidos' => $pedidos], 200);
    }

    /**
     * Display the specified resource (un Pedido específico).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $pedido = Pedidos::where('usuario_id', $user->id)
                         ->where('id', $id)
                         ->with('direccion', 'productos.producto')
                         ->first();

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado o no pertenece a este usuario.'], 404);
        }

        return response()->json(['pedido' => $pedido], 200);
    }

    /**
     * Store a newly created resource in storage (Crear un nuevo pedido desde el carrito).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar la solicitud
        try {
            $request->validate([
                'direccion_id' => 'required|exists:direcciones,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        $user = $request->user();
        $carrito = Carrito::where('usuario_id', $user->id)
                          ->where('estado', 'activo')
                          ->first();

        if (!$carrito || $carrito->productos->isEmpty()) {
            return response()->json(['message' => 'Tu carrito está vacío o no existe.'], 400);
        }

        $productos_a_comprar = [];
        $stock_errors = [];
        $total_pedido = 0;

        DB::beginTransaction();
        try {
            foreach ($carrito->productos as $item_carrito) {
                $producto = Producto::lockForUpdate()->find($item_carrito->producto_id);

                if (!$producto) {
                    DB::rollBack();
                    return response()->json(['message' => 'Producto no encontrado en la base de datos: ' . $item_carrito->producto_id], 404);
                }

                if ($producto->stock < $item_carrito->cantidad) {
                    $stock_errors[] = [
                        'titulo' => $producto->titulo,
                        'stock_disponible' => $producto->stock,
                        'cantidad_requerida' => $item_carrito->cantidad
                    ];
                }
                $productos_a_comprar[] = [
                    'producto' => $producto,
                    'cantidad' => $item_carrito->cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $producto->precio * $item_carrito->cantidad
                ];
                $total_pedido += $producto->precio * $item_carrito->cantidad;
            }

            if (!empty($stock_errors)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No hay suficiente stock para algunos productos.',
                    'status' => 'stock_error',
                    'stock_errors' => $stock_errors
                ], 409);
            }

            $pedido = Pedidos::create([
                'usuario_id' => $user->id,
                'direccion_id' => $request->direccion_id,
                'total' => $total_pedido,
                'estado' => 'pendiente',
                'fecha_pedido' => now(), // Usar now() para fecha_pedido
            ]);

            foreach ($productos_a_comprar as $item) {
                PedidosProductos::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['producto']->decrement('stock', $item['cantidad']);
            }

            $carrito->estado = 'completado';
            $carrito->save();

            DB::commit();

            return response()->json([
                'message' => 'Pedido realizado con éxito',
                'pedido' => $pedido->load('productos.producto', 'direccion'), // Cargar relaciones para la respuesta
                'carrito_estado' => $carrito->estado
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar el pedido: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pedidos  $pedido // Usar tu modelo Pedidos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pedidos $pedido) // Tipo correcto de modelo
    {
        // Verificar que el usuario autenticado sea el dueño del pedido o un administrador
        if (Auth::id() !== $pedido->usuario_id && Auth::user()->rol !== 'admin') {
            return response()->json(['message' => 'No tienes permiso para eliminar este pedido.'], 403);
        }

        // Verificar el estado del pedido antes de eliminar
        // Solo permitir eliminación si el estado es 'completado' o 'cancelado' (o si es admin)
        if (! (Auth::user()->rol === 'admin' || ($pedido->estado === 'completado' || $pedido->estado === 'cancelado'))) {
             return response()->json(['message' => 'Solo se pueden eliminar pedidos completados o cancelados (a menos que seas administrador).'], 400);
        }

        DB::beginTransaction();
        try {
            // Opcional: revertir stock si es un pedido que se cancela/elimina
            // Esto solo tiene sentido si el stock se decrementó y el pedido no se completó.
            // Decide si quieres revertir stock al eliminar un pedido.
            // Por ejemplo, si un admin "elimina" un pedido que estaba "pendiente" o "procesando"
            // foreach ($pedido->productos as $pedidoProducto) {
            //     $producto = Producto::find($pedidoProducto->producto_id);
            //     if ($producto) {
            //         $producto->stock += $pedidoProducto->cantidad;
            //         $producto->save();
            //     }
            // }

            // Eliminar los productos asociados al pedido en la tabla pivote
            $pedido->productos()->delete();
            
            // Eliminar el pedido
            $pedido->delete();

            DB::commit();
            return response()->json(['message' => 'Pedido eliminado exitosamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar el pedido: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of the resource (for Admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin(Request $request)
    {
        // El middleware IsAdmin ya se encarga de la autorización,
        // pero se mantiene la verificación explícita para robustez.
        if ($request->user()->rol !== 'admin') {
            return response()->json(['message' => 'Acceso denegado. Se requiere rol de administrador.'], 403);
        }

        // Cargar TODOS los pedidos con sus relaciones (productos.producto, user y direccion)
        // Usamos 'usuario' si esa es la relación en tu modelo Pedidos
        $pedidos = Pedidos::with(['productos.producto', 'usuario', 'direccion'])
                        ->orderBy('created_at', 'desc') // Ordenar por los más recientes
                        ->get();

        return response()->json($pedidos);
    }

    /**
     * Update the state of the specified order. (Admin only)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pedidos  $pedido // Usar tu modelo Pedidos
     * @return \Illuminate\Http\Response
     */
    public function updateEstado(Request $request, Pedidos $pedido) // Tipo correcto de modelo
    {
        // El middleware IsAdmin ya se encarga de la autorización,
        // pero se mantiene la verificación explícita para robustez.
        if ($request->user()->rol !== 'admin') {
            return response()->json(['message' => 'Acceso denegado. Se requiere rol de administrador.'], 403);
        }

        $request->validate([
            'estado' => ['required', 'string', 'in:pendiente,procesando,completado,cancelado'],
        ]);

        $pedido->estado = $request->estado;
        $pedido->save();

        return response()->json($pedido);
    }
}