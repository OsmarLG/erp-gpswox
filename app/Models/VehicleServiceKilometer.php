<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleServiceKilometer extends Model
{
    use HasFactory;

    protected $table = 'vehicle_service_kilometers';

    protected $fillable = [
        'vehicle_id',
        'service_id',
        'last_km',
        'current_km',
    ];

    // Relaciones
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function service()
    {
        return $this->belongsTo(Servicio::class, 'service_id');
    }
}
