<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\PersonalController;
//use App\Http\Controllers\Medico\PacienteController;
use App\Http\Controllers\Enfermero\TriageController;
use App\Http\Controllers\Medico\DashboardController;
use App\Http\Controllers\Enfermero\PacienteController;




// Rutas Públicas
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Recuperación de Contraseña
Route::get('/recuperar-password', [App\Http\Controllers\AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/recuperar-password', [App\Http\Controllers\AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/restablecer-password/{token}', [App\Http\Controllers\AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/restablecer-password', [App\Http\Controllers\AuthController::class, 'resetPassword'])->name('password.update');

// Rutas Protegidas
Route::middleware(['auth'])->group(function () {

    // Rutas del Administrador
    Route::middleware(['auth', 'checkrole:administrador'])->prefix('admin')->name('admin.')->group(function () {
        
        // Gestión de Personal Clínico
        Route::get('/personal', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('personal.index');
        Route::post('/personal', [App\Http\Controllers\Admin\AdminController::class, 'store'])->name('personal.store');
        Route::post('/personal/toggle/{id_usuario}', [App\Http\Controllers\Admin\AdminController::class, 'toggle'])->name('personal.toggle');
         // Esta es la ruta para guardar la edición
        Route::put('/personal/{id_usuario}', [App\Http\Controllers\Admin\AdminController::class, 'update'])->name('personal.update');
      
        // Reportes Estadísticos
        Route::get('/reportes', [App\Http\Controllers\Admin\AdminController::class, 'reportes'])->name('reportes');
        // Reportes Estadísticos
        Route::get('/reportes', [App\Http\Controllers\Admin\AdminController::class, 'reportes'])->name('reportes');
        
        // Gestión de Respaldos
        Route::get('/respaldos', [App\Http\Controllers\Admin\AdminController::class, 'respaldosIndex'])->name('respaldos.index');
        Route::post('/respaldos/generar', [App\Http\Controllers\Admin\AdminController::class, 'generarRespaldo'])->name('respaldos.generar');
        Route::get('/respaldos/descargar/{nombre}', [App\Http\Controllers\Admin\AdminController::class, 'descargarRespaldo'])->name('respaldos.descargar');
        Route::delete('/respaldos/eliminar/{nombre}', [App\Http\Controllers\Admin\AdminController::class, 'eliminarRespaldo'])->name('respaldos.eliminar');
        });


    // Rutas del Médico
    Route::middleware(['checkrole:médico'])->prefix('medico')->name('medico.')->group(function () {
        Route::get('/dashboard', function () { return "Dashboard Clínico (Panel de Monitoreo)"; })->name('dashboard');
    });

  
   // Rutas del Enfermero
    Route::middleware(['auth', 'checkrole:enfermero'])->prefix('enfermero')->name('enfermero.')->group(function () {
        
        // 1. Dashboard de Urgencias (Asegúrate de que tenga el name 'dashboard')
        Route::get('/dashboard', [App\Http\Controllers\Enfermero\TriageController::class, 'dashboard'])->name('dashboard');
        
        // 2. Recepción de Pacientes (CRUD)
        Route::get('/pacientes', [App\Http\Controllers\Enfermero\PacienteController::class, 'index'])->name('pacientes.index');
        Route::post('/pacientes', [App\Http\Controllers\Enfermero\PacienteController::class, 'store'])->name('pacientes.store');
        Route::put('/paciente/{id_paciente}', [App\Http\Controllers\Enfermero\PacienteController::class, 'updatePaciente'])->name('paciente.update');
        Route::delete('/paciente/{id_paciente}', [App\Http\Controllers\Enfermero\PacienteController::class, 'destroyPaciente'])->name('paciente.destroy');
        
        // 3. Triage y Signos
        Route::get('/signos', [App\Http\Controllers\Enfermero\TriageController::class, 'signos'])->name('signos');
        Route::get('/triage/{id_paciente}', [App\Http\Controllers\Enfermero\TriageController::class, 'create'])->name('triage.create');
        Route::get('/triage/ticket/{id_triage}', [App\Http\Controllers\Enfermero\TriageController::class, 'ticket'])->name('triage.ticket');
    });

    // Rutas del Médico
    Route::middleware(['checkrole:médico'])->prefix('medico')->name('medico.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/atender/{id_triage}', [DashboardController::class, 'atender'])->name('atender');
        
        // Nuevas rutas de consultorio
        Route::get('/consultorio/{id_triage}', [DashboardController::class, 'consultorio'])->name('consultorio');
        Route::post('/consultorio/{id_triage}/alta', [DashboardController::class, 'darDeAlta'])->name('alta');
    });

});