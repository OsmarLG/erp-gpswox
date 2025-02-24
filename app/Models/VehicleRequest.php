<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRequest extends Model
{
    protected $table = 'vehicle_requests';

    protected $fillable = [
        'vehicle_id',
        'field',
        'operador_id',
        'status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}
