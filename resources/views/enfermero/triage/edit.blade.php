<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Triage - Clasificador Triage</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root { --triage-primary: #fd7e14; --triage-primary-hover: #e8590c; --triage-bg: #f8f9fa; --text-main: #212529; }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #dee2e6; }
        .sub-navbar { background-color: var(--triage-primary); }
        .nav-link-custom { color: rgba(255,255,255,0.8); font-weight: 500; padding: 0.8rem 1rem; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #ffffff; background-color: rgba(255,255,255,0.2); border-radius: 6px; }
        .card-clinical { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .card-header-clinical { background-color: transparent; border-bottom: 1px solid #dee2e6; padding: 1.25rem 1.5rem; }
        .form-control { border: 1px solid #ced4da; border-radius: 8px; padding: 0.6rem 1rem; font-weight: 500; }
        .form-control:focus { border-color: var(--triage-primary); box-shadow: 0 0 0 0.25rem rgba(253, 126, 20, 0.25); }
        .form-label { font-weight: 600; color: #495057; font-size: 0.85rem; text-transform: uppercase; }
        .btn-triage { background-color: var(--triage-primary); color: white; border-radius: 8px; font-weight: 600; padding: 0.75rem 1.5rem; }
        .btn-triage:hover { background-color: var(--triage-primary-hover); color: white; }
    </style>
</head>
<body>

    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-warning text-dark rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-pencil-square fs-5"></i></div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">MODO EDICIÓN TRIAGE</h5>
                <small class="text-muted fw-medium">Corrección de Expediente Clínico</small>
            </div>
        </div>
        <div class="text-end d-none d-md-block">
            <p class="mb-0 fw-bold text-dark fs-6">{{ Auth::user()->nombre }}</p>
            <small class="text-muted">{{ Auth::user()->rol }}</small>
        </div>
    </header>

    <nav class="sub-navbar px-4 py-2 shadow-sm d-flex gap-2">
        <a href="{{ route('enfermero.signos') }}" class="nav-link-custom text-white"><i class="bi bi-arrow-left-circle me-2"></i>Cancelar y Volver al Directorio</a>
    </nav>

    <main class="container py-5" style="max-width: 900px;">
        <div class="alert alert-warning border-warning border-opacity-50 shadow-sm rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-2 text-warning fs-5"></i> <strong>Aviso:</strong> Está modificando un dictamen Triage previamente guardado. El nuevo color reemplazará al anterior en el expediente.
        </div>

        <form action="{{ route('enfermero.triage.update', $paciente->id_paciente) }}" method="POST" id="formTriage">
            @csrf
            @method('PUT')
            <div class="card card-clinical border-0 shadow-sm">
                <div class="card-header-clinical d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark text-uppercase"><i class="bi bi-activity text-warning me-2"></i>Corrección de Signos Vitales</h6>
                    <span class="badge bg-secondary">Paciente: {{ $paciente->nombre }} {{ $paciente->apellidoPat }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Presión Sistólica</label>
                            <div class="input-group"><input type="number" name="presion_sistolica" class="form-control" value="{{ $signos->presion_sistolica }}" min="0" max="300" required><span class="input-group-text">mmHg</span></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Presión Diastólica</label>
                            <div class="input-group"><input type="number" name="presion_diastolica" class="form-control" value="{{ $signos->presion_diastolica }}" min="0" max="200" required><span class="input-group-text">mmHg</span></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frecuencia Cardíaca</label>
                            <div class="input-group"><input type="number" name="frecuencia_cardiaca" class="form-control" value="{{ $signos->frecuencia_cardiaca }}" min="0" max="300" required><span class="input-group-text">lpm</span></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frecuencia Respiratoria</label>
                            <div class="input-group"><input type="number" name="frecuencia_respiratoria" class="form-control" value="{{ $signos->frecuencia_respiratoria }}" min="0" max="100" required><span class="input-group-text">rpm</span></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Temperatura Corporal</label>
                            <div class="input-group"><input type="number" step="0.1" name="temperatura" class="form-control" value="{{ $signos->temperatura }}" min="20" max="45" required><span class="input-group-text">°C</span></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-danger"><i class="bi bi-lungs-fill me-1"></i>Saturación Oxígeno (SpO2)</label>
                            <div class="input-group"><input type="number" name="saturacion_oxigeno" class="form-control border-danger" value="{{ $signos->saturacion_oxigeno }}" min="0" max="100" required><span class="input-group-text bg-danger bg-opacity-10 text-danger">%</span></div>
                        </div>

                        <!-- Checkboxes Cualitativos (Precargados si existen) -->
                        @php $sintomasArray = explode(', ', $signos->sintomas_cualitativos); @endphp
                        <div class="col-12 mt-4 p-3 bg-light border rounded">
                            <label class="form-label text-dark fw-bold mb-3"><i class="bi bi-clipboard2-plus me-2"></i>Condiciones Clínicas Agudas (Marcadores de Severidad)</label>
                            <div class="row g-3">
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="condiciones[]" value="Inconsciente / Paro" id="chk1" {{ in_array('Inconsciente / Paro', $sintomasArray) ? 'checked' : '' }}><label class="form-check-label text-danger fw-bold" for="chk1">Inconsciente / Paro</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="condiciones[]" value="Hemorragia Severa" id="chk2" {{ in_array('Hemorragia Severa', $sintomasArray) ? 'checked' : '' }}><label class="form-check-label text-danger fw-bold" for="chk2">Hemorragia Severa</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="condiciones[]" value="Dif. Respiratoria Severa" id="chk3" {{ in_array('Dif. Respiratoria Severa', $sintomasArray) ? 'checked' : '' }}><label class="form-check-label text-warning fw-bold" for="chk3">Dificultad Respiratoria</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="condiciones[]" value="Fractura Expuesta" id="chk4" {{ in_array('Fractura Expuesta', $sintomasArray) ? 'checked' : '' }}><label class="form-check-label text-warning fw-bold" for="chk4">Fractura Expuesta</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="condiciones[]" value="Alteración Mental" id="chk5" {{ in_array('Alteración Mental', $sintomasArray) ? 'checked' : '' }}><label class="form-check-label text-warning fw-bold" for="chk5">Alteración Mental</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="condiciones[]" value="Dolor Leve/Moderado" id="chk6" {{ in_array('Dolor Leve/Moderado', $sintomasArray) ? 'checked' : '' }}><label class="form-check-label text-success fw-bold" for="chk6">Dolor Leve/Moderado</label></div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top p-4 text-end">
                    <button type="submit" class="btn btn-triage btn-lg px-5"><i class="bi bi-arrow-repeat me-2"></i>ACTUALIZAR DICTAMEN</button>
                </div>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('formTriage').addEventListener('submit', function(e) {
            let warnings = [];
            let sis = parseInt(document.querySelector('[name="presion_sistolica"]').value);
            let dia = parseInt(document.querySelector('[name="presion_diastolica"]').value);
            let fc = parseInt(document.querySelector('[name="frecuencia_cardiaca"]').value);
            let fr = parseInt(document.querySelector('[name="frecuencia_respiratoria"]').value);
            let temp = parseFloat(document.querySelector('[name="temperatura"]').value);
            let spo2 = parseInt(document.querySelector('[name="saturacion_oxigeno"]').value);

            if(temp < 35.5 || temp > 39.5) warnings.push(`- Temperatura inusual (${temp}°C)`);
            if(fc < 45 || fc > 140) warnings.push(`- Frecuencia cardíaca inusual (${fc} lpm)`);
            if(fr < 12 || fr > 25) warnings.push(`- Frecuencia respiratoria inusual (${fr} rpm)`);
            if(sis < 90 || sis > 180) warnings.push(`- Tensión sistólica inusual (${sis} mmHg)`);
            if(dia < 50 || dia > 120) warnings.push(`- Tensión diastólica inusual (${dia} mmHg)`);
            if(spo2 < 88) warnings.push(`- Saturación de O2 extremadamente baja (${spo2}%)`);

            if(warnings.length > 0) {
                let confirmacion = confirm("⚠️ ADVERTENCIA CLÍNICA DE RANGOS\n\nSe detectaron valores que podrían indicar un compromiso vital severo o un error de captura:\n\n" + warnings.join("\n") + "\n\n¿Confirma que estos datos son correctos y proceden del monitor de signos?");
                if(!confirmacion) e.preventDefault();
            }
        });
    </script>
</body>
</html>