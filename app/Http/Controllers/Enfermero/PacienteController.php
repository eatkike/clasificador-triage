<?php

namespace App\Http\Controllers\Enfermero;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    public function index()
    {
        // PANTALLA PRINCIPAL: Solo mostramos a los que acaban de llegar y están esperando Triage
        $pacientes = DB::table('pacientes')
            ->where('estado_atencion', 'Espera de Valoración')
            ->orderBy('created_at', 'asc') // Ordenados por hora de llegada (el más antiguo primero)
            ->get();

        return view('enfermero.pacientes.index', compact('pacientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'apellidoPat' => 'required|string|max:45',
            'apellidoMat' => 'nullable|string|max:45',
            'fechaNacimiento' => 'required|date|before:today',
            'sexo' => ['required', Rule::in(['Masculino', 'Femenino'])],
            'contacto_familiar' => 'nullable|string|max:20',
            'antecedentes_clinicos' => 'nullable|string',
            'motivo_consulta' => 'required|string',
        ]);

        // Por defecto, al registrarse entra en "Espera de Valoración"
        $validated['estado_atencion'] = 'Espera de Valoración';

        Paciente::create($validated);

        return redirect()->route('enfermero.pacientes.index')
            ->with('success', 'Expediente del paciente aperturado correctamente. Listo para Triage.');
    }
    
    // Actualizar los datos personales del paciente
    public function updatePaciente(Request $request, $id_paciente)
    {
        // 1. Validamos usando los nombres EXACTOS de tus inputs en HTML
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidoPat' => 'required|string|max:100',
            'apellidoMat' => 'nullable|string|max:100',
            'fechaNacimiento' => 'required|date',
            'sexo' => 'required|in:Masculino,Femenino'
        ]);

        // 2. Actualizamos usando los nombres EXACTOS de tu base de datos
        DB::table('pacientes')->where('id_paciente', $id_paciente)->update([
            'nombre' => $request->nombre,
            'apellidoPat' => $request->apellidoPat,
            'apellidoMat' => $request->apellidoMat,
            'fechaNacimiento' => $request->fechaNacimiento,
            'sexo' => $request->sexo,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Los datos del paciente han sido actualizados correctamente.');
    }
    // Eliminar expediente de paciente (con protección de llaves foráneas)
    public function destroyPaciente($id_paciente)
    {
        try {
            DB::table('pacientes')->where('id_paciente', $id_paciente)->delete();
            return back()->with('success', 'El registro del paciente ha sido eliminado del sistema.');
        } catch (\Exception $e) {
            // Si el paciente ya tiene Triage o Signos Vitales, la BD bloqueará el borrado para no corromper historiales.
            return back()->withErrors(['msg' => 'No se puede eliminar: El paciente ya tiene un historial clínico o signos vitales registrados.']);
        }
    }
}