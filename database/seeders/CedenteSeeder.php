<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cedente;

class CedenteSeeder extends Seeder
{
    public function run()
    {
        $token = env('OPS_TOKEN_DEFAULT', null);
        $from  = env('OPS_FROM_DEFAULT', null);

        $cedentes = ['AIEP', 'UNAB', 'MASIVA', 'SALDO', 'UVM'];

        foreach ($cedentes as $nombre) {
            Cedente::create([
                'nombre'    => $nombre,
                'ops_token' => $token,
                'ops_from'  => $from,
            ]);
        }
    }
}
