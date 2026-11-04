<?php

namespace App\Http\Controllers\Enfermero;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\SignoVital;
use App\Models\HistorialTriage;
use App\Services\MotorTriageService;
use Illuminate\Http\Request;

class TriageController extends Controller
{
    // Mostrar formulario de toma de signos
    public function create($id_paciente)
    {
        $paciente = Paciente::findOrFail($id_paciente);
        
        if ($paciente->estado_atencion !== 'Espera de Valoración') {
            return redirect()->route('enfermero.pacientes.index')->withErrors(['msg' => 'Este paciente ya fue clasificado.']);
        }

        return view('enfermero.triage.create', compact('paciente'));
    }

    // Procesar signos, evaluar y guardar dictamen
    public function store(Request $request, $id_paciente, MotorTriageService $motorTriage)
    {
        // Validación estricta para evitar errores humanos (Iniciativa INI-04)
        $validated = $request->validate([
            'presion_sistolica' => 'required|numeric|min:40|max:250',
            'presion_diastolica' => 'required|numeric|min:30|max:180',
            'frecuencia_cardiaca' => 'required|numeric|min:20|max:250',
            'frecuencia_respiratoria' => 'required|numeric|min:5|max:60',
            'temperatura' => 'required|numeric|min:34|max:44',
            'saturacion_oxigeno' => 'required|numeric|min:30|max:100',
            'sintomas_cualitativos' => 'required|string',
        ]);

        $paciente = Paciente::findOrFail($id_paciente);

        // 1. Guardar Signos Vitales
        $signos = new SignoVital($validated);
        $signos->id_paciente = $paciente->id_paciente;
        $signos->id_enfermero = auth()->user()->id_usuario;
        $signos->save();

        // 2. Ejecutar Motor Lógico de Triage
        $dictamen = $motorTriage->evaluar($signos);

        // 3. Guardar el Dictamen (Historial)
        HistorialTriage::create([
            'id_paciente' => $paciente->id_paciente,
            'id_enfermero' => auth()->user()->id_usuario,
            'color_calculado' => $dictamen['color'],
            'criterio_aplicado' => $dictamen['criterio']
        ]);

        // 4. Actualizar estado del paciente
        $paciente->update(['estado_atencion' => 'Clasificado']);

        return redirect()->route('enfermero.pacientes.index')
            ->with('success', "Paciente evaluado exitosamente: CLASIFICACIÓN {$dictamen['color']}.");
    }
}