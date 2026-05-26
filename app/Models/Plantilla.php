<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = 'plantillas_sms';

    protected $fillable = ['nombre', 'contenido', 'cedente_id', 'created_by'];

    public function cedente()
    {
        return $this->belongsTo(Cedente::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
