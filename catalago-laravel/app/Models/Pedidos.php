<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    protected $fillable = [
        'usuario_id', 'direccion_id', 'total', 'estado', 'fecha_pedido',
    ];

    protected $dates = ['fecha_pedido'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function direccion()
    {
        return $this->belongsTo(Direcciones::class, 'direccion_id');
    }

    public function productos()
    {
        return $this->hasMany(PedidoProducto::class, 'pedido_id');
    }
}
