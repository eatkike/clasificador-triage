<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Medico\PacienteController;
use App\Http\Controllers\Enfermero\TriageController;
use App\Http\Controllers\Medico\DashboardController;



// Rutas Públicas
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas
Route::middleware(['auth'])->group(function () {

    // Rutas del Administrador
    Route::middleware(['checkrole:administrador'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/personal', [PersonalController::class, 'index'])->name('personal.index');
        Route::post('/personal', [PersonalController::class, 'store'])->name('personal.store');
        Route::post('/personal/{id}/toggle', [PersonalController::class, 'toggleStatus'])->name('personal.toggle');
    });

    // Rutas del Médico
    Route::middleware(['checkrole:médico'])->prefix('medico')->name('medico.')->group(function () {
        Route::get('/dashboard', function () { return "Dashboard Clínico (Panel de Monitoreo)"; })->name('dashboard');
    });

    // Rutas del Enfermero
    Route::middleware(['checkrole:enfermero'])->prefix('enfermero')->name('enfermero.')->group(function () {
        Route::get('/triage', function () { return "Módulo de Captura de Signos (CRUD 3)"; })->name('triage.create');
    });
    // Rutas del Enfermero
    Route::middleware(['checkrole:enfermero'])->prefix('enfermero')->name('enfermero.')->group(function () {
        // Gestión de Pacientes (FN.4)
        Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
        Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
        // Triage (FN.5 y FN.7)
        Route::get('/triage/{id_paciente}', [TriageController::class, 'create'])->name('triage.create');
        Route::post('/triage/{id_paciente}', [TriageController::class, 'store'])->name('triage.store');
    

        // La futura ruta de Signos Vitales
        Route::get('/triage', function () { return "Módulo de Captura de Signos (CRUD 3)"; })->name('triage.create');
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