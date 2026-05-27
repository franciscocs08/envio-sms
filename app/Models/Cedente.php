<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cedente extends Model
{
    protected $fillable = ['nombre', 'ops_token', 'ops_from'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function plantillas()
    {
        return $this->hasMany(Plantilla::class);
    }

    public function cargas()
    {
        return $this->hasMany(Carga::class);
    }

    public function envios()
    {
        return $this->hasMany(Envio::class);
    }
}
