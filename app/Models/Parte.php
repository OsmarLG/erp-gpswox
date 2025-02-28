<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parte extends Model
{
    protected $table = "partes";

    protected $fillable = [
        'nombre',
        'categoria_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function vehicleRequests()
    {
        return $this->hasMany(VehicleRequest::class, 'parte_id');
    }

    public function files()
    {
        return $this->hasManyThrough(File::class, VehicleRequest::class, 'parte_id', 'model_id')
            ->where('files.model_type', VehicleRequest::class)->orderBy('files.created_at', 'desc')->take(5);
    }
}
