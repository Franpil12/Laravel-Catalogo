<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Producto;
 
class CategoriaProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear categorías usando firstOrCreate
        $categorias = [
            ['nombre' => 'Electrónica', 'slug' => 'electronica'],
            ['nombre' => 'Ropa', 'slug' => 'ropa'],
            ['nombre' => 'Hogar', 'slug' => 'hogar'],
        ];
 
        foreach ($categorias as $catData) {
            Categoria::firstOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );
        }
 
        // Crear productos
        $productos = [
            [
                'titulo' => 'Laptop X1',
                'descripcion' => 'Laptop de alto rendimiento',
                'precio' => 1200.50,
                'imagen' => 'https://firebasestorage.googleapis.com/v0/b/imagenes-efdc3.firebasestorage.app/o/imagenes%2F1749550239500-sofa.jpg?alt=media&token=26157030-1d95-4cd8-aa3e-3290c524098d',
                'stock' => 10,
            ],
            [
                'titulo' => 'Camisa Casual',
                'descripcion' => 'Camisa de algodón',
                'precio' => 25.99,
                'imagen' => 'https://firebasestorage.googleapis.com/v0/b/imagenes-efdc3.firebasestorage.app/o/imagenes%2F1749550363924-camisa.jpg?alt=media&token=76724b86-1171-4241-9adb-b8959995d51d',
                'stock' => 50,
            ],
            [
                'titulo' => 'Sofá Moderno',
                'descripcion' => 'Sofá de diseño cómodo',
                'precio' => 499.99,
                'imagen' => 'https://firebasestorage.googleapis.com/v0/b/imagenes-efdc3.firebasestorage.app/o/imagenes%2F1749550239500-sofa.jpg?alt=media&token=26157030-1d95-4cd8-aa3e-3290c524098d',
                'stock' => 5,
            ],
        ];
 
        foreach ($productos as $prodData) {
            $producto = Producto::firstOrCreate(
                ['titulo' => $prodData['titulo']],
                $prodData
            );
 
            // Asignar categorías aleatorias
            $categoriaIds = Categoria::inRandomOrder()->limit(rand(1, 2))->pluck('id')->toArray();
            $producto->categorias()->sync($categoriaIds);
        }
    }
}