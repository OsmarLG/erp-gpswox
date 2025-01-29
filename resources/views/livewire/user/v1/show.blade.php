<div class="p-6 bg-base-100 shadow rounded-lg">
    {{-- Encabezado del Perfil --}}
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <h2 class="text-2xl font-bold text-center mb-6">Perfil de {{ $user->name }}</h2>

    {{-- Header con Avatar e info básica --}}
    <div class="flex items-center space-x-4 mb-6">
        <div class="relative group">
            @php
                // Generar iniciales
                $initials = collect(explode(' ', $user->name))
                    ->map(fn($word) => strtoupper($word[0]))
                    ->implode('');
            @endphp

            @if ($user->avatar)
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
                    <p><strong>Nombre:</strong> {{ $user->name }}</p>
                    <p><strong>Username:</strong> {{ $user->username }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
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
    </x-tabs>
</div>
