<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direcciones extends Model
{
    protected $table = 'direcciones';
    
    protected $fillable = [
        'usuario_id', 'direccion', 'ciudad', 'provincia', 'telefono',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedidos::class, 'direccion_id');
    }
}
