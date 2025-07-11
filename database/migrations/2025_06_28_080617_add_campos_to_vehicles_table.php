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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('asegurado')->nullable();
            $table->string('comentarios_seguro')->nullable();
            $table->string('tarjeta_estado')->nullable();
            $table->string('nombre_tarjeta')->nullable();   
            $table->string('marca_bateria')->nullable();    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('asegurado');
            $table->dropColumn('comentarios_seguro');
            $table->dropColumn('tarjeta_estado');
            $table->dropColumn('nombre_tarjeta');   
            $table->dropColumn('marca_bateria');    
        });
    }
};
