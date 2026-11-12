<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros_triage', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->string('clave_parametro')->unique(); // Ej: 'temp_fiebre_amarillo'
            $table->string('nombre_descriptivo');        // Ej: 'Límite de Fiebre (Código Amarillo)'
            $table->decimal('valor', 8, 2);              // Ej: 39.00
            $table->string('unidad_medida');             // Ej: '°C', 'lpm', 'mmHg'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros_triage');
    }
};