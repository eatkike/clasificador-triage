<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Clasificador Triage</title>
    
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
            --triage-bg: #f0f4f8;
            --text-main: #2b2d42;
            --text-muted: #6c757d;
        }

        body { 
            background-color: var(--triage-bg); 
            font-family: 'Inter', sans-serif; 
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Card & Layout */
        .login-wrapper { width: 100%; max-width: 900px; padding: 1rem; }
        .card-clinical { border: none; border-radius: 16px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); overflow: hidden; background: #fff; }
        
        /* Inputs & Typography */
        .form-control { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; background-color: #f8fafc; }
        .form-control:focus { border-color: var(--triage-primary); box-shadow: 0 0 0 0.25rem rgba(15, 76, 92, 0.1); background-color: #fff; }
        .form-label { font-weight: 600; color: #4a5568; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Buttons */
        .btn-triage { background-color: var(--triage-primary); color: white; border-radius: 8px; font-weight: 600; padding: 0.75rem 1.5rem; transition: all 0.2s; letter-spacing: 0.5px; }
        .btn-triage:hover { background-color: var(--triage-primary-hover); color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15, 76, 92, 0.2); }
        .btn-outline-triage { border: 1px solid #e2e8f0; color: #4a5568; border-radius: 8px; font-weight: 600; transition: all 0.2s; }
        .btn-outline-triage:hover { background-color: #f8fafc; color: var(--triage-primary); border-color: #cbd5e1; }

        /* Split sections */
        .auth-section { padding: 3rem; }
        .recovery-section { background-color: #f8fafc; padding: 3rem; border-left: 1px solid #edf2f7; display: flex; flex-direction: column; justify-content: center; }
        
        .brand-icon { width: 48px; height: 48px; background: #d90429; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="card card-clinical row g-0 flex-row">
            
            <!-- Sección de Acceso (Izquierda) -->
            <div class="col-md-7 auth-section">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-icon shadow-sm">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">CLASIFICADOR TRIAGE</h4>
                        <p class="text-muted mb-0 fw-medium">Portal de Acceso Clínico</p>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 py-2 px-3 mb-4 d-flex align-items-center">
                        <i class="bi bi-exclamation-octagon-fill fs-5 me-3 text-danger"></i>
                        <ul class="mb-0 text-sm ps-0" style="list-style: none;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-person-badge me-2"></i>Cédula Profesional</label>
                        <input type="text" name="cedula" class="form-control" placeholder="Ingrese su número de cédula" value="{{ old('cedula') }}" required autofocus autocomplete="off">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-key me-2"></i>Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
                    </div>

                    <button type="submit" class="btn btn-triage w-100 mt-2">
                        INGRESAR AL SISTEMA <i class="bi bi-box-arrow-in-right ms-2"></i>
                    </button>
                </form>
            </div>

            <!-- Sección de Recuperación (Derecha) -->
            <div class="col-md-5 recovery-section">
                <div class="text-center mb-4">
                    <div class="bg-white rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center text-primary mb-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-shield-lock fs-3 text-secondary"></i>
                    </div>
                    <h5 class="fw-bold text-dark">¿Olvidó su contraseña?</h5>
                    <p class="text-muted small">Ingrese su correo institucional para recibir un enlace seguro de restablecimiento.</p>
                </div>
                
                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
    
                    <label class="form-label font-weight-bold" style="font-size: 12px; color: #555;">CORREO REGISTRADO</label>
                    <input type="email" name="correo" class="form-control mb-3" placeholder="usuario@hospital.com" required>
    
                    @error('correo') 
                        <span class="text-danger d-block mb-2" style="font-size: 13px;">{{ $message }}</span> 
                    @enderror

                    @if(session('success')) 
                        <span class="text-success d-block mb-2" style="font-size: 13px;">{{ session('success') }}</span> 
                    @endif

                    <button type="submit" class="btn btn-outline-secondary w-100 font-weight-bold">
                    <i class="bi bi-envelope"></i> Solicitar Enlace
                    </button>
                </form>
            </div>

        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">© 2026 Sistema Clasificador Triage NOM-027-SSA3-2013. Todos los derechos reservados.</small>
        </div>
    </div>

</body>
</html>