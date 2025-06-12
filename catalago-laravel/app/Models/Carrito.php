<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $fillable = ['usuario_id', 'estado'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function productos()
    {
        // Un Carrito tiene muchos CarritoProducto (ítems en el carrito)
        return $this->hasMany(CarritoProducto::class, 'carrito_id');
    }
}