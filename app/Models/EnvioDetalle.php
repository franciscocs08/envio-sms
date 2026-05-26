<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvioDetalle extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'envio_id', 'telefono', 'rut', 'estado',
        'ops_sms_id', 'respuesta_gateway', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function envio()
    {
        return $this->belongsTo(Envio::class);
    }
}
