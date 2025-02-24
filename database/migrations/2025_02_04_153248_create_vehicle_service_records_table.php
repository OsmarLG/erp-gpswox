<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_service_records', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('vehicle_id')->nullable();
            $table->foreignId('service_id')->nullable();
            $table->foreignId('operador_id')->nullable();

            // Estado del servicio
            $table->boolean('completed')->default(false);       // ¿Se realizó el servicio?
            $table->dateTime('fecha_realizacion')->nullable();  // Fecha de realización
            $table->string('status')->default('pending');       // pending, completed, cancelled

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_service_records');
    }
};
