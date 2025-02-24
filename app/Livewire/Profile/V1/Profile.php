<?php

namespace App\Livewire\Profile\V1;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Http;

class Profile extends Component
{
    use WithFileUploads, Toast;

    public $user;

    // Campos básicos
    public $name;
    public $lastname;
    public $email;
    public $username;
    public $avatar;
    public $newAvatar;

    // Contraseñas
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    // Fecha de nacimiento
    public $fecha_nacimiento;

    // Campos extra para operador
    public $celular1;
    public $celular2;
    public $telefono_casa;
    public $nombre_contacto_con_quien_vive;
    public $ine_frontal;          // Ruta en BD
    public $ine_frontal_file;     // Archivo subido
    public $ine_reverso;
    public $ine_reverso_file;
    public $licencia_frontal;
    public $licencia_frontal_file;
    public $licencia_reverso;
    public $licencia_reverso_file;
    public $cp_domicilio;
    public $direccion_domicilio;
    public $comprobante_domicilio;
    public $comprobante_domicilio_file;
    public $ubicacion_domicilio;
    public $foto_fachada;
    public $foto_fachada_file;
    public $foto_estacionamiento;
    public $foto_estacionamiento_file;

    // Modal control
    public bool $update_avatar_modal = false;
    public bool $viewImageModal = false;
    public $viewImageModalUrl = null;

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->lastname = $this->user->apellidos;
        $this->email = $this->user->email;
        $this->username = $this->user->username;
        $this->avatar = $this->user->avatar;

        // Fecha de nacimiento
        $this->fecha_nacimiento = $this->user->fecha_nacimiento
            ? $this->user->fecha_nacimiento->format('Y-m-d')
            : null;

        // Si el user es operador, llenamos estos campos
        if ($this->user->hasRole('operador')) {
            $this->celular1 = $this->user->celular1;
            $this->celular2 = $this->user->celular2;
            $this->telefono_casa = $this->user->telefono_casa;
            $this->nombre_contacto_con_quien_vive = $this->user->nombre_contacto_con_quien_vive;
            $this->ine_frontal = $this->user->ine_frontal;
            $this->ine_reverso = $this->user->ine_reverso;
            $this->licencia_frontal = $this->user->licencia_frontal;
            $this->licencia_reverso = $this->user->licencia_reverso;
            $this->cp_domicilio = $this->user->cp_domicilio;
            $this->direccion_domicilio = $this->user->direccion_domicilio;
            $this->comprobante_domicilio = $this->user->comprobante_domicilio;
            $this->ubicacion_domicilio = $this->user->ubicacion_domicilio;
            $this->foto_fachada = $this->user->foto_fachada;
            $this->foto_estacionamiento = $this->user->foto_estacionamiento;
        }
    }

    public function updateProfile()
    {
        // Validaciones básicas
        $rules = [
            'name' => 'required|string|max:255',
            'lastname' => 'string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $this->user->id,
            'fecha_nacimiento' => 'nullable|date',
        ];

        // Si el usuario es operador, validamos campos extra
        if ($this->user->hasRole('operador')) {
            $rules = array_merge(
                $rules,
                [
                    'celular1' => ['nullable', 'regex:/^\+?\d{1,10}$/'],
                    'celular2' => ['nullable', 'regex:/^\+?\d{1,10}$/'],
                    'telefono_casa' => ['nullable', 'regex:/^\+?\d{1,10}$/'],
                    'nombre_contacto_con_quien_vive' => 'nullable|string|max:255',
                    'cp_domicilio' => 'nullable|numeric',
                    'direccion_domicilio' => 'nullable|string|max:255',
                    'ubicacion_domicilio' => 'nullable|string|max:255',
                    // archivos
                    'ine_frontal_file' => 'nullable|image|max:2048',  // 2MB
                    'ine_reverso_file' => 'nullable|image|max:2048',
                    'licencia_frontal_file' => 'nullable|image|max:2048',
                    'licencia_reverso_file' => 'nullable|image|max:2048',
                    'comprobante_domicilio_file' => 'nullable|image|max:2048',
                    'foto_fachada_file' => 'nullable|image|max:2048',
                    'foto_estacionamiento_file' => 'nullable|image|max:2048',
                ]
            );
        }

        $this->validate($rules);

        // Preparamos data base
        $data = [
            'name'             => $this->name,
            'apellidos'        => $this->lastname,
            'email'            => $this->email,
            'username'         => $this->username,
            'fecha_nacimiento' => $this->fecha_nacimiento,
        ];

        // Solo si es operador agregamos campos extra
        if ($this->user->hasRole('operador')) {
            $data = array_merge($data, [
                'celular1' => $this->celular1,
                'celular2' => $this->celular2,
                'telefono_casa' => $this->telefono_casa,
                'nombre_contacto_con_quien_vive' => $this->nombre_contacto_con_quien_vive,
                'cp_domicilio' => $this->cp_domicilio,
                'direccion_domicilio' => $this->direccion_domicilio,
                'ubicacion_domicilio' => $this->ubicacion_domicilio,
            ]);

            // Manejar archivos
            if ($this->ine_frontal_file) {
                $ineFPath = $this->ine_frontal_file->store('ine', 'public');
                $data['ine_frontal'] = $ineFPath;
            }
            if ($this->ine_reverso_file) {
                $ineRPath = $this->ine_reverso_file->store('ine', 'public');
                $data['ine_reverso'] = $ineRPath;
            }
            if ($this->licencia_frontal_file) {
                $licFPath = $this->licencia_frontal_file->store('licencia', 'public');
                $data['licencia_frontal'] = $licFPath;
            }
            if ($this->licencia_reverso_file) {
                $licRPath = $this->licencia_reverso_file->store('licencia', 'public');
                $data['licencia_reverso'] = $licRPath;
            }
            if ($this->comprobante_domicilio_file) {
                $compPath = $this->comprobante_domicilio_file->store('comprobantes', 'public');
                $data['comprobante_domicilio'] = $compPath;
            }
            if ($this->foto_fachada_file) {
                $fachadaPath = $this->foto_fachada_file->store('fachadas', 'public');
                $data['foto_fachada'] = $fachadaPath;
            }
            if ($this->foto_estacionamiento_file) {
                $estaPath = $this->foto_estacionamiento_file->store('estacionamientos', 'public');
                $data['foto_estacionamiento'] = $estaPath;
            }
        }

        // Actualizar al usuario
        $this->user->update($data);

        $this->toast(
            type: 'success',
            title: 'Cambios Guardados',
            description: 'Cambios del perfil guardados con éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($this->current_password, $this->user->password)) {
            $this->toast(
                type: 'error',
                title: 'Contraseña Incorrecta',
                description: 'La contraseña actual es incorrecta',
                icon: 'o-information-circle',
                css: 'alert-error text-white text-sm',
                timeout: 3000,
            );
        } else {
            $this->user->update([
                'password' => Hash::make($this->new_password),
            ]);

            $this->toast(
                type: 'success',
                title: 'Contraseña Actualizada',
                description: 'Contraseña actualizada con éxito',
                icon: 'o-information-circle',
                css: 'alert-success text-white text-sm',
                timeout: 3000,
            );
        }

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
    }

    public function saveAvatar()
    {
        $this->validate([
            'newAvatar' => 'image|max:1024', // Máximo 1MB
        ]);

        // Guardar la imagen en storage
        $path = $this->newAvatar->store('avatars', 'public');

        // Actualizar el usuario con la nueva imagen
        $this->user->update(['avatar' => $path]);

        // Resetear el campo para limpiar la selección
        $this->reset('newAvatar');

        $this->update_avatar_modal = false;

        // Mostrar notificación de éxito
        $this->toast(
            type: 'success',
            title: 'Avatar Actualizado',
            description: 'Tu foto de perfil ha sido actualizada con éxito',
            icon: 'o-check-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );

        return redirect()->route('profile.index');
    }

    public function openUpdateAvatarModal()
    {
        $this->reset(['newAvatar']);
        $this->update_avatar_modal = true;
    }

    public function openViewImageModal($imageUrl)
    {
        $this->viewImageModalUrl = $imageUrl;
        $this->viewImageModal = true;
    }

    public function closeViewImageModal()
    {
        $this->viewImageModal = false;
        $this->viewImageModalUrl = null;
    }

    public function render()
    {
        return view('livewire.profile.v1.profile');
    }
}
