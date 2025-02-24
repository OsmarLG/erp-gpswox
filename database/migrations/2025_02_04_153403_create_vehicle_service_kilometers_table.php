<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_service_kilometers', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('vehicle_id')->nullable();
            $table->foreignId('service_id')->nullable();

            // Kilometraje
            $table->unsignedBigInteger('last_km')->default(0);      // Último km registrado cuando se realizó el servicio
            $table->unsignedBigInteger('current_km')->default(0);   // Kilometraje actual

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_service_kilometers');
    }
};
