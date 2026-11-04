<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PersonalController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('id_usuario', 'asc')->get();
        return view('admin.personal.index', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'    => 'required|string|max:50',
            'apellidos' => 'required|string|max:60',
            'cedula'    => 'required|string|max:15|unique:usuarios,cedula',
            'rol'       => ['required', Rule::in(['Administrador', 'Médico', 'Enfermero'])],
            'turno'     => ['required', Rule::in(['Matutino', 'Vespertino', 'Nocturno', 'Mixto'])],
            'correo'    => 'required|email|max:100|unique:usuarios,correo',
            'password'  => 'required|string|min:6',
        ]);

        User::create([
            'nombre'            => $validated['nombre'],
            'apellidos'         => $validated['apellidos'],
            'cedula'            => $validated['cedula'],
            'rol'               => $validated['rol'],
            'turno'             => $validated['turno'],
            'correo'            => $validated['correo'],
            'password'          => Hash::make($validated['password']),
            'intentos_fallidos' => 0,
            'is_active'         => 1,
        ]);

        return redirect()->route('admin.personal.index')
            ->with('success', 'Personal registrado correctamente.');
    }

    public function toggleStatus($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id_usuario === auth()->user()->id_usuario) {
            return back()->withErrors(['msg' => 'No puedes desactivar tu propia cuenta activa.']);
        }

        $usuario->is_active = !$usuario->is_active;
        $usuario->save();

        return redirect()->route('admin.personal.index')
            ->with('success', 'Estatus actualizado correctamente.');
    }
}