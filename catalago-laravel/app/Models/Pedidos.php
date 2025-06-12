<?php

namespace App\Models; // Esta es la primera línea después de <?php

use Illuminate\Database\Eloquent\Factories\HasFactory; // Si estás usando factories para este modelo, es necesario.
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // <-- Esta línea
use App\Models\Direcciones; // <-- Esta línea
use App\Models\PedidosProductos; // <-- Y esta línea

class Pedidos extends Model
{
    use HasFactory; // Si declaraste HasFactory arriba, debes usarlo aquí.

    protected $table = 'pedidos';

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
        return $this->hasMany(PedidosProductos::class, 'pedido_id');
    }
}