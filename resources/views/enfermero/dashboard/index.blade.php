<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Urgencias - Clasificador Triage</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --triage-primary: #028090; --triage-bg: #f4f7f6; --text-main: #2b2d42; }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #edf2f7; }
        .sub-navbar { background-color: var(--triage-primary); }
        .nav-link-custom { color: rgba(255,255,255,0.7); font-weight: 500; padding: 0.8rem 1rem; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #ffffff; background-color: rgba(255,255,255,0.1); border-radius: 6px; }
        .card-clinical { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .metric-card { transition: transform 0.2s; }
        .metric-card:hover { transform: translateY(-5px); }
        .border-l-primary { border-left: 5px solid var(--triage-primary) !important; }
        .border-l-warning { border-left: 5px solid #f59e0b !important; }
        .border-l-success { border-left: 5px solid #10b981 !important; }
    </style>
</head>
<body>

    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-danger text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-heart-pulse fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">CLASIFICADOR TRIAGE</h5>
                <small class="text-muted fw-medium">Portal de Recepción Clínica</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="text-end d-none d-md-block">
                <p class="mb-0 fw-bold text-dark fs-6">{{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}</p>
                <small class="text-muted">{{ Auth::user()->rol }}</small>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-light border shadow-sm text-danger rounded-circle" style="width: 40px; height: 40px;"><i class="bi bi-power"></i></button>
            </form>
        </div>
    </header>

    <nav class="sub-navbar px-4 py-2 shadow-sm d-flex gap-2 overflow-auto">
        <a href="{{ route('enfermero.dashboard') }}" class="nav-link-custom {{ request()->routeIs('enfermero.dashboard') ? 'active' : '' }}"><i class="bi bi-display me-2"></i>Dashboard</a>
        <a href="{{ route('enfermero.pacientes.index') }}" class="nav-link-custom {{ request()->routeIs('enfermero.pacientes.index') ? 'active' : '' }}"><i class="bi bi-person-plus me-2"></i>Recepción (Fila Activa)</a>
        <a href="{{ route('enfermero.signos') }}" class="nav-link-custom {{ request()->routeIs('enfermero.signos') ? 'active' : '' }}"><i class="bi bi-folder2-open me-2"></i>Directorio de Pacientes</a>
    </nav>

    <main class="container-fluid px-4 py-5" style="max-width: 1400px;">
        <h4 class="fw-bold mb-4">Resumen del Turno</h4>
        
        <!-- Tarjetas de Métricas -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card card-clinical metric-card border-l-warning h-100 p-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold mb-1">EN ESPERA DE TRIAGE</h6>
                            <h2 class="fw-bold mb-0 text-dark">{{ $enEspera }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning fs-3"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-clinical metric-card border-l-primary h-100 p-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold mb-1">TOTAL REGISTRADOS</h6>
                            <h2 class="fw-bold mb-0 text-dark">{{ $totalPacientes }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fs-3" style="color: var(--triage-primary) !important;"><i class="bi bi-people"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-clinical metric-card border-l-success h-100 p-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold mb-1">PACIENTES DADOS DE ALTA</h6>
                            <h2 class="fw-bold mb-0 text-dark">{{ $atendidos }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success fs-3"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Pacientes Clasificados -->
        <div class="card card-clinical mt-4 shadow-sm">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-activity text-primary me-2"></i>Pacientes Valorados en Sala de Espera (Vista Médica)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Paciente</th>
                                <th>Sexo</th>
                                <th>Clasificación Triage (NOM-027)</th>
                                <th>Hora de Valoración</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clasificados as $clasificado)
                                @php
                                    $colores = [
                                        'Rojo' => ['bg' => '#dc3545', 'text' => '#fff', 'icon' => 'bi-exclamation-octagon-fill'],
                                        'Naranja' => ['bg' => '#fd7e14', 'text' => '#fff', 'icon' => 'bi-exclamation-triangle-fill'],
                                        'Amarillo' => ['bg' => '#ffc107', 'text' => '#000', 'icon' => 'bi-exclamation-circle-fill'],
                                        'Verde' => ['bg' => '#198754', 'text' => '#fff', 'icon' => 'bi-check-circle-fill'],
                                        'Azul' => ['bg' => '#0d6efd', 'text' => '#fff', 'icon' => 'bi-info-circle-fill']
                                    ];
                                    $estilo = $colores[$clasificado->color_calculado] ?? ['bg' => '#6c757d', 'text' => '#fff', 'icon' => 'bi-circle-fill'];
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $clasificado->nombre }} {{ $clasificado->apellidoPat }}</td>
                                    <td>{{ $clasificado->sexo }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $estilo['bg'] }}; color: {{ $estilo['text'] }}; padding: 0.5rem 0.8rem; font-size: 0.8rem;">
                                            <i class="bi {{ $estilo['icon'] }} me-1"></i> {{ strtoupper($clasificado->color_calculado) }}
                                        </span>
                                    </td>
                                    <td class="text-muted"><i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($clasificado->fecha_clasificacion)->format('H:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No hay pacientes valorados esperando en fila.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>