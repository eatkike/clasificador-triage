<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Respaldos - Clasificador Triage</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Mismos estilos que tu panel de personal */
        :root { --triage-primary: #0f4c5c; --triage-bg: #f8f9fa; }
        body { background-color: var(--triage-bg); font-family: 'Inter', sans-serif; }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #edf2f7; }
        .sub-navbar { background-color: var(--triage-primary); }
        .nav-link-custom { color: rgba(255,255,255,0.7); font-weight: 500; padding: 0.8rem 1rem; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #ffffff; background-color: rgba(255,255,255,0.1); border-radius: 6px; }
        .card-clinical { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .table-custom th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; background-color: #f8fafc; padding: 1rem; }
        .table-custom td { vertical-align: middle; padding: 1rem; border-bottom: 1px solid #edf2f7; }
    </style>
</head>
<body>

    <!-- Topbar (Idéntica) -->
    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-danger text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-hospital fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">CLASIFICADOR TRIAGE</h5>
                <small class="text-muted fw-medium">Portal de Administración</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="text-end">
                <p class="mb-0 fw-bold text-dark fs-6">{{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}</p>
                <small class="text-muted">{{ Auth::user()->rol }}</small>
            </div>
            <a href="{{ url('/') }}" class="btn btn-light border shadow-sm text-danger rounded-circle"><i class="bi bi-power"></i></a>
        </div>
    </header>

    <!-- Sub Navbar -->
    <nav class="sub-navbar px-4 py-2 shadow-sm d-flex gap-2">
        <a href="#" class="nav-link-custom"><i class="bi bi-shield-lock me-2"></i>Permisos</a>
        <a href="{{ route('admin.personal.index') }}" class="nav-link-custom"><i class="bi bi-people me-2"></i>Personal Clínico</a>
        <a href="#" class="nav-link-custom"><i class="bi bi-card-checklist me-2"></i>Catálogos Triage</a>
        <a href="{{ route('admin.respaldos.index') }}" class="nav-link-custom active"><i class="bi bi-database-down me-2"></i>Respaldos BD</a>
        <a href="{{ route('admin.reportes') }}" class="nav-link-custom"><i class="bi bi-graph-up me-2"></i>Reportes</a>
    </nav>

    <main class="container-fluid px-4 py-5" style="max-width: 1400px;">
        
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3"><i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3"><i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}</div>
        @endif

        <div class="card card-clinical">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-server text-primary me-2"></i>Historial de Respaldos del Sistema</h5>
                    <small class="text-muted">Copias de seguridad automáticas de expedientes y configuraciones.</small>
                </div>
                <form action="{{ route('admin.respaldos.generar') }}" method="POST" onsubmit="document.getElementById('btn-generar').innerHTML = '<span class=\'spinner-border spinner-border-sm me-2\'></span>Procesando...'; document.getElementById('btn-generar').disabled = true;">
                    @csrf
                    <button type="submit" id="btn-generar" class="btn btn-primary fw-medium px-4">
                        <i class="bi bi-cloud-download me-2"></i>Generar Nuevo Respaldo
                    </button>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Nombre del Archivo SQL</th>
                                <th>Fecha de Generación</th>
                                <th>Peso del Archivo</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($respaldos as $respaldo)
                                <tr>
                                    <td class="ps-4 fw-medium text-dark"><i class="bi bi-filetype-sql text-muted me-2 fs-5"></i>{{ $respaldo['nombre'] }}</td>
                                    <td><i class="bi bi-calendar3 text-muted me-2"></i>{{ $respaldo['fecha'] }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $respaldo['size'] }}</span></td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.respaldos.descargar', $respaldo['nombre']) }}" class="btn btn-sm btn-outline-success me-1" title="Descargar copia">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form action="{{ route('admin.respaldos.eliminar', $respaldo['nombre']) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar este respaldo del servidor?')" title="Eliminar del servidor">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-database-exclamation fs-1 d-block mb-3 text-light"></i>
                                        No hay respaldos generados actualmente en el sistema.
                                    </td>
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