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
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->string('type')->default('field'); // Puede ser 'field' o 'part'
            $table->unsignedBigInteger('parte_id')->nullable();
            $table->foreign('parte_id')->references('id')->on('partes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropForeign(['parte_id']);
            $table->dropColumn('parte_id');
        });
    }
};
