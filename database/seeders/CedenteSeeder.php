<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cedente;

class CedenteSeeder extends Seeder
{
    public function run()
    {
        Cedente::insert([
            [
                'nombre'    => 'Empresa Prueba A',
                'rut'       => '9.999.999-9', /* RUT de ejemplo no se sabe si es necesario en cedentes */
                'ops_token' => null,
                'ops_from'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
