<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio Médico - Clasificador Triage</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --triage-primary: #1d3557; 
            --triage-primary-hover: #14253d;
            --triage-bg: #f8f9fa;
        }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; color: #2b2d42; }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #edf2f7; }
        
        .card-clinical { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header-clinical { border-bottom: 1px solid #edf2f7; padding: 1.25rem 1.5rem; background-color: white; }
        
        .vital-box { background-color: #f8fafc; border: 1px solid #edf2f7; border-radius: 8px; padding: 1rem; text-align: center; }
        .vital-value { font-size: 1.5rem; font-weight: 700; color: var(--triage-primary); font-family: monospace; }
        .vital-label { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 0;}
        
        .form-control { border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; font-size: 0.95rem; }
        .form-control:focus { border-color: var(--triage-primary); box-shadow: 0 0 0 0.25rem rgba(29, 53, 87, 0.1); }
        .btn-triage { background-color: var(--triage-primary); color: white; border-radius: 8px; font-weight: 600; padding: 0.75rem 1.5rem; letter-spacing: 0.5px; }
        .btn-triage:hover { background-color: var(--triage-primary-hover); color: white; }
    </style>
</head>
<body>

    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('medico.dashboard') }}" class="btn btn-light border text-secondary rounded-circle me-2" title="Volver al Monitor">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-heart-pulse fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">CONSULTORIO MÉDICO</h5>
                <small class="text-muted fw-medium">Expediente Clínico en Curso</small>
            </div>
        </div>
        <div>
            <p class="mb-0 fw-bold text-dark fs-6 d-inline-block me-3">Dr. {{ Auth::user()->nombre }}</p>
            <span class="badge bg-{{ strtolower($expediente->color_calculado) }} px-3 py-2 text-uppercase rounded-pill border">
                Triage {{ $expediente->color_calculado }}
            </span>
        </div>
    </header>

    <main class="container-fluid px-4 py-5" style="max-width: 1400px;">
        <div class="row g-4">
            
            <!-- Columna Izquierda: Información del Paciente y Signos -->
            <div class="col-lg-5">
                <div class="card card-clinical mb-4">
                    <div class="card-header-clinical bg-primary bg-opacity-10 d-flex align-items-center gap-3">
                        <i class="bi bi-person-bounding-box fs-1 text-primary"></i>
                        <div>
                            <h5 class="fw-bold mb-0 text-primary">{{ $expediente->nombre }} {{ $expediente->apellidoPat }} {{ $expediente->apellidoMat }}</h5>
                            <small class="text-dark fw-medium">{{ \Carbon\Carbon::parse($expediente->fechaNacimiento)->age }} años | Sexo: {{ $expediente->sexo }} | Acompañante: {{ $expediente->contacto_familiar ?? 'Ninguno' }}</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark text-uppercase small mb-3"><i class="bi bi-journal-medical me-2"></i>Motivo de Consulta</h6>
                        <p class="text-muted bg-light p-3 rounded border border-light-subtle mb-4">{{ $expediente->motivo_consulta }}</p>

                        <h6 class="fw-bold text-dark text-uppercase small mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Antecedentes Clínicos</h6>
                        <p class="text-muted bg-light p-3 rounded border border-light-subtle mb-4">{{ $expediente->antecedentes_clinicos ?: 'Ninguno registrado por el paciente.' }}</p>
                        
                        <h6 class="fw-bold text-dark text-uppercase small mb-3"><i class="bi bi-activity me-2"></i>Lectura de Signos Vitales (Triage)</h6>
                        <div class="row g-3">
                            <div class="col-6"><div class="vital-box"><p class="vital-label">T.A.</p><div class="vital-value">{{ $expediente->presion_sistolica }}/{{ $expediente->presion_diastolica }}</div></div></div>
                            <div class="col-6"><div class="vital-box"><p class="vital-label">F.C.</p><div class="vital-value">{{ $expediente->frecuencia_cardiaca }} <small class="fs-6">lpm</small></div></div></div>
                            <div class="col-6"><div class="vital-box"><p class="vital-label">SpO2</p><div class="vital-value text-danger">{{ $expediente->saturacion_oxigeno }}<small class="fs-6">%</small></div></div></div>
                            <div class="col-6"><div class="vital-box"><p class="vital-label">Temp</p><div class="vital-value">{{ $expediente->temperatura }}<small class="fs-6">°C</small></div></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Redacción de Notas y Alta -->
            <div class="col-lg-7">
                <div class="card card-clinical h-100">
                    <div class="card-header-clinical d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Nota de Evolución Médica</h6>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        
                        <div class="bg-light p-3 rounded mb-4 border border-light-subtle">
                            <p class="fw-bold text-dark small text-uppercase mb-1">Criterio Algorítmico Disparado:</p>
                            <p class="text-muted small mb-0">{{ $expediente->criterio_aplicado }}</p>
                        </div>

                        <form action="{{ route('medico.alta', $expediente->id_triage) }}" method="POST" class="flex-grow-1 d-flex flex-column">
                            @csrf
                            <div class="flex-grow-1 mb-4">
                                <label class="form-label text-uppercase small fw-bold text-secondary">Redactar diagnóstico, tratamiento o derivación:</label>
                                <textarea name="notas_medicas" class="form-control" style="height: 250px; resize: none;" placeholder="Escriba aquí la nota de evolución clínica, diagnóstico médico, medicamentos recetados o indicaciones de traslado..." required></textarea>
                            </div>
                            
                            <div class="text-end border-top pt-4">
                                <button type="submit" class="btn btn-triage btn-lg px-5" onclick="return confirm('¿Está seguro de firmar el alta? Esto cerrará el expediente de urgencias del paciente.')">
                                    <i class="bi bi-file-earmark-check me-2"></i> FIRMAR Y DAR DE ALTA
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>