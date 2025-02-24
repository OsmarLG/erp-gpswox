<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            // Polimórfica: la combinación model_type + model_id 
            // define a qué modelo pertenece este registro
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            // Ruta del archivo, tipo y descripción
            $table->string('path');
            $table->string('type')->nullable();       // p.ej. "bateria_superior", "tarjeta_circulacion", etc.
            $table->string('description')->nullable(); // "Foto de la llanta frontal"
            $table->foreignId('operador_id')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Opcional: índice
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
