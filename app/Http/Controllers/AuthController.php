<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    // Mostrar formulario de Login
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->rol);
        }
        return view('auth.login');
    }

    // Procesar autenticación
    public function authenticate(Request $request)
    {
        // 1. Validar campos
        $request->validate([
            'cedula' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Buscar usuario por cédula
        $user = User::where('cedula', $request->cedula)->first();

        if ($user) {
            // 3. Validar bloqueo temporal (FF.2)
            if ($user->bloqueado_hasta && now()->lessThan($user->bloqueado_hasta)) {
                $minutosRestantes = now()->diffInMinutes($user->bloqueado_hasta) + 1;
                return back()->withErrors([
                    'cedula' => "Por seguridad, su cuenta está bloqueada. Intente de nuevo en {$minutosRestantes} minutos."
                ])->withInput($request->except('password'));
            }

            // 4. Intentar autenticación
            if (Auth::attempt(['cedula' => $request->cedula, 'password' => $request->password])) {
                
                // Validar baja lógica
                if (!$user->is_active) {
                    Auth::logout();
                    return back()->withErrors(['cedula' => 'Esta cuenta se encuentra inactiva. Contacte a la administración.']);
                }

                // Éxito: Limpiar contador y quitar bloqueo
                $user->update([
                    'intentos_fallidos' => 0,
                    'bloqueado_hasta' => null,
                ]);

                $request->session()->regenerate();
                return $this->redirectByRole($user->rol);

            } else {
                // 5. Contraseña incorrecta: Incrementar intentos_fallidos
                $user->increment('intentos_fallidos');

                if ($user->intentos_fallidos >= 5) {
                    $user->update(['bloqueado_hasta' => now()->addMinutes(15)]);
                    return back()->withErrors([
                        'cedula' => 'Ha superado los 5 intentos fallidos. Su cuenta ha sido bloqueada por 15 minutos.'
                    ])->withInput($request->except('password'));
                }

                $intentosRestantes = 5 - $user->intentos_fallidos;
                return back()->withErrors([
                    'cedula' => "Contraseña incorrecta. Le quedan {$intentosRestantes} intentos antes del bloqueo."
                ])->withInput($request->except('password'));
            }
        }

        return back()->withErrors([
            'cedula' => 'Las credenciales ingresadas no coinciden con nuestros registros.'
        ])->withInput($request->except('password'));
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    // Redirección dinámica según el rol
    private function redirectByRole($rol)
    {
        return match(strtolower($rol)) {
            'administrador', 'admin' => redirect()->route('admin.personal.index'),
            'médico', 'medico'       => redirect()->route('medico.dashboard'),
            'enfermero'              => redirect()->route('enfermero.pacientes.index'),
            default                  => redirect('/'),
        };
    }
    //envio de correos
    // 1. Mostrar el formulario para pedir el correo
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // 2. Generar el token y enviar el correo vía Gmail
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['correo' => 'required|email|exists:usuarios,correo']);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['correo' => $request->correo],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::send('emails.recuperar', ['token' => $token, 'correo' => $request->correo], function($message) use ($request) {
            $message->to($request->correo);
            $message->subject('Recuperación de Contraseña - Triage UPEMOR');
        });

        return back()->with('success', 'Te hemos enviado el enlace de recuperación a tu correo electrónico.');
    }

    // 3. Mostrar el formulario para escribir la nueva contraseña
    public function showResetForm(Request $request, $token)
    {
        return view('auth.passwords.reset', ['token' => $token, 'correo' => $request->correo]);
    }

    // 4. Procesar el cambio en la base de datos
    public function resetPassword(Request $request)
    {
        $request->validate([
            'correo' => 'required|email|exists:usuarios,correo',
            'password' => 'required|min:6|confirmed',
            'token' => 'required'
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('correo', $request->correo)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['correo' => 'El token es inválido o ha expirado.']);
        }

        DB::table('usuarios')->where('correo', $request->correo)->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('correo', $request->correo)->delete();

        return redirect('/')->with('success', 'Tu contraseña ha sido actualizada correctamente. Ya puedes iniciar sesión.');
    }
}