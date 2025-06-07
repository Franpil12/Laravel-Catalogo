<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{

    use HasApiTokens, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function carritos(){
        return $this->hasOne(Carrito::class, 'usuario_id');
    }

    public function pedidos(){
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    public function direcciones(){
        return $this->hasMany(Direccion::class, 'usuario_id');
    }
    
}
