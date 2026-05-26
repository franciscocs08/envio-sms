<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargaContacto extends Model
{
    protected $fillable = ['carga_id', 'rut', 'telefono'];

    public function carga()
    {
        return $this->belongsTo(Carga::class);
    }
}
