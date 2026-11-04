<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Clínico - Clasificador Triage</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --triage-primary: #1d3557; /* Azul marino ejecutivo para los médicos */
            --triage-bg: #f8f9fa;
        }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #edf2f7; }
        
        /* Tarjetas de Paciente */
        .card-patient { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: transform 0.2s; overflow: hidden; }
        .card-patient:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        
        /* Indicadores de Triage (Colores NOM-027) */
        .border-rojo { border-left: 6px solid #e63946 !important; }
        .border-naranja { border-left: 6px solid #f4a261 !important; }
        .border-amarillo { border-left: 6px solid #e9c46a !important; }
        .border-verde { border-left: 6px solid #2a9d8f !important; }
        .border-azul { border-left: 6px solid #457b9d !important; }

        .bg-rojo { background-color: rgba(230, 57, 70, 0.1); color: #e63946; }
        .bg-naranja { background-color: rgba(244, 162, 97, 0.1); color: #d97706; }
        .bg-amarillo { background-color: rgba(233, 196, 106, 0.1); color: #b4860b; }
        .bg-verde { background-color: rgba(42, 157, 143, 0.1); color: #2a9d8f; }
        .bg-azul { background-color: rgba(69, 123, 157, 0.1); color: #457b9d; }

        .btn-atender { border-radius: 8px; font-weight: 600; letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-display fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">MONITOR DE URGENCIAS</h5>
                <small class="text-muted fw-medium">Panel Médico</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="text-end d-none d-md-block">
                <p class="mb-0 fw-bold text-dark fs-6">Dr. {{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}</p>
                <small class="text-muted">Especialista en Turno</small>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-light border shadow-sm text-danger rounded-circle"><i class="bi bi-power"></i></button>
            </form>
        </div>
    </header>

    <main class="container-fluid px-4 py-5" style="max-width: 1400px;">
        
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Pacientes en Espera de Atención</h4>
                <p class="text-muted mb-0">Ordenados automáticamente por prioridad clínica.</p>
            </div>
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><i class="bi bi-person-lines-fill me-2"></i> {{ $pacientesEnEspera->count() }} Pacientes</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            @forelse($pacientesEnEspera as $paciente)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card card-patient h-100 border-{{ strtolower($paciente->color_calculado) }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-{{ strtolower($paciente->color_calculado) }} fw-bold px-3 py-2 rounded-3 text-uppercase">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i> CÓDIGO {{ $paciente->color_calculado }}
                                </span>
                                <small class="text-muted fw-bold" title="Hora de clasificación">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($paciente->fecha_clasificacion)->format('H:i') }}
                                </small>
                            </div>
                            
                            <h5 class="fw-bold text-dark mb-1">{{ $paciente->nombre }} {{ $paciente->apellidoPat }}</h5>
                            <p class="text-muted small mb-3">
                                {{ \Carbon\Carbon::parse($paciente->fechaNacimiento)->age }} años | {{ $paciente->sexo }}
                            </p>

                            <div class="bg-light rounded p-3 mb-4 border border-light-subtle">
                                <p class="text-dark small fw-medium mb-1"><i class="bi bi-clipboard2-pulse me-1 text-muted"></i> Criterio del Algoritmo:</p>
                                <p class="text-muted small mb-0">{{ $paciente->criterio_aplicado }}</p>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 p-4 pt-0">
                            <form action="{{ route('medico.atender', $paciente->id_triage) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-atender w-100">
                                    LLAMAR A CONSULTORIO <i class="bi bi-arrow-right-short fs-5 align-middle"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="bg-white rounded-4 shadow-sm p-5 border d-inline-block">
                        <i class="bi bi-cup-hot text-muted" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold text-dark mt-3">Sala de Espera Vacía</h5>
                        <p class="text-muted mb-0">No hay pacientes clasificados pendientes de atención.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>