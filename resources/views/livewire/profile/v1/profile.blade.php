<div class="p-6 bg-base-100 shadow-lg rounded-lg space-y-6">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <h2 class="text-2xl font-bold text-center">Perfil de Usuario</h2>

    <!-- Avatar y Datos Básicos -->
    <div class="flex items-center space-x-4">
        <div class="relative group">
            <?php
            $initials = collect(explode(' ', $user->name))
                ->map(fn($word) => strtoupper($word[0]))
                ->implode('');
            ?>

            @if ($user->avatar)
                <x-avatar :image="asset('storage/' . $user->avatar)" class="!w-24 !h-24 shadow-lg" />
            @else
                <x-avatar placeholder="{{ $initials }}" class="!w-24 !h-24 shadow-lg" />
            @endif

            <!-- Botón de edición sobre la imagen -->
            <x-button icon="o-pencil"
                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition text-white rounded-full"
                wire:click="openUpdateAvatarModal" />
        </div>

        <div>
            <h3 class="text-xl font-semibold text-primary">{{ $name }}</h3>
            <p class="text-sm text-gray-500">{{ $email }}</p>
            <p class="text-sm text-gray-500">{{ '@' . $username }}</p>
        </div>
    </div>

    <!-- Formulario de Actualización -->
    <form wire:submit.prevent="updateProfile" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold">Nombre Completo</label>
            <input type="text" wire:model="name" class="input input-bordered w-full">
        </div>
        <div>
            <label class="block text-sm font-semibold">Email</label>
            <input type="email" wire:model="email" class="input input-bordered w-full">
        </div>
        <div>
            <label class="block text-sm font-semibold">Username</label>
            <input type="text" wire:model="username" class="input input-bordered w-full">
        </div>
        <button type="submit" class="w-full md:w-[25%] bg-slate-300 dark:bg-slate-800 dark:text-white btn">Actualizar
            Información Personal</button>
    </form>

    <!-- Formulario de Cambio de Contraseña -->
    <div class="divider"></div>
    <h3 class="text-lg font-semibold">Cambiar Contraseña</h3>
    <form wire:submit.prevent="updatePassword" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold">Contraseña Actual</label>
            <input type="password" wire:model="current_password" class="input input-bordered w-full">

            @error('current_password')
                <p class="text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold">Nueva Contraseña</label>
            <input type="password" wire:model="new_password" class="input input-bordered w-full">

            @error('new_password')
                <p class="text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold">Confirmar Nueva Contraseña</label>
            <input type="password" wire:model="new_password_confirmation" class="input input-bordered w-full">
        </div>
        <button type="submit" class="w-full md:w-[25%] bg-slate-300 dark:bg-slate-800 dark:text-white btn">Actualizar
            Datos de Acceso</button>
    </form>

    {{-- Modal para crear roles --}}
    <x-modal wire:model="update_avatar_modal" title="Actualizar Avatar" subtitle="Selecciona y recorta tu nueva imagen"
        separator>
        <x-form wire:submit.prevent="saveAvatar">
            <div class="flex justify-center">
                <x-file wire:model="newAvatar" accept="image/png, image/jpg, image/jpeg" crop-after-change>
                    <div class="w-40 h-40 rounded-full bg-black flex items-center justify-center">
                        <img src="{{ $newAvatar ? $newAvatar->temporaryUrl() : ($user->avatar ? asset('storage/' . $user->avatar) : asset('storage/user.png')) }}"
                            class="w-full h-full object-cover rounded-full" />
                    </div>
                </x-file>
            </div>

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.update_avatar_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

</div>
