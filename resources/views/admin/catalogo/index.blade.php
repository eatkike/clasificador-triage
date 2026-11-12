<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo Triage - Portal de Administración</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --triage-primary: #0f4c5c;
            --triage-primary-hover: #0a3641;
            --triage-bg: #f8f9fa;
            --text-main: #2b2d42;
            --text-muted: #6c757d;
        }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #edf2f7; }
        .sub-navbar { background-color: var(--triage-primary); }
        .nav-link-custom { color: rgba(255,255,255,0.7); font-weight: 500; padding: 0.8rem 1rem; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #ffffff; background-color: rgba(255,255,255,0.1); border-radius: 6px; }
        .card-clinical { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
        .card-header-clinical { background-color: transparent; border-bottom: 1px solid #edf2f7; padding: 1.25rem 1.5rem; }
        .form-control { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 1rem; font-size: 0.95rem; }
        .form-control:focus { border-color: var(--triage-primary); box-shadow: 0 0 0 0.25rem rgba(15, 76, 92, 0.1); }
        .table-custom td { vertical-align: middle; padding: 0.8rem 1rem; border-bottom: 1px solid #edf2f7; }
        .table-custom tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>

    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-danger text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-hospital fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">CLASIFICADOR TRIAGE</h5>
                <small class="text-muted fw-medium">Portal de Administración</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="text-end d-none d-md-block">
                <p class="mb-0 fw-bold text-dark fs-6">{{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}</p>
                <small class="text-muted">{{ Auth::user()->rol }}</small>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-light border shadow-sm text-danger rounded-circle" style="width: 40px; height: 40px;" title="Cerrar Sesión">
                    <i class="bi bi-power"></i>
                </button>
            </form>
        </div>
    </header>

    <nav class="sub-navbar px-4 py-2 shadow-sm d-flex gap-2 overflow-auto">
        <a href="{{ route('admin.personal.index') }}" class="nav-link-custom"><i class="bi bi-people me-2"></i>Personal Clínico</a>
        <a href="{{ route('admin.catalogo.index') }}" class="nav-link-custom active"><i class="bi bi-sliders me-2"></i>Catálogo Triage</a>
        <a href="{{ route('admin.reportes') }}" class="nav-link-custom"><i class="bi bi-graph-up me-2"></i>Reportes</a>
        <a href="{{ route('admin.respaldos.index') }}" class="nav-link-custom"><i class="bi bi-database-down me-2"></i>Respaldos BD</a>    
    
    </nav>

    <main class="container-fluid py-5 px-4" style="max-width: 1400px;">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="mb-4">
            <h4 class="fw-bold text-dark"><i class="bi bi-ui-checks-grid me-2 text-primary"></i>Configuración de Algoritmo (NOM-027)</h4>
            <p class="text-muted">Ajusta los límites clínicos por categoría. Los cambios aplican de inmediato en la recepción del hospital.</p>
        </div>

        @php
            // Agrupamos los parámetros dinámicamente por su unidad de medida
            $grupos = collect($parametros)->groupBy('unidad_medida');
            
            // Diccionario para ponerle nombres e iconos bonitos a cada grupo
            $infoCategorias = [
                '°C' => ['icono' => 'bi-thermometer-high text-danger', 'titulo' => 'Temperatura Corporal'],
                'mmHg' => ['icono' => 'bi-heart-pulse text-danger', 'titulo' => 'Presión Arterial (Sistólica / Diastólica)'],
                '%' => ['icono' => 'bi-lungs text-info', 'titulo' => 'Saturación de Oxígeno (SpO2)'],
                'lpm' => ['icono' => 'bi-activity text-success', 'titulo' => 'Frecuencia Cardíaca'],
                'rpm' => ['icono' => 'bi-wind text-primary', 'titulo' => 'Frecuencia Respiratoria'],
            ];
        @endphp

        <!-- Grid de Categorías Clínicas -->
        <div class="row g-4">
            @foreach($grupos as $unidad => $params)
                <div class="col-md-6 col-xl-4">
                    <div class="card card-clinical h-100">
                        <div class="card-header-clinical bg-white">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi {{ $infoCategorias[$unidad]['icono'] ?? 'bi-sliders text-secondary' }} me-2 fs-5"></i> 
                                {{ $infoCategorias[$unidad]['titulo'] ?? 'Otros Parámetros' }}
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-custom table-hover mb-0">
                                <tbody>
                                    @foreach($params as $param)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $param->nombre_descriptivo }}</div>
                                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $param->clave_parametro }}</small>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <span class="fw-bold fs-5 me-1" style="color: var(--triage-primary);">{{ $param->valor }}</span>
                                                <span class="text-muted small">{{ $param->unidad_medida }}</span>
                                            </td>
                                            <td class="text-center pe-4" style="width: 60px;">
                                                <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#modalParam{{ $param->id_parametro }}" title="Modificar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    <!-- Modales para editar (Mantenidos al final y fuera de las tablas) -->
    @foreach($parametros as $param)
    <div class="modal fade text-start" id="modalParam{{ $param->id_parametro }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form action="{{ route('admin.catalogo.update', $param->id_parametro) }}" method="POST" class="modal-content border-0 shadow-lg">
                @csrf @method('PUT')
                <div class="modal-header text-white border-0" style="background-color: var(--triage-primary);">
                    <h6 class="modal-title fw-bold">Modificar Límite</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <p class="text-muted small mb-3">Estás editando: <br><strong class="text-dark">{{ $param->nombre_descriptivo }}</strong></p>
                    <div class="input-group">
                        <input type="number" step="0.1" name="valor" class="form-control form-control-lg fw-bold text-center" value="{{ $param->valor }}" required>
                        <span class="input-group-text bg-light text-muted fw-bold">{{ $param->unidad_medida }}</span>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 p-3">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn fw-bold text-white btn-sm px-4" style="background-color: var(--triage-primary);">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>