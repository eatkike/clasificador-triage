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

// Pantalla de Dashboard (Métricas generales de enfermería)
    // Dashboard con Métricas y Cola de Pacientes Clasificados
    public function dashboard()
    {
        $totalPacientes = DB::table('pacientes')->count();
        $enEspera = DB::table('pacientes')->where('estado_atencion', 'Espera de Valoración')->count();
        $atendidos = DB::table('pacientes')->where('estado_atencion', 'Alta Médica')->count();

        // Traemos a los pacientes que ya pasaron por Triage y están esperando al médico
        $clasificados = DB::table('historial_triage')
            ->join('pacientes', 'historial_triage.id_paciente', '=', 'pacientes.id_paciente')
            ->where('pacientes.estado_atencion', 'Clasificado')
            ->orderByRaw("FIELD(color_calculado, 'Rojo', 'Naranja', 'Amarillo', 'Verde', 'Azul')")
            ->orderBy('historial_triage.fecha_clasificacion', 'asc')
            ->select('pacientes.nombre', 'pacientes.apellidoPat', 'pacientes.sexo', 'historial_triage.color_calculado', 'historial_triage.fecha_clasificacion')
            ->get();

        return view('enfermero.dashboard.index', compact('totalPacientes', 'enEspera', 'atendidos', 'clasificados'));
    }

    // Directorio General con Buscador
    public function signos(Request $request)
    {
        $query = DB::table('pacientes')->orderBy('id_paciente', 'desc');

        // Si el enfermero usa la barra de búsqueda, filtramos la tabla
        if ($request->has('buscar') && $request->buscar != '') {
            $termino = $request->buscar;
            $query->where(function($q) use ($termino) {
                $q->where('nombre', 'LIKE', "%{$termino}%")
                  ->orWhere('apellidoPat', 'LIKE', "%{$termino}%")
                  ->orWhere('apellidoMat', 'LIKE', "%{$termino}%");
            });
        }

        $pacientes = $query->get();

        return view('enfermero.signos.index', compact('pacientes'));
    }

    // Mostrar formulario de toma de signos
    public function create($id_paciente)
    {
        $paciente = Paciente::findOrFail($id_paciente);
        
        if ($paciente->estado_atencion !== 'Espera de Valoración') {
            return redirect()->route('enfermero.pacientes.index')->withErrors(['msg' => 'Este paciente ya fue clasificado.']);
        }

        return view('enfermero.triage.create', compact('paciente'));
    }

    // --- NUEVO CEREBRO DEL ALGORITMO NOM-027 ---
    private function calcularColorTriage($req) {
        $color = 'Azul';
        $criterio = 'Signos vitales dentro de parámetros normales.';

        $fc = $req->frecuencia_cardiaca;
        $fr = $req->frecuencia_respiratoria;
        $sis = $req->presion_sistolica;
        $dia = $req->presion_diastolica;
        $spo2 = $req->saturacion_oxigeno;
        $temp = $req->temperatura;
        $sintomas = $req->input('condiciones', []); 

        // Extraer los parámetros dinámicos desde la base de datos (FR6)
        $limiteFiebreNaranja = DB::table('parametros_triage')->where('clave_parametro', 'temp_fiebre_naranja')->value('valor') ?? 40.00;
        $limiteFiebreAmarillo = DB::table('parametros_triage')->where('clave_parametro', 'temp_fiebre_amarillo')->value('valor') ?? 39.00;
        $limiteFiebreVerde = DB::table('parametros_triage')->where('clave_parametro', 'temp_fiebre_verde')->value('valor') ?? 38.00;

        if (in_array('Inconsciente / Paro', $sintomas) || in_array('Hemorragia Severa', $sintomas) || $spo2 <= 85 || $fc <= 40 || $fc >= 130 || $fr <= 10 || $fr >= 35 || $sis <= 70) {
            return ['color' => 'Rojo', 'criterio' => 'Compromiso vital inminente. Pasar directo a choque.'];
        } 
        // USANDO EL PARÁMETRO DINÁMICO (NARANJA)
        elseif (in_array('Dif. Respiratoria Severa', $sintomas) || in_array('Alteración Mental', $sintomas) || $spo2 <= 89 || $sis >= 200 || $sis <= 90 || $dia >= 120 || $temp >= $limiteFiebreNaranja || $temp <= 35) {
            return ['color' => 'Naranja', 'criterio' => 'Emergencia. Riesgo de pérdida de función u órgano.'];
        } 
        // USANDO EL PARÁMETRO DINÁMICO (AMARILLO)
        elseif (in_array('Fractura Expuesta', $sintomas) || $spo2 <= 94 || $sis >= 160 || $temp >= $limiteFiebreAmarillo) {
            return ['color' => 'Amarillo', 'criterio' => 'Urgencia calificada. Requiere atención rápida.'];
        } 
        // USANDO EL PARÁMETRO DINÁMICO (VERDE)
        elseif (in_array('Dolor Leve/Moderado', $sintomas) || $temp >= $limiteFiebreVerde || $sis >= 140) {
            return ['color' => 'Verde', 'criterio' => 'Urgencia menor. Paciente estable.'];
        }

        return ['color' => $color, 'criterio' => $criterio];
    }

    // --- GUARDAR NUEVO TRIAGE ---
    public function store(Request $request, $id_paciente)
    {
        // Límites Rígidos (Hard Limits) Backend
        $request->validate([
            'temperatura' => 'required|numeric|min:20|max:45',
            'frecuencia_cardiaca' => 'required|integer|min:0|max:300',
            'frecuencia_respiratoria' => 'required|integer|min:0|max:100',
            'presion_sistolica' => 'required|integer|min:0|max:300',
            'presion_diastolica' => 'required|integer|min:0|max:200',
            'saturacion_oxigeno' => 'required|integer|min:0|max:100',
        ]);

        $analisis = $this->calcularColorTriage($request);
        $sintomasStr = implode(', ', $request->input('condiciones', []));

        // Insertamos los signos vitales INCLUYENDO la hora de captura
        DB::table('signos_vitales')->insert([
            'id_paciente' => $id_paciente,
            'id_enfermero' => auth()->user()->id_usuario, 
            'temperatura' => $request->temperatura, 
            'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
            'frecuencia_respiratoria' => $request->frecuencia_respiratoria, 
            'presion_sistolica' => $request->presion_sistolica,
            'presion_diastolica' => $request->presion_diastolica, 
            'saturacion_oxigeno' => $request->saturacion_oxigeno,
            'sintomas_cualitativos' => $sintomasStr,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('historial_triage')->insert([
            'id_paciente' => $id_paciente,
            'id_enfermero' => auth()->user()->id_usuario, 
            'color_calculado' => $analisis['color'],
            'criterio_aplicado' => $analisis['criterio'], 
            'fecha_clasificacion' => now()
        ]);

        DB::table('pacientes')->where('id_paciente', $id_paciente)->update([
            'estado_atencion' => 'Clasificado', 
            'updated_at' => now()
        ]);

        return redirect()->route('enfermero.dashboard')->with('success', "Clasificado como Código {$analisis['color']}");
    }

    // --- CARGAR VISTA PARA EDITAR ---
    public function edit($id_paciente)
    {
        $paciente = DB::table('pacientes')->where('id_paciente', $id_paciente)->first();
        $signos = DB::table('signos_vitales')->where('id_paciente', $id_paciente)->orderBy('id_signos', 'desc')->first();
        $triage = DB::table('historial_triage')->where('id_paciente', $id_paciente)->orderBy('id_triage', 'desc')->first();

        return view('enfermero.triage.edit', compact('paciente', 'signos', 'triage'));
    }

    // --- ACTUALIZAR TRIAGE ---
    public function update(Request $request, $id_paciente)
    {
        $request->validate(['temperatura' => 'required|numeric|min:20|max:45', 'frecuencia_cardiaca' => 'required|integer|min:0|max:300', 'frecuencia_respiratoria' => 'required|integer|min:0|max:100', 'presion_sistolica' => 'required|integer|min:0|max:300', 'presion_diastolica' => 'required|integer|min:0|max:200', 'saturacion_oxigeno' => 'required|integer|min:0|max:100']);

        $analisis = $this->calcularColorTriage($request);
        $sintomasStr = implode(', ', $request->input('condiciones', []));

        $signos = DB::table('signos_vitales')->where('id_paciente', $id_paciente)->orderBy('id_signos', 'desc')->first();
        $triage = DB::table('historial_triage')->where('id_paciente', $id_paciente)->orderBy('id_triage', 'desc')->first();

        DB::table('signos_vitales')->where('id_signos', $signos->id_signos)->update(['temperatura' => $request->temperatura, 'frecuencia_cardiaca' => $request->frecuencia_cardiaca, 'frecuencia_respiratoria' => $request->frecuencia_respiratoria, 'presion_sistolica' => $request->presion_sistolica, 'presion_diastolica' => $request->presion_diastolica, 'saturacion_oxigeno' => $request->saturacion_oxigeno, 'sintomas_cualitativos' => $sintomasStr]);
        DB::table('historial_triage')->where('id_triage', $triage->id_triage)->update(['color_calculado' => $analisis['color'], 'criterio_aplicado' => $analisis['criterio']]);

        return redirect()->route('enfermero.signos')->with('success', "Triage actualizado. Nuevo código: {$analisis['color']}");
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