<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Médio - Clasificador Triage</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root { --medico-primary: #2563eb; --triage-bg: #f8fafc; --text-main: #0f172a; }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #e2e8f0; }
        .card-monitor { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; }
        
        /* Colores NOM-027 para las filas */
        .row-rojo { border-left: 6px solid #dc3545; background-color: rgba(220, 53, 69, 0.03); }
        .row-naranja { border-left: 6px solid #fd7e14; background-color: rgba(253, 126, 20, 0.03); }
        .row-amarillo { border-left: 6px solid #ffc107; background-color: rgba(255, 193, 7, 0.03); }
        .row-verde { border-left: 6px solid #198754; background-color: rgba(25, 135, 84, 0.03); }
        .row-azul { border-left: 6px solid #0d6efd; background-color: rgba(13, 110, 253, 0.03); }

        .table-custom th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: #64748b; background-color: #f1f5f9; padding: 1.2rem 1rem; border-bottom: 2px solid #e2e8f0; }
        .table-custom td { vertical-align: middle; padding: 1.2rem 1rem; border-bottom: 1px solid #e2e8f0; }
        .badge-color { padding: 0.5rem 1rem; font-weight: 700; border-radius: 6px; letter-spacing: 0.5px; }
        .wait-time { font-size: 1.1rem; font-weight: 700; color: #334155; }
    </style>
</head>
<body>

    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-clipboard2-pulse fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">MONITOR DE URGENCIAS</h5>
                <small class="text-muted fw-medium">Sala de Espera Médica</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="text-end d-none d-md-block">
                <p class="mb-0 fw-bold text-dark fs-6">{{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}</p>
                <small class="text-muted">Médico de Turno</small>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-light border shadow-sm text-danger rounded-circle" style="width: 40px; height: 40px;"><i class="bi bi-power"></i></button>
            </form>
        </div>
    </header>

    <main class="container-fluid py-5 px-4" style="max-width: 1400px;">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1"><i class="bi bi-list-ol text-primary me-2"></i>Cola de Pacientes Activos</h4>
                <p class="text-muted mb-0">Atención estrictamente basada en algoritmo de Triage (NOM-027).</p>
            </div>
            <div>
                <!-- Botón para refrescar la pantalla manualmente si el doctor lo desea -->
                <button onclick="window.location.reload();" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i> Actualizar Monitor</button>
            </div>
        </div>

        <div class="card card-monitor">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Prioridad / Código</th>
                            <th>Paciente</th>
                            <th>Motivo / Criterio Clínico</th>
                            <th class="text-center">Tiempo en Espera</th>
                            <th class="text-center pe-4">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pacientesEspera as $pac)
                            @php
                                // Configuración de estilos por color
                                $colores = [
                                    'Rojo' => ['bg' => '#dc3545', 'text' => '#fff', 'class' => 'row-rojo'],
                                    'Naranja' => ['bg' => '#fd7e14', 'text' => '#fff', 'class' => 'row-naranja'],
                                    'Amarillo' => ['bg' => '#ffc107', 'text' => '#000', 'class' => 'row-amarillo'],
                                    'Verde' => ['bg' => '#198754', 'text' => '#fff', 'class' => 'row-verde'],
                                    'Azul' => ['bg' => '#0d6efd', 'text' => '#fff', 'class' => 'row-azul']
                                ];
                                $estilo = $colores[$pac->color_calculado] ?? ['bg' => '#6c757d', 'text' => '#fff', 'class' => ''];
                                
                                // Cálculo del tiempo de espera en minutos
                                $minutosEspera = floor(\Carbon\Carbon::parse($pac->fecha_clasificacion)->diffInMinutes(now()));
                                $edad = \Carbon\Carbon::parse($pac->fechaNacimiento)->age;
                            @endphp
                            
                            <tr class="{{ $estilo['class'] }}">
                                <td class="ps-4">
                                    <span class="badge badge-color shadow-sm" style="background-color: {{ $estilo['bg'] }}; color: {{ $estilo['text'] }};">
                                        CÓDIGO {{ strtoupper($pac->color_calculado) }}
                                    </span>
                                </td>
                                <td>
                                    <h6 class="fw-bold text-dark mb-1">{{ $pac->nombre }} {{ $pac->apellidoPat }}</h6>
                                    <small class="text-muted"><i class="bi bi-person me-1"></i>{{ $pac->sexo }} • {{ $edad }} años</small>
                                </td>
                                <td>
                                    <div class="text-dark fw-medium" style="font-size: 0.9rem;">{{ $pac->criterio_aplicado }}</div>
                                    <small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>{{ $pac->sintomas_cualitativos }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="wait-time">
                                        <i class="bi bi-clock-history me-1 text-muted"></i> {{ $minutosEspera }} min
                                    </div>
                                    <small class="text-muted">Clasificado: {{ \Carbon\Carbon::parse($pac->fecha_clasificacion)->format('H:i') }}</small>
                                </td>
                                <td class="text-center pe-4">
                                    <form action="{{ route('medico.atender', $pac->id_triage) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-primary fw-bold px-4">
                                            Llamar / Atender <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-check2-circle fs-1 d-block mb-3 text-success"></i>
                                    No hay pacientes en espera en este momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Refresco automático de la página cada 60 segundos para mantener el tiempo de espera actualizado -->
    <meta http-equiv="refresh" content="60">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>