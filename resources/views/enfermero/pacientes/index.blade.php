<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepción de Pacientes - Clasificador Triage</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
        .form-control, .form-select { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 1rem; font-size: 0.95rem; }
        .form-control:focus, .form-select:focus { border-color: var(--triage-primary); box-shadow: 0 0 0 0.25rem rgba(2, 128, 144, 0.1); }
        .form-label { font-weight: 600; color: #4a5568; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .btn-triage { background-color: var(--triage-primary); color: white; border-radius: 8px; font-weight: 500; padding: 0.6rem 1.5rem; }
        .btn-triage:hover { background-color: var(--triage-primary-hover); color: white; }
        .table-custom th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; background-color: #f8fafc; padding: 1rem; }
        .table-custom td { vertical-align: middle; padding: 1rem; border-bottom: 1px solid #edf2f7; }
        .badge-soft { padding: 0.5rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.75rem; }
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
                <button type="submit" class="btn btn-light border shadow-sm text-danger rounded-circle" style="width: 40px; height: 40px;" title="Cerrar Sesión">
                    <i class="bi bi-power"></i>
                </button>
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

        <div class="row g-4">
            <!-- Columna 1: Formulario de Registro -->
            <div class="col-lg-4">
                <div class="card card-clinical h-100">
                    <div class="card-header-clinical">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-add text-primary me-2"></i>Apertura de Expediente Físico</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('enfermero.pacientes.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nombre(s)</label>
                                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del paciente" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Apellido Paterno</label>
                                    <input type="text" name="apellidoPat" class="form-control" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Apellido Materno</label>
                                    <input type="text" name="apellidoMat" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">F. Nacimiento</label>
                                    <input type="date" name="fechaNacimiento" class="form-control" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Sexo</label>
                                    <select name="sexo" class="form-select" required>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Contacto Familiar</label>
                                    <input type="text" name="contacto_familiar" class="form-control" placeholder="Teléfono o Nombre del acompañante">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Antecedentes Clínicos</label>
                                    <textarea name="antecedentes_clinicos" class="form-control" rows="2" placeholder="Alergias, enfermedades crónicas..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Motivo de Consulta (Síntomas)</label>
                                    <textarea name="motivo_consulta" class="form-control" rows="3" placeholder="Razón principal por la que acude a urgencias..." required></textarea>
                                </div>
                                <div class="col-12 mt-4 pt-2 border-top">
                                    <button type="submit" class="btn btn-triage w-100">
                                        <i class="bi bi-save me-2"></i>Registrar Paciente
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Columna 2: Tabla de Pacientes -->
            <div class="col-lg-8">
                <div class="card card-clinical h-100">
                    <div class="card-header-clinical d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-columns text-primary me-2"></i>Pacientes en Recepción</h6>
                        <span class="badge bg-light text-dark border"><i class="bi bi-people-fill me-1"></i> Total: {{ $pacientes->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Paciente</th>
                                        <th>Fecha Nacimiento</th>
                                        <th>Sexo</th>
                                        <th>Estado Actual</th>
                                        <th class="text-center pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pacientes as $pac)
                                        <tr>
                                            <td class="ps-4">
                                                <h6 class="mb-0 fw-bold text-dark">{{ $pac->nombre }} {{ $pac->apellidoPat }}</h6>
                                                <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;" title="{{ $pac->motivo_consulta }}">
                                                    {{ $pac->motivo_consulta }}
                                                </small>
                                            </td>
                                            <td class="fw-medium text-dark">{{ \Carbon\Carbon::parse($pac->fechaNacimiento)->format('d/m/Y') }}</td>
                                            <td>{{ $pac->sexo }}</td>
                                            <td>
                                                <span class="badge badge-soft {{ $pac->estado_atencion === 'Espera de Valoración' ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                                                    <i class="bi {{ $pac->estado_atencion === 'Espera de Valoración' ? 'bi-hourglass-split' : 'bi-check-circle' }} me-1"></i>
                                                    {{ $pac->estado_atencion }}
                                                </span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <!-- Botón Editar que llama al modal externo -->
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditPac{{ $pac->id_paciente }}" title="Editar Paciente">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <!-- Botón Eliminar con Confirmación en JS -->
                                                <form action="{{ route('enfermero.paciente.destroy', $pac->id_paciente) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger me-1" onclick="return confirm('⚠️ ADVERTENCIA: ¿Estás seguro de que deseas eliminar este expediente? Esta acción no se puede deshacer.')" title="Eliminar Paciente">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>

                                                <!-- Botones de Flujo -->
                                                <a href="{{ route('enfermero.triage.create', $pac->id_paciente) }}" class="btn btn-sm btn-outline-primary" title="Pasar a Triage"><i class="bi bi-clipboard2-pulse"></i> Triage</a>
                                                <a href="{{ route('enfermero.triage.ticket', 1) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Imprimir Ticket">
                                                    <i class="bi bi-file-pdf"></i> Generar PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-folder-x fs-1 d-block mb-3 text-light"></i>
                                                No hay pacientes registrados en el sistema de urgencias.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- ZONA DE MODALES (Renderizados fuera de la tabla para evitar el problema de transparencia) -->
    @foreach($pacientes as $pac)
        <div class="modal fade text-start" id="modalEditPac{{ $pac->id_paciente }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('enfermero.paciente.update', $pac->id_paciente) }}" method="POST" class="modal-content border-0 shadow">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold">Editar Datos del Paciente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-dark fw-bold">Nombre(s)</label>
                                <input type="text" name="nombre" class="form-control" value="{{ $pac->nombre }}" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-dark fw-bold">Apellido Paterno</label>
                                <input type="text" name="apellidoPat" class="form-control" value="{{ $pac->apellidoPat }}" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-dark fw-bold">Apellido Materno</label>
                                <input type="text" name="apellidoMat" class="form-control" value="{{ $pac->apellidoMat }}">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-dark fw-bold">Fecha de Nacimiento</label>
                                <input type="date" name="fechaNacimiento" class="form-control" value="{{ $pac->fechaNacimiento }}" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-dark fw-bold">Sexo</label>
                                <select name="sexo" class="form-select" required>
                                    <option value="Masculino" {{ $pac->sexo == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ $pac->sexo == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary fw-medium" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-medium px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>