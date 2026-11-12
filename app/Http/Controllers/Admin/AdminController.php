<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    public function index()
    {
        // Traemos a todos los usuarios que NO sean el administrador principal
        $usuarios = DB::table('usuarios')
            ->whereIn('rol', ['Médico', 'Enfermero'])
            ->orderBy('id_usuario', 'desc')
            ->get();

        return view('admin.personal.index', compact('usuarios'));
    }

    // Mostrar el catálogo de parámetros
    public function catalogo()
    {
        $parametros = DB::table('parametros_triage')->get();
        return view('admin.catalogo.index', compact('parametros'));
    }

    // Actualizar un parámetro específico
    public function updateCatalogo(Request $request, $id_parametro)
    {
        $request->validate([
            'valor' => 'required|numeric|min:0'
        ]);

        DB::table('parametros_triage')->where('id_parametro', $id_parametro)->update([
            'valor' => $request->valor,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Parámetro clínico actualizado. El algoritmo utilizará este nuevo valor a partir de ahora.');
    }

    // Registrar nuevo personal
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:60',
            'cedula' => 'required|string|max:15|unique:usuarios',
            'correo' => 'required|email|max:100|unique:usuarios',
            'password' => 'required|min:6',
            'rol' => 'required|in:Médico,Enfermero,Administrador',
            'turno' => 'required|in:Matutino,Vespertino,Nocturno,Mixto'
        ]);

        DB::table('usuarios')->insert([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'cedula' => $request->cedula,
            'correo' => $request->correo,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'turno' => $request->turno,
            'is_active' => 1,
            'intentos_fallidos' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Personal registrado exitosamente.');
    }

    // Actualizar datos (Turno o Rol)
    public function update(Request $request, $id_usuario)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:60',
            'rol' => 'required|in:Médico,Enfermero',
            'turno' => 'required|in:Matutino,Vespertino,Nocturno,Mixto'
        ]);

        DB::table('usuarios')->where('id_usuario', $id_usuario)->update([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'rol' => $request->rol,
            'turno' => $request->turno,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Datos actualizados correctamente.');
    }

    // Dar de baja temporalmente (Cambiar is_active sin borrar para proteger el historial médico)
    public function toggle($id_usuario)
    {
        $usuario = DB::table('usuarios')->where('id_usuario', $id_usuario)->first();
        
        $nuevoEstado = $usuario->is_active ? 0 : 1;
        $mensaje = $nuevoEstado ? 'Usuario reactivado.' : 'Usuario dado de baja (Inactivo).';

        DB::table('usuarios')->where('id_usuario', $id_usuario)->update([
            'is_active' => $nuevoEstado,
            'updated_at' => now()
        ]);

        return back()->with('success', $mensaje);
    }
    public function reportes()
    {
        // Agrupamos el total de pacientes atendidos por cada color de Triage
        $estadisticas = DB::table('historial_triage')
            ->select('color_calculado', DB::raw('count(*) as total'))
            ->groupBy('color_calculado')
            ->get();

        // Preparamos los arrays para inyectarlos en Chart.js
        $colores = $estadisticas->pluck('color_calculado');
        $totales = $estadisticas->pluck('total');

        return view('admin.reportes.index', compact('colores', 'totales'));
    }
    // Mostrar la pantalla con la tabla de respaldos
    public function respaldosIndex()
    {
        $path = storage_path('app/backups');
        
        // Si la carpeta no existe, la creamos
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // Leemos todos los archivos .sql y armamos un arreglo
        $archivos = File::files($path);
        $respaldos = [];

        foreach ($archivos as $archivo) {
            $respaldos[] = [
                'nombre' => $archivo->getFilename(),
                'fecha' => date('d/m/Y H:i:s', $archivo->getMTime()),
                'size' => round($archivo->getSize() / 1024, 2) . ' KB',
                'timestamp' => $archivo->getMTime()
            ];
        }

        // Ordenamos para que el respaldo más reciente salga arriba
        usort($respaldos, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return view('admin.respaldos.index', compact('respaldos'));
    }

    // Ejecutar el volcado y guardarlo en la carpeta
    public function generarRespaldo()
    {
        $dbName = env('DB_DATABASE');
        $userName = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $dumpPath = env('DB_DUMP_PATH', 'mysqldump'); 

        $path = storage_path('app/backups');
        $fileName = 'triage_backup_' . date('Y_m_d_H_i_s') . '.sql';
        $filePath = $path . '/' . $fileName;

        $passOption = !empty($password) ? "-p{$password}" : "";
        
        $command = "\"{$dumpPath}\" -h {$host} -u {$userName} {$passOption} {$dbName} > \"{$filePath}\"";
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            // Si falla, borramos el archivo vacío para no generar basura
            if (File::exists($filePath)) File::delete($filePath);
            return back()->withErrors(['msg' => 'Error al generar el respaldo de la base de datos.']);
        }

        return back()->with('success', 'Nuevo respaldo generado exitosamente.');
    }

    // Descargar un respaldo específico
    public function descargarRespaldo($nombre)
    {
        $path = storage_path('app/backups/' . $nombre);
        if (File::exists($path)) return response()->download($path);
        
        return back()->withErrors(['msg' => 'El archivo solicitado ya no existe.']);
    }

    // Eliminar un respaldo del servidor
    public function eliminarRespaldo($nombre)
    {
        $path = storage_path('app/backups/' . $nombre);
        if (File::exists($path)) {
            File::delete($path);
            return back()->with('success', 'Respaldo eliminado de forma permanente.');
        }
        return back()->withErrors(['msg' => 'El archivo solicitado ya no existe.']);
    }
}