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
        Schema::table('vehicle_service_record_detail_archivos', function (Blueprint $table) {
            $table->string('type')->nullable();
        });

        Schema::table('vehicle_request_archivos', function (Blueprint $table) {
            $table->string('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_service_record_detail_archivos', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('vehicle_request_archivos', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
