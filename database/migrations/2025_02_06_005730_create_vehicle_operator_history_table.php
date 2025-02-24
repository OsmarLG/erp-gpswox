<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_operator_history', function (Blueprint $table) {
            $table->id();

            // Relaciones con vehículos y usuarios (operadores)
            $table->foreignId('vehicle_id')->nullable();
            $table->foreignId('operador_id')->nullable();

            // Fecha de asignación y liberación del operador
            $table->date('fecha_asignacion')->nullable();
            $table->date('fecha_liberacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_operator_history');
    }
};
