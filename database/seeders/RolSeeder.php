<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run()
    {
        Rol::insert([
            ['nombre' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Operador', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
