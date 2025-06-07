<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'titulo', 'descripcion', 'precio', 'imagen', 'stock',
    ];

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_productos');
    }

    public function carritoProductos()
    {
        return $this->hasMany(CarritoProducto::class, 'producto_id');
    }

    public function pedidoProductos()
    {
        return $this->hasMany(PedidoProducto::class, 'producto_id');
    }
}

