<div class="p-6 bg-base-100 shadow-lg rounded-lg space-y-6">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <h2 class="text-2xl font-bold text-center">Perfil de Usuario</h2>

    <!-- Avatar y Datos Básicos -->
    <div class="flex items-center space-x-4">
        <div class="relative group">
            <?php
            $initials = collect(explode(' ', trim($user->name))) // elimina espacios al inicio/final
                ->filter() // elimina cadenas vacías
                ->map(fn($word) => strtoupper($word[0] ?? '')) // previene error si la palabra está vacía
                ->implode('');
            ?>

            @if ($user->avatar)
                <x-avatar :image="asset('storage/' . $user->avatar)" class="!w-24 !h-24 shadow-lg" />
            @else
                <x-avatar placeholder="{{ $initials }}" class="!w-24 !h-24 shadow-lg" />
            @endif

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

    <form wire:submit.prevent="updateProfile" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold">Nombre</label>
            <input type="text" wire:model="name" class="input input-bordered w-full">
        </div>
        <div>
            <label class="block text-sm font-semibold">Apellidos</label>
            <input type="text" wire:model="lastname" class="input input-bordered w-full">
        </div>
        <div>
            <label class="block text-sm font-semibold">Email</label>
            <input type="email" wire:model="email" class="input input-bordered w-full">
        </div>
        <div>
            <label class="block text-sm font-semibold">Username</label>
            <input type="text" wire:model="username" class="input input-bordered w-full">
        </div>
        <x-datetime label="Fecha de nacimiento" wire:model="fecha_nacimiento" icon="o-calendar" />

        @if (Auth::user()->hasRole('operador'))
            <div class="mt-4">
                <x-input label="Celular 1" wire:model="celular1" icon-right="o-phone" />
                <x-input label="Celular 2" wire:model="celular2" icon-right="o-phone" />
                <x-input label="Teléfono de casa" wire:model="telefono_casa" icon-right="o-phone" />
                {{-- <x-input label="Nombre con quien vive" wire:model="nombre_contacto_con_quien_vive" /> --}}
                <x-input label="Código Postal" wire:model="cp_domicilio" />

                <x-input label="Nombre Con Quien Vive" wire:model="contacto_emergencia_nombre" />
                <x-input label="Telefono Con Quien Vive" wire:model="contacto_emergencia_telefono" />
                <x-input label="Link Google Maps (Domicilio)" wire:model="link_google_maps" />
                <x-input label="Perfil Uber" wire:model="perfil_uber" />
                <x-input label="Telefono Uber" wire:model="datos_uber" />

                <br>

                <p class="text-lg font-semibold">Archivos</p>

                <div x-data="{ isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) }">
                    <template x-if="isMobile">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                            @foreach ([
        'ine_frontal_file' => 'INE (Frontal)',
        'ine_reverso_file' => 'INE (Reverso)',
        'licencia_frontal_file' => 'Licencia (Frontal)',
        'licencia_reverso_file' => 'Licencia (Reverso)',
        'comprobante_domicilio_file' => 'Comprobante de Domicilio',
        'foto_fachada_file' => 'Foto de Fachada',
        'foto_estacionamiento_file' => 'Foto de Estacionamiento',
    ] as $field => $label)
                                <div>
                                    <x-file wire:model="{{ $field }}" label="{{ $label }}"
                                        accept="image/png, image/jpg, image/jpeg" capture="environment"
                                        crop-after-change>
                                        <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                            <img src="{{ $$field
                                                ? $$field->temporaryUrl()
                                                : ($user->{str_replace('_file', '', $field)}
                                                    ? asset('storage/' . $user->{str_replace('_file', '', $field)})
                                                    : asset('storage/user.png')) }}"
                                                class="w-full h-full object-cover rounded-lg" />
                                        </div>
                                    </x-file>
                                    <div>
                                        <x-button label="Ver"
                                            wire:click="openViewImageModal('{{ $user->{str_replace('_file', '', $field)} ? asset('storage/' . $user->{str_replace('_file', '', $field)}) : asset('storage/user.png') }}')"
                                            icon-right="o-eye" spinner />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </template>

                    <template x-if="!isMobile">
                        <div class="text-center text-sm italic text-gray-500 mt-4">
                            Las fotos solo pueden capturarse desde dispositivos móviles.
                        </div>
                    </template>
                </div>
            </div>
        @endif

        <button type="submit" class="w-full md:w-[25%] bg-slate-300 dark:bg-slate-800 dark:text-white btn">
            Actualizar Información Personal
        </button>
    </form>

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
        <button type="submit" class="w-full md:w-[25%] bg-slate-300 dark:bg-slate-800 dark:text-white btn">
            Actualizar Datos de Acceso
        </button>
    </form>

    <x-modal wire:model="update_avatar_modal" title="Actualizar Avatar" subtitle="Selecciona y recorta tu nueva imagen"
        separator>
        <x-form wire:submit.prevent="saveAvatar">
            <div class="flex justify-center">
                <x-file wire:model="newAvatar" accept="image/png, image/jpg, image/jpeg" capture="user"
                    crop-after-change>
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

    <x-modal wire:model="viewImageModal" max-width="full" class="!max-w-full !h-screen !rounded-none p-0">
        <div class="relative w-full h-full flex items-center justify-center bg-black bg-opacity-90">
            @if ($viewImageModalUrl)
                <img src="{{ $viewImageModalUrl }}" alt="Vista Previa"
                    class="max-w-full max-h-full object-contain" />
            @endif
        </div>
    </x-modal>
</div>
