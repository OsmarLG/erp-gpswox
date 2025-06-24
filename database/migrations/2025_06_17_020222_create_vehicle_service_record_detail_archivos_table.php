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
        Schema::create('vehicle_service_record_detail_archivos', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('description');

            $table->unsignedBigInteger('vehicle_service_record_detail_id');
            $table->unsignedBigInteger('operador_id');

            $table->foreign('vehicle_service_record_detail_id', 'vsrd_archivos_vsrd_id_fk')
                ->references('id')
                ->on('vehicle_service_record_details')
                ->cascadeOnDelete();

            $table->foreign('operador_id')->references('id')->on('users')->cascadeOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_service_record_detail_archivos');
    }
};
