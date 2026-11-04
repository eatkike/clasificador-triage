<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Conectar a la tabla definida en tu diseño
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellidos',
        'cedula',
        'rol',
        'turno',
        'correo',
        'password',
        'intentos_fallidos',
        'bloqueado_hasta',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'bloqueado_hasta' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}