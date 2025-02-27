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

    public function vehicleParts()
    {
        return $this->hasMany(VehiclePart::class);
    }

    public function files()
    {
        return $this->hasManyThrough(File::class, VehiclePart::class, 'parte_id', 'model_id')
            ->where('files.model_type', VehiclePart::class);
    }
}
