<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Direcciones; // CAMBIO: Importamos el modelo en plural 'Direcciones'
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DireccionController extends Controller
{
    /**
     * Display a listing of the user's addresses.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $direcciones = $user->direcciones; // Esto funciona gracias a la relación en el modelo User

            return response()->json([
                'message' => 'Direcciones obtenidas exitosamente',
                'direcciones' => $direcciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener direcciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'direccion' => 'required|string|max:255',
                'ciudad' => 'required|string|max:255',
                'provincia' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:20',
                // Si tu migración no tiene 'codigo_postal', asegúrate de que no esté aquí
            ]);

            $user = Auth::user();

            $direccion = $user->direcciones()->create([
                'direccion' => $request->direccion,
                'ciudad' => $request->ciudad,
                'provincia' => $request->provincia,
                'telefono' => $request->telefono,
            ]);

            return response()->json([
                'message' => 'Dirección creada exitosamente',
                'direccion' => $direccion
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la dirección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified address.
     */
    public function show(Direcciones $direccion) // CAMBIO: Usamos 'Direcciones' para el type hint
    {
        // CAMBIO: Usar 'usuario_id' para la comprobación
        if (Auth::id() !== $direccion->usuario_id) {
            return response()->json(['message' => 'No autorizado para ver esta dirección.'], 403);
        }

        return response()->json([
            'message' => 'Dirección obtenida exitosamente',
            'direccion' => $direccion
        ], 200);
    }

    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, Direcciones $direccion) // CAMBIO: Usamos 'Direcciones' para el type hint
    {
        // CAMBIO: Usar 'usuario_id' para la comprobación
        if (Auth::id() !== $direccion->usuario_id) {
            return response()->json(['message' => 'No autorizado para actualizar esta dirección.'], 403);
        }

        try {
            $request->validate([
                'direccion' => 'required|string|max:255',
                'ciudad' => 'required|string|max:255',
                'provincia' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:20',
            ]);

            $direccion->update([
                'direccion' => $request->direccion,
                'ciudad' => $request->ciudad,
                'provincia' => $request->provincia,
                'telefono' => $request->telefono,
            ]);

            return response()->json([
                'message' => 'Dirección actualizada exitosamente',
                'direccion' => $direccion
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la dirección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy(Direcciones $direccion) // CAMBIO: Usamos 'Direcciones' para el type hint
    {
        // CAMBIO: Usar 'usuario_id' para la comprobación
        if (Auth::id() !== $direccion->usuario_id) {
            return response()->json(['message' => 'No autorizado para eliminar esta dirección.'], 403);
        }

        try {
            $direccion->delete();
            return response()->json(['message' => 'Dirección eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la dirección: ' . $e->getMessage()
            ], 500);
        }
    }
}