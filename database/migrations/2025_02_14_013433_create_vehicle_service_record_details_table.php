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
        Schema::create('vehicle_service_record_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_service_record_id')->constrained('vehicle_service_records')->onDelete('cascade');
            $table->foreignId('operador_id')->constrained('users')->onDelete('cascade');
            $table->text('detalle');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_service_record_details');
    }
};
