<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Rol;
use App\Models\Cedente;

class UserSeeder extends Seeder
{
    public function run()
    {
        $adminRol    = Rol::where('nombre', 'Admin')->first();
        $operRol     = Rol::where('nombre', 'Operador')->first();
        $cedente1    = Cedente::first();

        // Admin global 
        User::create([
            'nombre'     => 'Administrador',    
            'email'      => 'admin@sms.cl',
            'password'   => Hash::make('123456'),
            'rol_id'     => $adminRol->id,
            'cedente_id' => null,
        ]);

        // Operador del primer cedente
        User::create([
            'nombre'     => 'Operador',
            'email'      => 'operador@sms.cl',
            'password'   => Hash::make('123456'),
            'rol_id'     => $operRol->id,
            'cedente_id' => $cedente1->id,
        ]);
    }
}
