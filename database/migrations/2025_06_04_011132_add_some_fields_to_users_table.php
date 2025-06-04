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
        Schema::table('users', function (Blueprint $table) {
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_telefono')->nullable();
            $table->string('link_google_maps')->nullable();
            $table->string('perfil_uber')->nullable();
            $table->string('datos_uber')->nullable();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('get_datos_gpswox')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('contacto_emergencia_nombre');
            $table->dropColumn('contacto_emergencia_telefono');
            $table->dropColumn('link_google_maps');
            $table->dropColumn('perfil_uber');
            $table->dropColumn('datos_uber');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('get_datos_gpswox');
        });
    }
};
