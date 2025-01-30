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
                <x-input label="Contacto con quien vive" wire:model="nombre_contacto_con_quien_vive" />
                <x-input label="Código Postal" wire:model="cp_domicilio" />

                <!-- Grid para los archivos (INE, licencia, etc.) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                    <!-- INE Frontal -->
                    <div>
                        <x-file wire:model="ine_frontal_file" label="INE (Frontal)"
                            accept="image/png, image/jpg, image/jpeg" crop-after-change>
                            <!-- Ajustamos el ancho/alto -->
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                <img src="{{ $ine_frontal_file
                                    ? $ine_frontal_file->temporaryUrl()
                                    : ($user->ine_frontal
                                        ? asset('storage/' . $user->ine_frontal)
                                        : asset('storage/user.png')) }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>
                        <div>
                            <x-button label="Ver"
                                wire:click="openViewImageModal('{{ $user->ine_frontal ? asset('storage/' . $user->ine_frontal) : asset('storage/user.png') }}')"
                                icon-right="o-eye" spinner />
                        </div>
                    </div>


                    <!-- INE Reverso -->
                    <div>
                        <x-file wire:model="ine_reverso_file" label="INE (Reverso)"
                            accept="image/png, image/jpg, image/jpeg" crop-after-change>
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                <img src="{{ $ine_reverso_file
                                    ? $ine_reverso_file->temporaryUrl()
                                    : ($user->ine_reverso
                                        ? asset('storage/' . $user->ine_reverso)
                                        : asset('storage/user.png')) }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>
                        <div>
                            <x-button label="Ver"
                                wire:click="openViewImageModal('{{ $user->ine_reverso ? asset('storage/' . $user->ine_reverso) : asset('storage/user.png') }}')"
                                icon-right="o-eye" spinner />
                        </div>
                    </div>

                    <!-- Licencia Frontal -->
                    <div>
                        <x-file wire:model="licencia_frontal_file" label="Licencia (Frontal)"
                            accept="image/png, image/jpg, image/jpeg" crop-after-change>
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">

                                <img src="{{ $licencia_frontal_file
                                    ? $licencia_frontal_file->temporaryUrl()
                                    : ($user->licencia_frontal
                                        ? asset('storage/' . $user->licencia_frontal)
                                        : asset('storage/user.png')) }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>
                        <div>
                            <x-button label="Ver"
                                wire:click="openViewImageModal('{{ $user->licencia_frontal ? asset('storage/' . $user->licencia_frontal) : asset('storage/user.png') }}')"
                                icon-right="o-eye" spinner />
                        </div>
                    </div>

                    <!-- Licencia Reverso -->
                    <div>
                        <x-file wire:model="licencia_reverso_file" label="Licencia (Reverso)"
                            accept="image/png, image/jpg, image/jpeg" crop-after-change>
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">

                                <img src="{{ $licencia_reverso_file
                                    ? $licencia_reverso_file->temporaryUrl()
                                    : ($user->licencia_reverso
                                        ? asset('storage/' . $user->licencia_reverso)
                                        : asset('storage/user.png')) }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>
                        <div>
                            <x-button label="Ver"
                                wire:click="openViewImageModal('{{ $user->licencia_reverso ? asset('storage/' . $user->licencia_reverso) : asset('storage/user.png') }}')"
                                icon-right="o-eye" spinner />
                        </div>
                    </div>

                    <!-- Comprobante de Domicilio -->
                    <div>
                        <x-file wire:model="comprobante_domicilio_file" label="Comprobante de Domicilio"
                            accept="image/png, image/jpg, image/jpeg" crop-after-change>
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">

                                <img src="{{ $comprobante_domicilio_file
                                    ? $comprobante_domicilio_file->temporaryUrl()
                                    : ($user->comprobante_domicilio
                                        ? asset('storage/' . $user->comprobante_domicilio)
                                        : asset('storage/user.png')) }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>
                        <div>
                            <x-button label="Ver"
                                wire:click="openViewImageModal('{{ $user->comprobante_domicilio ? asset('storage/' . $user->comprobante_domicilio) : asset('storage/user.png') }}')"
                                icon-right="o-eye" spinner />
                        </div>
                    </div>

                    <!-- Foto Fachada -->
                    <div>
                        <x-file wire:model="foto_fachada_file" label="Foto de Fachada"
                            accept="image/png, image/jpg, image/jpeg" crop-after-change>
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">

                                <img src="{{ $foto_fachada_file
                                    ? $foto_fachada_file->temporaryUrl()
                                    : ($user->foto_fachada
                                        ? asset('storage/' . $user->foto_fachada)
                                        : asset('storage/user.png')) }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>
                        <div>
                            <x-button label="Ver"
                                wire:click="openViewImageModal('{{ $user->foto_fachada ? asset('storage/' . $user->foto_fachada) : asset('storage/user.png') }}')"
                                icon-right="o-eye" spinner />
                        </div>
                    </div>

                    <!-- Foto Estacionamiento -->
                    <div>
                        <x-file wire:model="foto_estacionamiento_file" label="Foto de Estacionamiento"
                            accept="image/png, image/jpg, image/jpeg" crop-after-change>
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">

                                <img src="{{ $foto_estacionamiento_file
                                    ? $foto_estacionamiento_file->temporaryUrl()
                                    : ($user->foto_estacionamiento
                                        ? asset('storage/' . $user->foto_estacionamiento)
                                        : asset('storage/user.png')) }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>
                        <div>
                            <x-button label="Ver"
                                wire:click="openViewImageModal('{{ $user->foto_estacionamiento ? asset('storage/' . $user->foto_estacionamiento) : asset('storage/user.png') }}')"
                                icon-right="o-eye" spinner />
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
    <x-modal wire:model="update_avatar_modal" title="Actualizar Avatar"
        subtitle="Selecciona y recorta tu nueva imagen" separator>
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

    <!-- Modal para ver imagen en grande -->
    <x-modal wire:model="viewImageModal" max-width="full" class="!max-w-full !h-screen !rounded-none p-0">
        <div class="relative w-full h-full flex items-center justify-center bg-black bg-opacity-90">
            @if ($viewImageModalUrl)
                <img src="{{ $viewImageModalUrl }}" alt="Vista Previa"
                    class="max-w-full max-h-full object-contain" />
            @endif
        </div>
    </x-modal>
</div>
