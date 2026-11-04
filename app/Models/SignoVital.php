<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignoVital extends Model
{
    protected $table = 'signos_vitales';
    protected $primaryKey = 'id_signos';
    public $timestamps = false; // Usaremos el default de MySQL para fecha_captura

    protected $fillable = [
        'id_paciente',
        'id_enfermero',
        'presion_sistolica',
        'presion_diastolica',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'temperatura',
        'saturacion_oxigeno',
        'sintomas_cualitativos'
    ];
}