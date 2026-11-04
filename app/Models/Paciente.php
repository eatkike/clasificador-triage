<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';
    protected $primaryKey = 'id_paciente';

    protected $fillable = [
        'nombre',
        'apellidoPat',
        'apellidoMat',
        'fechaNacimiento',
        'sexo',
        'antecedentes_clinicos',
        'motivo_consulta',
        'contacto_familiar',
        'estado_atencion',
    ];
}