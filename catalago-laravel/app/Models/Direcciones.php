<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Asegúrate de tener esta importación
use Illuminate\Database\Eloquent\Model;

class Direcciones extends Model // Mantenemos el nombre del modelo en plural
{
    use HasFactory; // Agrega esto si tu modelo no lo tiene

    protected $table = 'direcciones'; // Es importante que esta línea esté explícita si el modelo es plural

    protected $fillable = [
        'usuario_id', // Mantenemos 'usuario_id' aquí
        'direccion',
        'ciudad',
        'provincia',
        'telefono',
    ];

    public function usuario()
    {
        // CAMBIO: Asegurarnos de que el belongsTo use 'usuario_id' explícitamente y el modelo User
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Asegúrate de que el nombre del modelo 'Pedidos' o 'Pedido' sea correcto
    public function pedidos()
    {
        // Si tu modelo de Pedido se llama 'Pedidos' (plural), entonces:
        return $this->hasMany(Pedidos::class, 'direccion_id'); // Usando Pedidos por tu modelo anterior
        // Si tu modelo de Pedido se llama 'Pedido' (singular), la relación debería ser:
        // return $this->hasMany(Pedido::class, 'direccion_id');
    }
}