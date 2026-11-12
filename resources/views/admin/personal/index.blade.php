<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personal - Clasificador Triage</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --triage-primary: #0f4c5c;
            --triage-primary-hover: #0a3641;
            --triage-danger: #d90429;
            --triage-bg: #f8f9fa;
            --text-main: #2b2d42;
            --text-muted: #6c757d;
        }

        body { 
            background-color: var(--triage-bg); 
            font-family: 'Inter', sans-serif; 
            color: var(--text-main);
        }

        /* Navbars */
        .topbar { background-color: #ffffff; border-bottom: 1px solid #edf2f7; }
        .sub-navbar { background-color: var(--triage-primary); }
        .nav-link-custom { color: rgba(255,255,255,0.7); font-weight: 500; padding: 0.8rem 1rem; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #ffffff; background-color: rgba(255,255,255,0.1); border-radius: 6px; }

        /* Cards & UI Elements */
        .card-clinical { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
        .card-header-clinical { background-color: transparent; border-bottom: 1px solid #edf2f7; padding: 1.25rem 1.5rem; }
        
        /* Inputs */
        .form-control, .form-select { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 1rem; font-size: 0.95rem; }
        .form-control:focus, .form-select:focus { border-color: var(--triage-primary); box-shadow: 0 0 0 0.25rem rgba(15, 76, 92, 0.1); }
        .form-label { font-weight: 600; color: #4a5568; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Buttons */
        .btn-triage { background-color: var(--triage-primary); color: white; border-radius: 8px; font-weight: 500; padding: 0.6rem 1.5rem; transition: all 0.2s; }
        .btn-triage:hover { background-color: var(--triage-primary-hover); color: white; transform: translateY(-1px); }
        
        /* Tables & Badges SaaS Style */
        .table-custom th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; border-bottom: 2px solid #edf2f7; background-color: #f8fafc; padding: 1rem; }
        .table-custom td { vertical-align: middle; padding: 1rem; border-bottom: 1px solid #edf2f7; color: #4a5568; }
        .badge-soft { padding: 0.5rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.75rem; }
        
        /* Badge Colors */
        .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); color: #059669; }
        .bg-soft-danger { background-color: rgba(239, 68, 68, 0.1); color: #dc2626; }
        .bg-soft-primary { background-color: rgba(15, 76, 92, 0.1); color: var(--triage-primary); }
        .bg-soft-info { background-color: rgba(56, 189, 248, 0.1); color: #0284c7; }
        .bg-soft-secondary { background-color: rgba(100, 116, 139, 0.1); color: #475569; }
    </style>
</head>
<body>

    <!-- Topbar -->
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
<!-- Sub Navbar -->
    <nav class="sub-navbar px-4 py-2 shadow-sm d-flex gap-2 overflow-auto">
        <!--<a href="#" class="nav-link-custom"><i class="bi bi-shield-lock me-2"></i>Permisos</a>-->
        <a href="{{ route('admin.personal.index') }}" class="nav-link-custom active"><i class="bi bi-people me-2"></i>Personal Clínico</a>
        <a href="{{ route('admin.catalogo.index') }}" class="nav-link-custom"><i class="bi bi-sliders me-2"></i>Catálogo Triage</a>
        <a href="{{ route('admin.reportes') }}" class="nav-link-custom"><i class="bi bi-graph-up me-2"></i>Reportes</a>
        <a href="{{ route('admin.respaldos.index') }}" class="nav-link-custom"><i class="bi bi-database-down me-2"></i>Respaldos BD</a>    
    </nav>

    <main class="container-fluid px-4 py-5" style="max-width: 1400px;">
        
        <!-- Alertas -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong class="mb-0">Se encontraron errores en el formulario:</strong>
                </div>
                <ul class="mb-0 text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            
            <!-- Columna Izquierda: Formulario -->
            <div class="col-lg-4">
                <div class="card card-clinical h-100">
                    <div class="card-header-clinical">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-plus text-primary me-2"></i>Registrar Nuevo Trabajador</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.personal.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nombre(s)</label>
                                    <input type="text" name="nombre" class="form-control" placeholder="Ej. Carlos Roberto" value="{{ old('nombre') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Apellidos</label>
                                    <input type="text" name="apellidos" class="form-control" placeholder="Ej. Mendoza Pérez" value="{{ old('apellidos') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Cédula Profesional</label>
                                    <input type="text" name="cedula" class="form-control" placeholder="Número de cédula" value="{{ old('cedula') }}" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Rol del Sistema</label>
                                    <select name="rol" class="form-select" required>
                                        <option value="Enfermero" {{ old('rol') == 'Enfermero' ? 'selected' : '' }}>Enfermero</option>
                                        <option value="Médico" {{ old('rol') == 'Médico' ? 'selected' : '' }}>Médico</option>
                                        <option value="Administrador" {{ old('rol') == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Turno</label>
                                    <select name="turno" class="form-select" required>
                                        <option value="Matutino">Matutino</option>
                                        <option value="Vespertino">Vespertino</option>
                                        <option value="Nocturno">Nocturno</option>
                                        <option value="Mixto">Mixto</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Correo Institucional</label>
                                    <input type="email" name="correo" class="form-control" placeholder="usuario@hospital.com" value="{{ old('correo') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Contraseña de Acceso</label>
                                    <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                                </div>
                                <div class="col-12 mt-4 pt-2 border-top">
                                    <button type="submit" class="btn btn-triage w-100">
                                        <i class="bi bi-save me-2"></i>Guardar Expediente
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Tabla -->
            <div class="col-lg-8">
                <div class="card card-clinical h-100">
                    <div class="card-header-clinical d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul text-primary me-2"></i>Directorio del Personal Activo</h6>
                        <span class="badge bg-light text-dark border"><i class="bi bi-people-fill me-1"></i> Total: {{ $usuarios->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Usuario</th>
                                        <th>Cédula</th>
                                        <th>Rol / Turno</th>
                                        <th>Estado</th>
                                        <th class="text-end pe-4">Control</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($usuarios as $usr)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-muted fw-bold me-3" style="width: 40px; height: 40px;">
                                                        {{ substr($usr->nombre, 0, 1) }}{{ substr($usr->apellidos, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">{{ $usr->nombre }} {{ $usr->apellidos }}</h6>
                                                        <small class="text-muted">{{ $usr->correo }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fw-medium text-dark">{{ $usr->cedula }}</td>
                                            <td>
                                                <span class="badge badge-soft {{ $usr->rol === 'Administrador' ? 'bg-soft-secondary' : ($usr->rol === 'Médico' ? 'bg-soft-primary' : 'bg-soft-info') }} mb-1 d-inline-block">
                                                    {{ $usr->rol }}
                                                </span>
                                                <br>
                                                <small class="text-muted fw-medium"><i class="bi bi-clock me-1"></i>{{ $usr->turno }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-soft {{ $usr->is_active ? 'bg-soft-success' : 'bg-soft-danger' }}">
                                                    @if($usr->is_active) <i class="bi bi-check-circle me-1"></i>Activo @else <i class="bi bi-x-circle me-1"></i>Inactivo @endif
                                                </span>
                                            </td>

                                            <td class="text-end pe-4">
                                                <!-- Botón Editar -->
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $usr->id_usuario }}" title="Editar datos">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <!-- Botón Suspender/Restaurar (Delete lógico) -->
                                                <form action="{{ route('admin.personal.toggle', $usr->id_usuario) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $usr->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                            onclick="return confirm('¿Confirma que desea cambiar el estado de acceso de este trabajador?')"
                                                            title="{{ $usr->is_active ? 'Suspender acceso' : 'Restaurar acceso' }}">
                                                        <i class="bi {{ $usr->is_active ? 'bi-dash-circle' : 'bi-check2-circle' }}"></i>
                                                    </button>
                                                </form>

                                                <!-- Modal Editar (Se genera uno por cada usuario) -->
                                                <div class="modal fade text-start" id="modalEdit{{ $usr->id_usuario }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <form action="{{ route('admin.personal.update', $usr->id_usuario) }}" method="POST" class="modal-content">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">Editar Personal Clínico</h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nombre(s)</label>
                                                                    <input type="text" name="nombre" class="form-control" value="{{ $usr->nombre }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Apellidos</label>
                                                                    <input type="text" name="apellidos" class="form-control" value="{{ $usr->apellidos }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Rol</label>
                                                                    <select name="rol" class="form-select" required>
                                                                        <option value="Médico" {{ $usr->rol == 'Médico' ? 'selected' : '' }}>Médico</option>
                                                                        <option value="Enfermero" {{ $usr->rol == 'Enfermero' ? 'selected' : '' }}>Enfermero</option>
                                                                        <option value="Administrador" {{ $usr->rol == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Turno</label>
                                                                    <select name="turno" class="form-select" required>
                                                                        <option value="Matutino" {{ $usr->turno == 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                                                        <option value="Vespertino" {{ $usr->turno == 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                                                        <option value="Nocturno" {{ $usr->turno == 'Nocturno' ? 'selected' : '' }}>Nocturno</option>
                                                                        <option value="Mixto" {{ $usr->turno == 'Mixto' ? 'selected' : '' }}>Mixto</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>


                                            
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-3 text-light"></i>
                                                No hay personal clínico registrado en la base de datos.
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>