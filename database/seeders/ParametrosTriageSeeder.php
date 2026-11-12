<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametrosTriageSeeder extends Seeder
{
    public function run(): void
    {
        $parametros = [
            [
                'clave_parametro' => 'temp_fiebre_naranja',
                'nombre_descriptivo' => 'Fiebre muy alta / Hipotermia (Naranja)',
                'valor' => 40.00,
                'unidad_medida' => '°C',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave_parametro' => 'temp_fiebre_amarillo',
                'nombre_descriptivo' => 'Fiebre alta (Amarillo)',
                'valor' => 39.00,
                'unidad_medida' => '°C',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave_parametro' => 'temp_fiebre_verde',
                'nombre_descriptivo' => 'Febrícula (Verde)',
                'valor' => 38.00,
                'unidad_medida' => '°C',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Podremos agregar la presión o saturación después, pero con la temperatura probamos el FR6.
        ];

        DB::table('parametros_triage')->insert($parametros);
    }
}