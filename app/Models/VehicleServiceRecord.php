<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleServiceRecord extends Model
{
    use HasFactory;

    protected $table = 'vehicle_service_records';

    protected $fillable = [
        'vehicle_id',
        'service_id',
        'operador_id',
        'completed',
        'fecha_realizacion',
        'valor_kilometraje',
        'status',
        'solicitud_id',
    ];

    protected $casts = [
        'fecha_realizacion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    // Relaciones
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function service()
    {
        return $this->belongsTo(Servicio::class, 'service_id');
    }

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

    /**
     * Relación con el historial de operadores (VehicleOperatorHistory).
     */
    public function operatorHistory()
    {
        return $this->hasMany(VehicleOperatorHistory::class, 'vehicle_id');
    }

    public function detalles()
    {
        return $this->hasMany(VehicleServiceRecordDetail::class, 'vehicle_service_record_id')->orderBy('created_at', 'desc');
    }

    public function solicitud()
    {
        return $this->belongsTo(ServiceRequest::class, 'solicitud_id');
    }

    public function getHasDetallesAttribute()
    {
        $lastDetalle = $this->detalles()->orderBy('created_at', 'desc')->first();

        if (!$lastDetalle) {
            return false;
        }

        return $lastDetalle->operador_id === $this->operador_id;
    }

    public function getHasDetallesOperadorAttribute()
    {
        $lastDetalle = $this->detalles()->orderBy('created_at', 'desc')->first();

        if (!$lastDetalle) {
            return false;
        }

        return $lastDetalle->operador_id === auth()->user()->id;
    }
}
