<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleRequestArchivo extends Model
{
    use SoftDeletes;
    protected $table = 'vehicle_request_archivos';

    protected $fillable = [
        'path',
        'description',
        'vehicle_request_id',
        'operador_id',
        'vehicle_id',
        'parte_id',
        'type',
    ];

    public function request()
    {
        return $this->belongsTo(VehicleRequest::class, 'vehicle_request_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function parte()
    {
        return $this->belongsTo(Parte::class, 'parte_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}
