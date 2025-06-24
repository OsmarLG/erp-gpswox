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
        Schema::table('vehicle_service_records', function (Blueprint $table) {
            $table->foreignId('solicitud_id')->nullable()->constrained('service_requests')->nullOnDelete();
            $table->string('valor_kilometraje')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_service_records', function (Blueprint $table) {
            $table->dropForeign(['solicitud_id']);
            $table->dropIndex(['solicitud_id']);
            $table->dropColumn('solicitud_id');
            $table->dropColumn('valor_kilometraje');
        });
    }
};
