<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol_id',
        'cedente_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    public function cedente()
    {
        return $this->belongsTo(Cedente::class);
    }

    public function esAdmin()
    {
        return $this->rol && $this->rol->nombre === 'Admin';
    }
}
