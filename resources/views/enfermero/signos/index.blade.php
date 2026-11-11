<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Pacientes - Clasificador Triage</title>
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
        .table-custom th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; background-color: #f8fafc; padding: 1rem; }
        .table-custom td { vertical-align: middle; padding: 1rem; border-bottom: 1px solid #edf2f7; }
    </style>
</head>
<body>

    <header class="topbar py-3 px-4 d-flex justify-content-between align-items-center sticky-top">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-danger text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-heart-pulse fs-5"></i></div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">CLASIFICADOR TRIAGE</h5>
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
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card card-clinical h-100">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-medical text-primary me-2"></i>Directorio General de Pacientes</h5>
                    <small class="text-muted">Busca pacientes en el sistema o regístralos desde Recepción.</small>
                </div>
                
                <!-- Buscador de Pacientes -->
                <form action="{{ route('enfermero.signos') }}" method="GET" class="d-flex align-items-center bg-light border rounded-3 p-1 shadow-sm" style="width: 100%; max-width: 450px;">
                    <i class="bi bi-search text-muted ms-3 me-2"></i>
                    <input type="text" name="buscar" class="form-control border-0 bg-transparent shadow-none" placeholder="Buscar por nombre o apellidos..." value="{{ request('buscar') }}">
                    <button type="submit" class="btn btn-primary btn-sm px-3 rounded-2">Buscar</button>
                    @if(request('buscar'))
                        <a href="{{ route('enfermero.signos') }}" class="btn btn-light btn-sm text-danger border ms-1 rounded-2" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Paciente</th>
                                <th>Fecha Nacimiento</th>
                                <th>Sexo</th>
                                <th>Estado de Atención</th>
                                <th class="text-center pe-4">Gestión (CRUD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pacientes as $pac)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $pac->nombre }} {{ $pac->apellidoPat }} {{ $pac->apellidoMat }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pac->fechaNacimiento)->format('d/m/Y') }}</td>
                                    <td>{{ $pac->sexo }}</td>
                                    <td>
                                        <span class="badge {{ $pac->estado_atencion === 'Espera de Valoración' ? 'bg-warning text-dark' : ($pac->estado_atencion === 'Alta Médica' ? 'bg-success' : 'bg-primary') }}">
                                            {{ $pac->estado_atencion }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <!-- Botón Editar Datos Personales -->
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditPac{{ $pac->id_paciente }}" title="Editar Paciente"><i class="bi bi-pencil"></i></button>
                                        
                                        <!-- Botón Editar Triage (Solo si ya fue clasificado) -->
                                        @if($pac->estado_atencion !== 'Espera de Valoración')
                                            <a href="{{ route('enfermero.triage.edit', $pac->id_paciente) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar Signos/Triage">
                                                <i class="bi bi-heart-pulse"></i>
                                            </a>
                                        @endif

                                        <!-- Botón Eliminar -->
                                        <form action="{{ route('enfermero.paciente.destroy', $pac->id_paciente) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar este registro histórico?')" title="Eliminar Paciente"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">No se encontraron pacientes registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modales iterados fuera de la tabla -->
    @foreach($pacientes as $pac)
        <div class="modal fade" id="modalEditPac{{ $pac->id_paciente }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('enfermero.paciente.update', $pac->id_paciente) }}" method="POST" class="modal-content border-0 shadow">
                    @csrf @method('PUT')
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold">Editar Paciente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label">Nombre(s)</label><input type="text" name="nombre" class="form-control" value="{{ $pac->nombre }}" required></div>
                            <div class="col-sm-6"><label class="form-label">Ap. Paterno</label><input type="text" name="apellidoPat" class="form-control" value="{{ $pac->apellidoPat }}" required></div>
                            <div class="col-sm-6"><label class="form-label">Ap. Materno</label><input type="text" name="apellidoMat" class="form-control" value="{{ $pac->apellidoMat }}"></div>
                            <div class="col-sm-6"><label class="form-label">F. Nacimiento</label><input type="date" name="fechaNacimiento" class="form-control" value="{{ $pac->fechaNacimiento }}" required></div>
                            <div class="col-sm-6">
                                <label class="form-label">Sexo</label>
                                <select name="sexo" class="form-select" required>
                                    <option value="Masculino" {{ $pac->sexo == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ $pac->sexo == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>