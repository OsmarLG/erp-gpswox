<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'files';

    protected $fillable = [
        'model_type',
        'model_id',
        'path',
        'type',
        'description',
        'operador_id',
    ];

    /**
     * Relación polimórfica inversa (belongsTo morph).
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Relación con el operador (user).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}
