<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            // Campos requeridos en FN.3:
            $table->enum('role', ['admin', 'medico', 'enfermero'])->default('enfermero');
            $table->string('cedula_profesional', 20)->nullable();
            $table->string('especialidad', 100)->nullable();
            $table->enum('turno', ['matutino', 'vespertino', 'nocturno', 'jornada_acumulada'])->default('matutino');
            $table->boolean('is_active')->default(true); // Baja lógica requerida en FN.3
            // Requisito FF.2 (Seguridad contra fuerza bruta):
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};