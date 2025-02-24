<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellidos')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('celular1')->nullable();
            $table->string('celular2')->nullable();
            $table->string('telefono_casa')->nullable();
            $table->string('nombre_contacto_con_quien_vive')->nullable();

            // INE (frente y reverso)
            $table->string('ine_frontal')->nullable();
            $table->string('ine_reverso')->nullable();

            // Licencia (frente y reverso)
            $table->string('licencia_frontal')->nullable();
            $table->string('licencia_reverso')->nullable();

            // Domicilio
            $table->unsignedInteger('cp_domicilio')->nullable();
            $table->string('direccion_domicilio')->nullable();
            $table->string('comprobante_domicilio')->nullable(); // si subes el archivo

            // Ubicación (puedes separar en lat/lng o usar un string/JSON, según prefieras)
            $table->string('ubicacion_domicilio')->nullable();

            // Fotos adicionales
            $table->string('foto_fachada')->nullable();
            $table->string('foto_estacionamiento')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'apellidos',
                'fecha_nacimiento',
                'celular1',
                'celular2',
                'telefono_casa',
                'nombre_contacto_con_quien_vive',
                'ine_frontal',
                'ine_reverso',
                'licencia_frontal',
                'licencia_reverso',
                'cp_domicilio',
                'direccion_domicilio',
                'comprobante_domicilio',
                'ubicacion_domicilio',
                'foto_fachada',
                'foto_estacionamiento',
            ]);
        });
    }
};
