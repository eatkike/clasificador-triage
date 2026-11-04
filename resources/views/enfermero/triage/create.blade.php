<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valoración Triage - Clasificador Triage</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --triage-primary: #028090;
            --triage-primary-hover: #026670;
            --triage-bg: #f4f7f6;
            --text-main: #2b2d42;
        }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #edf2f7; }
        .sub-navbar { background-color: var(--triage-primary); }
        .nav-link-custom { color: rgba(255,255,255,0.7); font-weight: 500; padding: 0.8rem 1rem; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #ffffff; background-color: rgba(255,255,255,0.1); border-radius: 6px; }
        .card-clinical { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .card-header-clinical { background-color: transparent; border-bottom: 1px solid #edf2f7; padding: 1.25rem 1.5rem; }
        .form-control { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 1rem; font-size: 1rem; font-weight: 500; background-color: #f8fafc; }
        .form-control:focus { border-color: var(--triage-primary); box-shadow: 0 0 0 0.25rem rgba(2, 128, 144, 0.1); background-color: #fff; }
        .form-label { font-weight: 600; color: #4a5568; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group-text { background-color: #edf2f7; border-color: #e2e8f0; font-weight: 600; color: #718096; border-radius: 0 8px 8px 0; }
        .btn-triage { background-color: var(--triage-primary); color: white; border-radius: 8px; font-weight: 600; padding: 0.75rem 1.5rem; letter-spacing: 0.5px; }
        .btn-triage:hover { background-color: var(--triage-primary-hover); color: white; }
    </style>
</head>
<body>

    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-danger text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-heart-pulse fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">CLASIFICADOR TRIAGE</h5>
                <small class="text-muted fw-medium">Portal de Recepción Clínica</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="text-end d-none d-md-block">
                <p class="mb-0 fw-bold text-dark fs-6">{{ Auth::user()->nombre }}</p>
                <small class="text-muted">{{ Auth::user()->rol }}</small>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-light border shadow-sm text-danger rounded-circle"><i class="bi bi-power"></i></button>
            </form>
        </div>
    </header>

    <nav class="sub-navbar px-4 py-2 shadow-sm d-flex gap-2">
        <a href="{{ route('enfermero.pacientes.index') }}" class="nav-link-custom text-white"><i class="bi bi-arrow-left-circle me-2"></i>Volver a Pacientes</a>
        <a href="#" class="nav-link-custom active"><i class="bi bi-clipboard2-pulse me-2"></i>Valoración de Signos Vitales</a>
    </nav>

    <main class="container py-5" style="max-width: 900px;">
        
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Revise los campos marcados en rojo.
                <ul class="mb-0 mt-2 text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-clinical mb-4">
            <div class="card-body bg-light p-4 rounded-top d-flex align-items-center">
                <i class="bi bi-person-bounding-box fs-1 text-secondary me-4"></i>
                <div>
                    <h4 class="fw-bold text-dark mb-1">Paciente: {{ $paciente->nombre }} {{ $paciente->apellidoPat }}</h4>
                    <p class="text-muted mb-0"><i class="bi bi-calendar-event me-2"></i> Fecha de Nacimiento: {{ \Carbon\Carbon::parse($paciente->fechaNacimiento)->format('d/m/Y') }} &nbsp; | &nbsp; <i class="bi bi-gender-ambiguous me-2"></i>{{ $paciente->sexo }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('enfermero.triage.store', $paciente->id_paciente) }}" method="POST">
            @csrf
            <div class="card card-clinical border-0 shadow-sm">
                <div class="card-header-clinical">
                    <h6 class="fw-bold mb-0 text-dark text-uppercase"><i class="bi bi-activity text-danger me-2"></i>Captura de Signos Vitales</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Presión Sistólica</label>
                            <div class="input-group">
                                <input type="number" name="presion_sistolica" class="form-control" value="{{ old('presion_sistolica') }}" required>
                                <span class="input-group-text">mmHg</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Presión Diastólica</label>
                            <div class="input-group">
                                <input type="number" name="presion_diastolica" class="form-control" value="{{ old('presion_diastolica') }}" required>
                                <span class="input-group-text">mmHg</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Frecuencia Cardíaca</label>
                            <div class="input-group">
                                <input type="number" name="frecuencia_cardiaca" class="form-control" value="{{ old('frecuencia_cardiaca') }}" required>
                                <span class="input-group-text">lpm</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frecuencia Respiratoria</label>
                            <div class="input-group">
                                <input type="number" name="frecuencia_respiratoria" class="form-control" value="{{ old('frecuencia_respiratoria') }}" required>
                                <span class="input-group-text">rpm</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Temperatura Corporal</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="temperatura" class="form-control" value="{{ old('temperatura') }}" required>
                                <span class="input-group-text">°C</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-danger"><i class="bi bi-lungs-fill me-1"></i>Saturación de Oxígeno (SpO2)</label>
                            <div class="input-group">
                                <input type="number" name="saturacion_oxigeno" class="form-control border-danger border-opacity-50" value="{{ old('saturacion_oxigeno') }}" required>
                                <span class="input-group-text bg-danger bg-opacity-10 text-danger border-danger border-opacity-50">%</span>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <label class="form-label">Síntomas y Observaciones del Cuadro Clínico</label>
                            <textarea name="sintomas_cualitativos" class="form-control" rows="3" required>{{ old('sintomas_cualitativos') ?? $paciente->motivo_consulta }}</textarea>
                            <small class="text-muted">Describa signos evidentes (ej. palidez, diaforesis, pérdida de conciencia).</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top p-4 text-end">
                    <button type="submit" class="btn btn-triage btn-lg px-5">
                        <i class="bi bi-cpu me-2"></i>EVALUAR Y GUARDAR DICTAMEN
                    </button>
                </div>
            </div>
        </form>

    </main>
</body>
</html>