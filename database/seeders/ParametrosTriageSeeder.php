<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametrosTriageSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos la tabla para que no choquen los datos viejos
        DB::table('parametros_triage')->delete();

        $parametros = [
            // PARÁMETROS ROJO (RESUCITACIÓN)
            ['clave_parametro' => 'spo2_rojo', 'nombre_descriptivo' => 'SpO2 Crítico Mínimo (Rojo)', 'valor' => 85, 'unidad_medida' => '%'],
            ['clave_parametro' => 'fc_rojo_min', 'nombre_descriptivo' => 'FC Bradicardia Severa (Rojo)', 'valor' => 40, 'unidad_medida' => 'lpm'],
            ['clave_parametro' => 'fc_rojo_max', 'nombre_descriptivo' => 'FC Taquicardia Severa (Rojo)', 'valor' => 130, 'unidad_medida' => 'lpm'],
            ['clave_parametro' => 'fr_rojo_min', 'nombre_descriptivo' => 'FR Depresión Respiratoria (Rojo)', 'valor' => 10, 'unidad_medida' => 'rpm'],
            ['clave_parametro' => 'fr_rojo_max', 'nombre_descriptivo' => 'FR Taquipnea Severa (Rojo)', 'valor' => 35, 'unidad_medida' => 'rpm'],
            ['clave_parametro' => 'sis_rojo_min', 'nombre_descriptivo' => 'Tensión Sistólica Hipotensión Severa (Rojo)', 'valor' => 70, 'unidad_medida' => 'mmHg'],
            
            // PARÁMETROS NARANJA (EMERGENCIA)
            ['clave_parametro' => 'temp_naranja_max', 'nombre_descriptivo' => 'Fiebre muy alta (Naranja)', 'valor' => 40.00, 'unidad_medida' => '°C'],
            ['clave_parametro' => 'temp_naranja_min', 'nombre_descriptivo' => 'Hipotermia (Naranja)', 'valor' => 35.00, 'unidad_medida' => '°C'],
            ['clave_parametro' => 'spo2_naranja', 'nombre_descriptivo' => 'SpO2 Bajo (Naranja)', 'valor' => 89, 'unidad_medida' => '%'],
            ['clave_parametro' => 'sis_naranja_max', 'nombre_descriptivo' => 'Crisis Hipertensiva Sistólica (Naranja)', 'valor' => 200, 'unidad_medida' => 'mmHg'],
            ['clave_parametro' => 'sis_naranja_min', 'nombre_descriptivo' => 'Hipotensión (Naranja)', 'valor' => 90, 'unidad_medida' => 'mmHg'],
            ['clave_parametro' => 'dia_naranja_max', 'nombre_descriptivo' => 'Crisis Hipertensiva Diastólica (Naranja)', 'valor' => 120, 'unidad_medida' => 'mmHg'],

            // PARÁMETROS AMARILLO (URGENCIA)
            ['clave_parametro' => 'temp_amarillo', 'nombre_descriptivo' => 'Fiebre alta (Amarillo)', 'valor' => 39.00, 'unidad_medida' => '°C'],
            ['clave_parametro' => 'spo2_amarillo', 'nombre_descriptivo' => 'SpO2 Levemente Bajo (Amarillo)', 'valor' => 94, 'unidad_medida' => '%'],
            ['clave_parametro' => 'sis_amarillo_max', 'nombre_descriptivo' => 'Hipertensión Nivel 2 Sistólica (Amarillo)', 'valor' => 160, 'unidad_medida' => 'mmHg'],

            // PARÁMETROS VERDE (URGENCIA MENOR)
            ['clave_parametro' => 'temp_verde', 'nombre_descriptivo' => 'Febrícula (Verde)', 'valor' => 38.00, 'unidad_medida' => '°C'],
            ['clave_parametro' => 'sis_verde_max', 'nombre_descriptivo' => 'Hipertensión Nivel 1 Sistólica (Verde)', 'valor' => 140, 'unidad_medida' => 'mmHg'],
        ];

        // Añadir timestamps a todos automáticamente
        foreach ($parametros as &$param) {
            $param['created_at'] = now();
            $param['updated_at'] = now();
        }

        DB::table('parametros_triage')->insert($parametros);
    }
}