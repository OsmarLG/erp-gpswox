<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleServiceRecordDetailArchivo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'vehicle_service_record_detail_archivos';

    protected $fillable = [
        'path',
        'description',
        'vehicle_service_record_detail_id',
        'operador_id', 
        'type',
    ];

    public function detail()
    {
        return $this->belongsTo(VehicleServiceRecordDetail::class, 'vehicle_service_record_detail_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}
