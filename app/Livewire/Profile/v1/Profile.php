<?php

namespace App\Livewire\Profile\v1;

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
    public $name;
    public $email;
    public $username;
    public $avatar;
    public $newAvatar;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public bool $update_avatar_modal = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->username = $this->user->username;
        $this->avatar = $this->user->avatar;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $this->user->id,
        ]);

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
        ]);

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

    public function render()
    {
        return view('livewire.profile.v1.profile');
    }
}
