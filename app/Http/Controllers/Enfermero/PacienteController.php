<?php

namespace App\Http\Controllers\Enfermero;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PacienteController extends Controller
{
    public function index()
    {
        // Ordenamos para ver primero a los más recientes
        $pacientes = Paciente::orderBy('created_at', 'desc')->get();
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
        // Validamos los datos entrantes
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|in:Masculino,Femenino,Otro'
        ]);

        // Actualizamos en la base de datos
        DB::table('pacientes')->where('id_paciente', $id_paciente)->update([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'genero' => $request->genero,
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