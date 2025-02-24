<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // Datos básicos
            $table->string('placa')->nullable();
            $table->string('tipo_marca')->nullable(); // Tipo/Marca
            $table->string('nombre_unidad')->nullable();

            // Relación con users (operador)
            $table->foreignId('operador_id')->nullable();

            $table->string('vin')->nullable();

            // Tarjeta y TAG
            $table->string('no_tarjeta_circulacion')->nullable();
            $table->date('vigencia_tarjeta')->nullable();
            $table->string('tag_numero')->nullable();
            $table->string('tag_gasolina_id')->nullable();

            // Verificación y batería
            $table->date('verificacion_vencimiento')->nullable();
            $table->date('fecha_bateria')->nullable();

            // Rines y llantas (campos básicos; si deseas más detalle, podrías usar otra tabla)
            $table->string('rines_medida')->nullable();
            $table->string('medida_llantas')->nullable();

            // Datos de póliza/seguro
            $table->string('poliza_no')->nullable();
            $table->string('compania_seguros')->nullable();
            $table->date('poliza_vigencia')->nullable();
            $table->decimal('costo_poliza', 10, 2)->nullable();
            $table->string('pago')->nullable();
            $table->string('telefono_seguro')->nullable();

            // GPS
            $table->string('gpswox_id')->nullable();

            $table->string('id_gps1')->nullable();
            $table->string('tel_gps1')->nullable();
            $table->string('imei_gps1')->nullable();
            $table->date('vigencia_gps1')->nullable();
            $table->decimal('saldo_gps1', 10, 2)->nullable();

            $table->string('id_gps2')->nullable();
            $table->string('tel_gps2')->nullable();
            $table->string('imei_gps2')->nullable();
            $table->date('vigencia_gps2')->nullable();
            $table->decimal('saldo_gps2', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
