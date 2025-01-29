<div>
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header separator progress-indicator>
    </x-header>
    <x-form wire:submit="register">
        <x-input label="Name" class="border-blue-900" icon="o-user" wire:model="name" hint="Enter your Name" />

        <x-input label="Email" class="border-blue-900" icon="o-envelope" wire:model="email" hint="Enter your email" />

        <x-input label="Password" class="border-blue-900" wire:model="password" icon="o-key" type="password"
            hint="Enter your password" />

        <x-slot:actions>
            <x-button class="bg-blue-100" label="Register" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
