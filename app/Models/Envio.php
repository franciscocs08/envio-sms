<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    protected $fillable = [
        'nombre', 'plantilla_id', 'carga_id', 'cedente_id',
        'estado', 'total', 'enviados', 'fallidos', 'created_by',
    ];

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class);
    }

    public function carga()
    {
        return $this->belongsTo(Carga::class);
    }

    public function cedente()
    {
        return $this->belongsTo(Cedente::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function detalles()
    {
        return $this->hasMany(EnvioDetalle::class);
    }
}
