<div>
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header separator progress-indicator>
    </x-header>
    <h1 class="sm:hidden">Iniciar Sesión</h1>
    <x-form wire:submit="authenticate">
        {{-- <x-input label="Email" class="border-blue-900" icon="o-envelope" wire:model="email" hint="Enter your email" /> --}}
        <x-input label="Username" class="border-blue-900" icon="o-user" wire:model="username"
            hint="Introduce tu nombre de usuario" />
        <x-input label="Contraseña" class="border-blue-900" wire:model="password" icon="o-key" type="password"
            hint="Introduce tu contraseña" />

        <x-slot:actions>
            {{-- <x-button link="{{ route('password.request') }}" label="Forgot Password" /> --}}
            <x-button label="Entrar" class="bg-blue-100" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
