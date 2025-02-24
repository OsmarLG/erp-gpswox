<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'apellidos',
        'username',
        'email',
        'avatar',
        'password',
        'fecha_nacimiento',
        'celular1',
        'celular2',
        'telefono_casa',
        'nombre_contacto_con_quien_vive',
        'ine_frontal',
        'ine_reverso',
        'licencia_frontal',
        'licencia_reverso',
        'cp_domicilio',
        'direccion_domicilio',
        'comprobante_domicilio',
        'ubicacion_domicilio',
        'foto_fachada',
        'foto_estacionamiento',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
        ];
    }

    /**
     * Accessor para la edad (calculada)
     */
    public function getEdadAttribute()
    {
        if (! $this->fecha_nacimiento) {
            return null;
        }

        return Carbon::parse($this->fecha_nacimiento)->age;
    }

    /**
     * Relación con el vehiculo.
     */
    public function vehiculo()
    {
        return $this->hasOne(Vehicle::class, 'operador_id');
    }
}
