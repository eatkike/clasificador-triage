<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generación de Reportes - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root { --triage-primary: #0f4c5c; --triage-bg: #f4f6f8; }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #edf2f7; }
        .sub-navbar { background-color: var(--triage-primary); }
        .nav-link-custom { color: rgba(255,255,255,0.7); padding: 0.8rem 1rem; text-decoration: none; transition: 0.2s; }
        .nav-link-custom.active { color: #fff; background-color: rgba(255,255,255,0.1); border-radius: 6px; }
        .panel-header { background-color: #e2e8f0; padding: 10px 20px; font-weight: 700; color: #334155; border-radius: 8px 8px 0 0; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        .panel-body { background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #475569; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .stat-label { font-size: 0.85rem; color: #64748b; }
    </style>
</head>
<body>

    <!-- Menús Superiores -->
    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-hospital text-danger me-2"></i>CLASIFICADOR TRIAGE <span class="fs-6 text-muted fw-normal ms-2">Admin Portal</span></h5>
        </div>
        <div>
            <span class="text-muted me-3">Rol: Administrador | <a href="#" class="text-danger text-decoration-none fw-bold">Cerrar Sesión <i class="bi bi-power"></i></a></span>
        </div>
    </header>

    <nav class="sub-navbar px-4 py-2 d-flex gap-2">
        <a href="{{ route('admin.personal.index') }}" class="nav-link-custom">Personal Clínico</a>
        <a href="{{ route('admin.catalogo.index') }}" class="nav-link-custom">Catálogos Triage</a>
        <a href="{{ route('admin.reportes') }}" class="nav-link-custom active">Reportes</a>
        <a href="{{ route('admin.respaldos.index') }}" class="nav-link-custom"><i class="bi bi-database-down me-2"></i>Respaldos BD</a>    
    </nav>

    <main class="container py-4" style="max-width: 1100px;">
        <h4 class="fw-bold mb-4 text-danger">+ GENERACIÓN DE REPORTES ESTADÍSTICOS</h4>

        <div class="row g-4">
            <!-- PANEL IZQUIERDO: FILTROS -->
            <div class="col-md-5">
                <div class="panel-header">Filtrar Datos</div>
                <div class="panel-body h-100">
                    <form action="{{ route('admin.reportes') }}" method="GET" id="formFiltros">
                        <div class="mb-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Turno</label>
                            <select name="turno" class="form-select">
                                <option value="Todos" {{ request('turno') == 'Todos' ? 'selected' : '' }}>Todos</option>
                                <option value="Matutino" {{ request('turno') == 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                <option value="Vespertino" {{ request('turno') == 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                <option value="Nocturno" {{ request('turno') == 'Nocturno' ? 'selected' : '' }}>Nocturno</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Tipo de Reporte</label>
                            <select name="tipo_reporte" class="form-select">
                                <option value="Pacientes Atendidos" {{ request('tipo_reporte') == 'Pacientes Atendidos' ? 'selected' : '' }}>Pacientes Atendidos</option>
                                <option value="Saturación Horaria" {{ request('tipo_reporte') == 'Saturación Horaria' ? 'selected' : '' }}>Saturación Horaria</option>
                                <option value="Tiempos de Espera" {{ request('tipo_reporte') == 'Tiempos de Espera' ? 'selected' : '' }}>Tiempos de Espera</option>
                                <option value="Incidencias" {{ request('tipo_reporte') == 'Incidencias' ? 'selected' : '' }}>Incidencias</option>
                            </select>
                        </div>
                        
                        <!-- Botón para actualizar la vista previa en la misma página -->
                        <button type="submit" class="btn text-white fw-bold w-100 py-2" style="background-color: var(--triage-primary);">
                            <i class="bi bi-arrow-clockwise me-2"></i> ACTUALIZAR PREVISUALIZACIÓN
                        </button>
                    </form>
                </div>
            </div>

            <!-- PANEL DERECHO: PREVISUALIZACIÓN Y DESCARGA -->
            <div class="col-md-7">
                <div class="panel-header">Previsualización de Estadísticas</div>
                <div class="panel-body d-flex flex-column h-100">
                    
                    <h6 class="fw-bold mb-4 text-center">Gráfica de {{ $tipo_reporte }} por Color Triage</h6>
                    
                    <div class="row align-items-center flex-grow-1 mb-4">
                        <div class="col-7">
                            @if(empty($estadisticas))
                                <p class="text-muted text-center">Sin datos en el rango seleccionado.</p>
                            @else
                                <!-- Gráfica de Dona como en el boceto -->
                                <canvas id="chartPreview"></canvas>
                            @endif
                        </div>
                        <div class="col-5">
                            <!-- KPIs Centrales -->
                            <div class="mb-4">
                                <div class="stat-label">Total Atendidos:</div>
                                <div class="stat-value">{{ $total_atendidos }} pacientes</div>
                            </div>
                            <div>
                                <div class="stat-label">Promedio de Espera:</div>
                                <div class="stat-value">{{ $promedio_espera ? number_format($promedio_espera, 1) : '0' }} min</div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción Final -->
                    <div class="d-flex gap-2 mt-auto pt-3 border-top">
                        <!-- Este botón manda exactamente los filtros actuales a la ruta de descarga PDF -->
                        <a href="{{ route('admin.reportes.pdf', ['fecha_inicio' => request('fecha_inicio'), 'fecha_fin' => request('fecha_fin'), 'turno' => request('turno'), 'tipo_reporte' => request('tipo_reporte')]) }}" 
                           class="btn btn-primary fw-bold flex-grow-1 py-2">
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i> DESCARGAR REPORTE EN PDF
                        </a>
                        <button type="button" onclick="window.print()" class="btn btn-secondary fw-bold px-4 py-2">
                            <i class="bi bi-printer-fill me-2"></i> IMPRIMIR
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>

    @if(!empty($estadisticas))
    <script>
        const ctx = document.getElementById('chartPreview').getContext('2d');
        const colorMap = { 'Rojo': '#dc3545', 'Naranja': '#fd7e14', 'Amarillo': '#ffc107', 'Verde': '#198754', 'Azul': '#0d6efd' };
        const dataStats = @json($estadisticas);
        
        new Chart(ctx, {
            type: 'doughnut', // Tipo Dona para dejar el centro vacío como en tu imagen
            data: { 
                labels: Object.keys(dataStats), 
                datasets: [{ 
                    data: Object.values(dataStats), 
                    backgroundColor: Object.keys(dataStats).map(c => colorMap[c] || '#ccc')
                }] 
            },
            options: { 
                responsive: true, 
                cutout: '60%', // Grosor de la dona
                plugins: { legend: { position: 'left' } } 
            }
        });
    </script>
    @endif

</body>
</html>