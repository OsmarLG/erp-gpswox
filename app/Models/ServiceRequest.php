<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'service_id',
        'operador_id',
        'status',
    ];

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
}
