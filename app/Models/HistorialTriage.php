<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialTriage extends Model
{
    protected $table = 'historial_triage';
    protected $primaryKey = 'id_triage';
    public $timestamps = false; // Usaremos el default de MySQL

    protected $fillable = [
        'id_paciente',
        'id_enfermero',
        'id_medico',
        'color_calculado',
        'criterio_aplicado',
        'fecha_atencion_medica'
    ];
}