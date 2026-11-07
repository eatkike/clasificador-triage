<?php

namespace App\Services;

use App\Models\SignoVital;

class MotorTriage
{
    /**
     * Evalúa los signos vitales y devuelve el color de inmediatez y el criterio aplicado.
     */
    public function evaluar(SignoVital $signos): array
    {
        // 1. Evaluación de Código ROJO (Atención Inmediata)
        if ($signos->saturacion_oxigeno < 80 || $signos->frecuencia_respiratoria < 10) {
            return [
                'color' => 'Rojo',
                'criterio' => 'Hipoxia severa o depresión respiratoria inminente.'
            ];
        }

        // 2. Evaluación de Código NARANJA (Atención < 15 min)
        if ($signos->saturacion_oxigeno < 90 || $signos->frecuencia_cardiaca > 130) {
            return [
                'color' => 'Naranja',
                'criterio' => 'Oxigenación comprometida o taquicardia severa.'
            ];
        }

        // 3. Evaluación de Código AMARILLO (Atención < 30 min)
        if ($signos->temperatura >= 38.5 || $signos->presion_sistolica > 160) {
            return [
                'color' => 'Amarillo',
                'criterio' => 'Fiebre alta o crisis hipertensiva.'
            ];
        }

        // 4. Evaluación de Código VERDE (Atención estándar)
        if ($signos->temperatura > 37.5 && $signos->temperatura < 38.5) {
            return [
                'color' => 'Verde',
                'criterio' => 'Febrícula sin descompensación.'
            ];
        }

        // 5. Código AZUL (No urgente)
        return [
            'color' => 'Azul',
            'criterio' => 'Signos vitales estables dentro de parámetros normales.'
        ];
    }
}