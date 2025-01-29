<?php

namespace App\Livewire\Auth\v1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;
use Mary\Traits\Toast;

class Login extends Component
{
    use Toast;

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
