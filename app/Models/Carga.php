<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
    protected $fillable = ['nombre', 'cedente_id', 'total_registros', 'created_by'];

    public function cedente()
    {
        return $this->belongsTo(Cedente::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contactos()
    {
        return $this->hasMany(CargaContacto::class);
    }
}
