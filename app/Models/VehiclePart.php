<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePart extends Model
{
    protected $table = 'vehicle_parts';

    protected $fillable = [
        'vehicle_id',
        'parte_id',
        'operador_id',
        'status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function parte()
    {
        return $this->belongsTo(Parte::class);
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
}
