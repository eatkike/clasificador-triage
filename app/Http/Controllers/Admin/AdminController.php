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
    
// Mostrar la pantalla de Reportes interactiva (Basada en el boceto)
    public function reportes(Request $request)
    {
        $tipo_reporte = $request->input('tipo_reporte', 'Pacientes Atendidos');
        $turno = $request->input('turno', 'Todos');
        
        $query = DB::table('historial_triage')
            ->join('pacientes', 'historial_triage.id_paciente', '=', 'pacientes.id_paciente');

        // 1. Filtros de Fecha
        if ($request->fecha_inicio) {
            $query->whereDate('historial_triage.fecha_clasificacion', '>=', $request->fecha_inicio);
        }
        if ($request->fecha_fin) {
            $query->whereDate('historial_triage.fecha_clasificacion', '<=', $request->fecha_fin);
        }

        // 2. Filtro de Turno (Matutino: 07-15h, Vespertino: 15-23h, Nocturno: 23-07h)
        if ($turno == 'Matutino') {
            $query->whereRaw('TIME(historial_triage.fecha_clasificacion) BETWEEN "07:00:00" AND "14:59:59"');
        } elseif ($turno == 'Vespertino') {
            $query->whereRaw('TIME(historial_triage.fecha_clasificacion) BETWEEN "15:00:00" AND "22:59:59"');
        } elseif ($turno == 'Nocturno') {
            $query->whereRaw('(TIME(historial_triage.fecha_clasificacion) >= "23:00:00" OR TIME(historial_triage.fecha_clasificacion) <= "06:59:59")');
        }

        // 3. Cálculos de KPIs (Total y Promedio de espera)
        $total_atendidos = $query->count();
        
        $avg_espera = clone $query;
        $promedio_espera = $avg_espera->whereNotNull('historial_triage.fecha_atencion_medica')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, historial_triage.fecha_clasificacion, historial_triage.fecha_atencion_medica)) as promedio')
            ->value('promedio');

        // 4. Datos para la Gráfica de Dona
        $estadisticas = clone $query;
        $estadisticas = $estadisticas->select('historial_triage.color_calculado', DB::raw('count(*) as total'))
            ->groupBy('historial_triage.color_calculado')
            ->pluck('total', 'color_calculado')->toArray();

        return view('admin.reportes.index', compact('estadisticas', 'total_atendidos', 'promedio_espera', 'tipo_reporte', 'turno'));
    }

    public function descargarPdf(Request $request)
    {
        $tipo_reporte = $request->input('tipo_reporte', 'Pacientes Atendidos');
        $turno = $request->input('turno', 'Todos');
        
        $query = DB::table('historial_triage')
            ->join('pacientes', 'historial_triage.id_paciente', '=', 'pacientes.id_paciente');

        // 1. Aplicar los mismos filtros que en la pantalla web
        if ($request->fecha_inicio) {
            $query->whereDate('historial_triage.fecha_clasificacion', '>=', $request->fecha_inicio);
        }
        if ($request->fecha_fin) {
            $query->whereDate('historial_triage.fecha_clasificacion', '<=', $request->fecha_fin);
        }
        if ($turno == 'Matutino') {
            $query->whereRaw('TIME(historial_triage.fecha_clasificacion) BETWEEN "07:00:00" AND "14:59:59"');
        } elseif ($turno == 'Vespertino') {
            $query->whereRaw('TIME(historial_triage.fecha_clasificacion) BETWEEN "15:00:00" AND "22:59:59"');
        } elseif ($turno == 'Nocturno') {
            $query->whereRaw('(TIME(historial_triage.fecha_clasificacion) >= "23:00:00" OR TIME(historial_triage.fecha_clasificacion) <= "06:59:59")');
        }

        $datosTabla = clone $query;
        $datosTabla = $datosTabla->select('pacientes.nombre', 'pacientes.apellidoPat', 'historial_triage.color_calculado', 'historial_triage.fecha_clasificacion', 'historial_triage.criterio_aplicado')
            ->orderBy('historial_triage.fecha_clasificacion', 'desc')->get();

        $estadisticas = clone $query;
        $estadisticas = $estadisticas->select('historial_triage.color_calculado', DB::raw('count(*) as total'))
            ->groupBy('historial_triage.color_calculado')
            ->pluck('total', 'color_calculado')->toArray();

        // 2. Generar Gráfica de Dona como Imagen (QuickChart API) para el PDF
        $labels = []; $data = []; $colors = [];
        $colorMap = ['Rojo' => '#dc3545', 'Naranja' => '#fd7e14', 'Amarillo' => '#ffc107', 'Verde' => '#198754', 'Azul' => '#0d6efd'];
        
        foreach($estadisticas as $color => $total) {
            $labels[] = "Código " . $color;
            $data[] = $total;
            $colors[] = $colorMap[$color] ?? '#ccc';
        }

        $chartUrl = null;
        if (!empty($data)) {
            $chartConfig = [
                'type' => 'doughnut',
                'data' => [ 'labels' => $labels, 'datasets' => [[ 'data' => $data, 'backgroundColor' => $colors ]] ]
            ];
            $chartUrl = 'https://quickchart.io/chart?width=500&height=300&c=' . urlencode(json_encode($chartConfig));
        }

        // 3. Renderizar y Forzar Descarga (El return que faltaba)
        // Asegúrate de que tengas el use Barryvdh\DomPDF\Facade\Pdf; hasta arriba del archivo
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reportes.pdf', compact('datosTabla', 'chartUrl', 'tipo_reporte', 'turno', 'request'))
                  ->setOption(['isRemoteEnabled' => true]);

        return $pdf->download('Reporte_Triage_'. now()->format('Ymd_Hi') .'.pdf');
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