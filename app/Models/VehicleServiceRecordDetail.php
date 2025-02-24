<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleServiceRecordDetail extends Model
{
    use HasFactory;

    protected $table = 'vehicle_service_record_details';

    protected $fillable = [
        'vehicle_service_record_id',
        'operador_id',
        'detalle',
    ];

    public function serviceRecord()
    {
        return $this->belongsTo(VehicleServiceRecord::class, 'vehicle_service_record_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }
}
