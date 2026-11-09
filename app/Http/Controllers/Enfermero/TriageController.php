<?php

namespace App\Http\Controllers\Enfermero;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\SignoVital;
use App\Models\HistorialTriage;
use App\Services\MotorTriage;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


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
    public function store(Request $request, $id_paciente, MotorTriage $motorTriage)
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
    public function generarTicket($id_triage)
    {
        // Consultar los datos exactos del triage y del paciente
        $triage = DB::table('historial_triage')
            ->join('pacientes', 'historial_triage.id_paciente', '=', 'pacientes.id_paciente')
            ->join('signos_vitales', 'historial_triage.id_paciente', '=', 'signos_vitales.id_paciente')
            ->select(
                'historial_triage.*', 
                'pacientes.nombre', 'pacientes.apellidoPat', 'pacientes.fechaNacimiento',
                'signos_vitales.frecuencia_cardiaca', 'signos_vitales.frecuencia_respiratoria', 
                'signos_vitales.temperatura', 'signos_vitales.saturacion_oxigeno', 
                'signos_vitales.presion_sistolica', 'signos_vitales.presion_diastolica'
            )
            ->where('id_triage', $id_triage)
            ->orderBy('signos_vitales.fecha_captura', 'desc')
            ->first();

        if (!$triage) {
            return redirect()->back()->with('error', 'Ticket no encontrado.');
        }

        // Generar el PDF cargando una vista de Blade
        $pdf = Pdf::loadView('enfermero.triage.ticket', compact('triage'));
        
        // Mostrar el PDF directamente en el navegador (stream) o forzar descarga (download)
        return $pdf->stream('Ticket_Triage_NOM027_' . $triage->id_triage . '.pdf');
    }
}