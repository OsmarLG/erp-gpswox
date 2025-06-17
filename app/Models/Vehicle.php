<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\HttpRequestService;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'placa',
        'tipo_marca',
        'nombre_unidad',
        'operador_id',
        'vin',
        'no_tarjeta_circulacion',
        'vigencia_tarjeta',
        'tag_numero',
        'tag_gasolina_id',
        'verificacion_vencimiento',
        'fecha_bateria',
        'rines_medida',
        'medida_llantas',
        'poliza_no',
        'compania_seguros',
        'poliza_vigencia',
        'costo_poliza',
        'pago',
        'telefono_seguro',
        'gpswox_id',
        'id_gps1',
        'tel_gps1',
        'imei_gps1',
        'vigencia_gps1',
        'saldo_gps1',
        'id_gps2',
        'tel_gps2',
        'imei_gps2',
        'vigencia_gps2',
        'saldo_gps2',
        'get_datos_gpswox',
    ];

    protected $casts = [
        'get_datos_gpswox' => 'boolean',
    ];

    /**
     * Relación con el operador (user).
     */
    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    /**
     * Relación polimórfica con la tabla files (morphMany).
     */
    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }

    public function operatorHistory()
    {
        return $this->hasMany(VehicleOperatorHistory::class, 'vehicle_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'vehicle_id');
    }

    public function getOdometroAttribute()
    {
        return $this->kilometraje_gpswox ?? $this->kilometraje;
    }

    public function getKilometrajeAttribute()
    {
        $km = $this->hasMany(VehicleServiceKilometer::class, 'vehicle_id')->first();
        return $km->current_km ?? $km->last_km ?? 0;
    }

    public function getKilometrajeGpswoxAttribute()
    {
        if (!$this->gpswox_id) {
            return 0;
        }

        try {
            $response = HttpRequestService::makeRequest('post', HttpRequestService::BASE_GPSWOX_URL . 'get_devices', [
                'user_api_hash' => HttpRequestService::API_GPSWOX_TOKEN,
                'id' => $this->gpswox_id,
            ]);

            return collect($response[0]['items'][0]['sensors'] ?? [])
                ->firstWhere('type', 'odometer')['val'] ?? 0;
        } catch (\Throwable $e) {
            report($e);
            return 0;
        }
    }
}
