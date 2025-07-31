<div class="p-6 bg-base-100 shadow rounded-lg">
    {{-- Encabezado del Perfil --}}
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <h2 class="text-2xl font-bold text-center mb-6">Perfil de {{ $user->name . ' ' . $user->apellidos }}</h2>

    {{-- Header con Avatar e info básica --}}
    <div class="flex items-center space-x-4 mb-6">
        <div class="relative group">
            @php
                // Generar iniciales de forma segura
                $initials = collect(explode(' ', trim($user->name ?? '')))
                    ->filter() // elimina vacíos por múltiples espacios
                    ->map(fn($word) => strtoupper($word[0] ?? '')) // previene errores de offset
                    ->implode('');
            @endphp

            @if (!empty($user->avatar))
                <x-avatar :image="asset('storage/' . $user->avatar)" class="!w-24 !h-24 shadow-lg" />
            @else
                <x-avatar placeholder="{{ $initials }}" class="!w-24 !h-24 shadow-lg" />
            @endif
        </div>

        <div>
            <h3 class="text-xl font-semibold text-primary">{{ $user->name }}</h3>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
            <p class="text-sm text-gray-500">{{ '@' . $user->username }}</p>
        </div>
    </div>

    {{-- Ejemplo de Tabs con Mary UI --}}
    <x-tabs wire:model="selectedTab">
        {{-- Tab 1: Info Personal --}}
        <x-tab name="info-tab" label="Info Personal" icon="o-user">
            <div class="mt-4">
                <x-card title="Información Personal">
                    <p><strong>Nombre:</strong> {{ $user->name . ' ' . $user->apellidos }}</p>
                    <p><strong>Username:</strong> {{ $user->username }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Fecha de Nacimiento:</strong> {{ optional($user->fecha_nacimiento)?->format('d/m/Y') }}
                    </p>
                    <p><strong>Edad:</strong> {{ $user->edad . ' años' }}</p>
                    <p><strong>Celular 1:</strong> {{ $user->celular1 }}</p>
                    <p><strong>Celular 2:</strong> {{ $user->celular2 }}</p>
                    <p><strong>Teléfono de Casa:</strong> {{ $user->telefono_casa }}</p>
                    {{-- <p><strong>Nombre con quien vive:</strong> {{ $user->nombre_contacto_con_quien_vive }}</p> --}}
                    <p><strong>CP Domicilio:</strong> {{ $user->cp_domicilio }}</p>
                    <p><strong>Dirección:</strong> {{ $user->direccion_domicilio }}</p>
                    <p><strong>Nombre Con Quien Vive:</strong> {{ $user->contacto_emergencia_nombre }}</p>
                    <p><strong>Telefono Con Quien Vive:</strong> {{ $user->contacto_emergencia_telefono }}</p>
                    <p><strong>Link Google Maps (Domicilio):</strong> {{ $user->link_google_maps }}</p>
                    <p><strong>Perfil Uber:</strong> {{ $user->perfil_uber }}</p>
                    <p><strong>Telefono Uber:</strong> {{ $user->datos_uber }}</p>
                </x-card>
            </div>
        </x-tab>

        {{-- Tab 2: Roles y Permisos --}}
        <x-tab name="roles-tab" label="Roles y Permisos" icon="o-lock-closed">
            <div class="mt-4">
                <x-card title="Roles">
                    @if ($user->roles->count())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->roles as $role)
                                <x-badge :value="$role->name" class="badge-primary" />
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No tiene roles asignados.</p>
                    @endif
                </x-card>

                <div class="h-4"></div>

                <x-card title="Permisos (directos + heredados)">
                    @php
                        // Combina los permisos directos del usuario + los que provienen de sus roles
                        $allPermissions = $user->getAllPermissions();

                        function transformPermission($permission)
                        {
                            $translations = [
                                'view_any' => 'Ver Todos',
                                'view' => 'Ver',
                                'create' => 'Crear',
                                'update' => 'Actualizar',
                                'delete' => 'Eliminar',
                                'restore' => 'Restaurar',
                                'force_delete' => 'Eliminar Permanentemente',
                                'mark_as_read' => 'Marcar como Leído',
                                'mark_as_unread' => 'Marcar como no Leído',
                                'view_menu' => 'Ver Menú',
                            ];

                            $parts = explode('_', $permission);
                            $actionKey = implode('_', array_slice($parts, 0, -1));
                            $modelName = Str::headline(end($parts));

                            $action = $translations[$actionKey] ?? ucfirst($actionKey);

                            return "{$action} - {$modelName}";
                        }
                    @endphp

                    @if ($allPermissions->count())
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($allPermissions as $perm)
                                <x-badge :value="transformPermission($perm->name)"
                                    class="bg-gray-300 text-black dark:bg-slate-800 dark:text-white" />
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">No tiene permisos asignados.</p>
                    @endif
                </x-card>
            </div>
        </x-tab>

        {{-- Tab: Fotos/Documentos (sólo si es operador) --}}
        @if ($user->hasRole('operador'))
            <x-tab name="docs-tab" label="Documentos" icon="o-photo">
                <div class="mt-4">
                    <x-card title="Documentos / Imágenes">
                        {{-- Grid con imágenes solo lectura --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                            {{-- INE Frontal --}}
                            <div class="flex flex-col items-center">
                                <p class="font-semibold mb-2">INE (Frontal)</p>
                                <div class="relative w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                    <img src="{{ $user->ine_frontal ? asset('storage/' . $user->ine_frontal) : asset('storage/picture.png') }}"
                                        alt="INE Frontal" class="w-full h-full object-cover rounded-lg" />
                                    <div class="absolute bottom-2 right-2">
                                        <x-button label="Ver" icon="o-eye" class="btn-sm"
                                            wire:click="openViewImageModal(
                                                '{{ $user->ine_frontal ? asset('storage/' . $user->ine_frontal) : asset('storage/picture.png') }}'
                                            )" />
                                    </div>
                                </div>
                            </div>

                            {{-- INE Reverso --}}
                            <div class="flex flex-col items-center">
                                <p class="font-semibold mb-2">INE (Reverso)</p>
                                <div class="relative w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                    <img src="{{ $user->ine_reverso ? asset('storage/' . $user->ine_reverso) : asset('storage/picture.png') }}"
                                        alt="INE Reverso" class="w-full h-full object-cover rounded-lg" />
                                    <div class="absolute bottom-2 right-2">
                                        <x-button label="Ver" icon="o-eye" class="btn-sm"
                                            wire:click="openViewImageModal(
                                                '{{ $user->ine_reverso ? asset('storage/' . $user->ine_reverso) : asset('storage/picture.png') }}'
                                            )" />
                                    </div>
                                </div>
                            </div>

                            {{-- Licencia Frontal --}}
                            <div class="flex flex-col items-center">
                                <p class="font-semibold mb-2">Licencia (Frontal)</p>
                                <div class="relative w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                    <img src="{{ $user->licencia_frontal ? asset('storage/' . $user->licencia_frontal) : asset('storage/picture.png') }}"
                                        alt="Licencia Frontal" class="w-full h-full object-cover rounded-lg" />
                                    <div class="absolute bottom-2 right-2">
                                        <x-button label="Ver" icon="o-eye" class="btn-sm"
                                            wire:click="openViewImageModal(
                                                '{{ $user->licencia_frontal ? asset('storage/' . $user->licencia_frontal) : asset('storage/picture.png') }}'
                                            )" />
                                    </div>
                                </div>
                            </div>

                            {{-- Licencia Reverso --}}
                            <div class="flex flex-col items-center">
                                <p class="font-semibold mb-2">Licencia (Reverso)</p>
                                <div class="relative w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                    <img src="{{ $user->licencia_reverso ? asset('storage/' . $user->licencia_reverso) : asset('storage/picture.png') }}"
                                        alt="Licencia Reverso" class="w-full h-full object-cover rounded-lg" />
                                    <div class="absolute bottom-2 right-2">
                                        <x-button label="Ver" icon="o-eye" class="btn-sm"
                                            wire:click="openViewImageModal(
                                                '{{ $user->licencia_reverso ? asset('storage/' . $user->licencia_reverso) : asset('storage/picture.png') }}'
                                            )" />
                                    </div>
                                </div>
                            </div>

                            {{-- Comprobante de Domicilio --}}
                            <div class="flex flex-col items-center">
                                <p class="font-semibold mb-2">Comprobante de Domicilio</p>
                                <div class="relative w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                    <img src="{{ $user->comprobante_domicilio ? asset('storage/' . $user->comprobante_domicilio) : asset('storage/picture.png') }}"
                                        alt="Comprobante de Domicilio" class="w-full h-full object-cover rounded-lg" />
                                    <div class="absolute bottom-2 right-2">
                                        <x-button label="Ver" icon="o-eye" class="btn-sm"
                                            wire:click="openViewImageModal(
                                                '{{ $user->comprobante_domicilio ? asset('storage/' . $user->comprobante_domicilio) : asset('storage/picture.png') }}'
                                            )" />
                                    </div>
                                </div>
                            </div>

                            {{-- Foto Fachada --}}
                            <div class="flex flex-col items-center">
                                <p class="font-semibold mb-2">Foto de Fachada</p>
                                <div class="relative w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                    <img src="{{ $user->foto_fachada ? asset('storage/' . $user->foto_fachada) : asset('storage/picture.png') }}"
                                        alt="Foto Fachada" class="w-full h-full object-cover rounded-lg" />
                                    <div class="absolute bottom-2 right-2">
                                        <x-button label="Ver" icon="o-eye" class="btn-sm"
                                            wire:click="openViewImageModal(
                                                '{{ $user->foto_fachada ? asset('storage/' . $user->foto_fachada) : asset('storage/picture.png') }}'
                                            )" />
                                    </div>
                                </div>
                            </div>

                            {{-- Foto Estacionamiento --}}
                            <div class="flex flex-col items-center">
                                <p class="font-semibold mb-2">Foto Estacionamiento</p>
                                <div class="relative w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                    <img src="{{ $user->foto_estacionamiento ? asset('storage/' . $user->foto_estacionamiento) : asset('storage/picture.png') }}"
                                        alt="Foto Estacionamiento" class="w-full h-full object-cover rounded-lg" />
                                    <div class="absolute bottom-2 right-2">
                                        <x-button label="Ver" icon="o-eye" class="btn-sm"
                                            wire:click="openViewImageModal(
                                                '{{ $user->foto_estacionamiento ? asset('storage/' . $user->foto_estacionamiento) : asset('storage/picture.png') }}'
                                            )" />
                                    </div>
                                </div>
                            </div>

                        </div>{{-- end grid --}}
                    </x-card>
                </div>
            </x-tab>
        @endif
    </x-tabs>

    <!-- Modal para ver imagen en pantalla completa -->
    <x-modal wire:model="viewImageModal" max-width="full" class="!max-w-full !h-screen !rounded-none p-0">
        <div class="relative w-full h-full flex items-center justify-center bg-black bg-opacity-90">
            @if ($viewImageModalUrl)
                <img src="{{ $viewImageModalUrl }}" alt="Vista Previa"
                    class="max-w-full max-h-full object-contain" />
            @endif
        </div>
    </x-modal>
</div>
