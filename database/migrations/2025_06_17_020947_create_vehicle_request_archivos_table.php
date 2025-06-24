<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_request_archivos', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('description');
            $table->foreignId('vehicle_request_id')->constrained('vehicle_requests')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('parte_id')->constrained('partes')->cascadeOnDelete();
            $table->foreignId('operador_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_request_archivos');
    }
};
