<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleOperatorHistory extends Model
{
    use HasFactory;

    protected $table = 'vehicle_operator_history';

    protected $fillable = [
        'vehicle_id',
        'operador_id',
        'fecha_asignacion',
        'fecha_liberacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_liberacion' => 'datetime',
    ];

    /**
     * Relación con el vehículo.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Relación con el operador (usuario).
     */
    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}
