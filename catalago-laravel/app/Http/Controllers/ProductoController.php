<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Producto::with('categorias') ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string|max:255',
            'stock' => 'required|integer|min:0',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);


        $product= DB::transaction(function () use ($validatedData, $request) {
            $producto = Producto::create($validatedData);

            if ($request->has('categorias')) {
                $producto->categorias()->sync($validatedData['categorias']);
            }

            return response()->json($producto->load('categorias'), 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $producto = Producto::with('categorias')->findOrFail($id);
        return response()->json($producto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);

        $validatedData = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|required|string',
            'precio' => 'sometimes|required|numeric|min:0',
            'imagen' => 'nullable|string',
            'stock' => 'sometimes|required|integer|min:0',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        $product = DB::transaction(function () use ($request, $producto, $validatedData) {
            $producto->update($validatedData);
            if ($request->has('categorias')) {
                $producto->categorias()->sync($validatedData['categorias']);
            }
            return $producto;
        });

        $producto->update($validatedData);

        if ($request->has('categorias')) {
            $producto->categorias()->sync($validatedData['categorias']);
        }

        return response()->json($producto->load('categorias'), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->categorias()->detach();
        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente'], 200);
    }
}
