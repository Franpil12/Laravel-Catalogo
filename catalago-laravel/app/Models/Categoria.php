<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre'];

    public function productos() {
        return $this->belongsToMany(Producto::class, 'categoria_productos');
    }
}
