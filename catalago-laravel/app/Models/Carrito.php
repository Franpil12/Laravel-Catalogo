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
        return $this->hasMany(CarritoProducto::class, 'carrito_id');
    }
}
