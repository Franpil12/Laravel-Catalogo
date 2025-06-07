<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Direcciones;
use App\Models\User;


class UsuarioDireccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@tienda.com',
            'password' => Hash::make('admin123'),
            'rol' => 'admin',
        ]);
 
        // Crear usuario cliente
        $cliente = User::create([
            'name' => 'Cliente User',
            'email' => 'cliente@tienda.com',
            'password' => Hash::make('cliente123'),
            'rol' => 'cliente',
        ]);
 
        // Agregar direcciones
        Direcciones::create([
            'usuario_id' => $admin->id,
            'direccion' => 'Av. Central 123',
            'ciudad' => 'Quito',
            'provincia' => 'Pichincha',
            'telefono' => '0987654321',
        ]);
 
        Direcciones::create([
            'usuario_id' => $cliente->id,
            'direccion' => 'Calle Falsa 456',
            'ciudad' => 'Guayaquil',
            'provincia' => 'Guayas',
            'telefono' => '0976543210',
        ]);
    }
}
