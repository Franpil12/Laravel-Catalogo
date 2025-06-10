<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        // Obtener solo los nombres de las categorías
        $categorias = Categoria::select('id', 'nombre')->get();
        
        return response()->json($categorias);
    }
}