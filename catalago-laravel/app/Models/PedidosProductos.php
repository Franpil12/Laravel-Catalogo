<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidosProductos extends Model
{
    protected $table = 'pedido_producto';

    protected $fillable = [
        'pedido_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedidos::class, 'pedido_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
