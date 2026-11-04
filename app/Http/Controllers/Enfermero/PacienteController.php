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
}