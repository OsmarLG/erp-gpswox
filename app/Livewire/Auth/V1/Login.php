<?php

namespace App\Livewire\Auth\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;
use Mary\Traits\Toast;

class Login extends Component
{
    use Toast; ///

    // public ?string $email;
    public ?string $username;
    public ?string $password;

    public function authenticate()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt(['username' => $this->username, 'password' => $this->password])) {
            request()->session()->regenerate();

            $user = Auth::user();

            // Verifica si el usuario tiene el rol de "admin" o "master"
            $isAdminOrMaster = $user->hasRole('admin') || $user->hasRole('master');

            // Verifica si tiene un vehículo asignado
            $hasVehicle = $user->vehiculo !== null;

            // Si no es admin ni master y no tiene vehículo, se le bloquea
            if (!$isAdminOrMaster && !$hasVehicle) {
                Auth::logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
                $this->error("No tienes permiso para iniciar sesión.", position: 'toast-top');
                return;
            } ////

            $this->success('Logged in successfully', position: 'toast-top');
            return Redirect::route('dashboard');
        } else {
            $this->error("Cannot verify the credentials !", position: 'toast-top');
        }
    }

    public function render()
    {
        return view('livewire.auth.v1.login');
    }
}
