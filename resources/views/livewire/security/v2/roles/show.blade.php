<div class="p-6 bg-base-100 shadow rounded-lg">
    <h2 class="text-2xl font-bold mb-4">Gestión de Permisos: {{ $role->name }}</h2>

    {{-- Toggle Global --}}
    <div class="mb-6">
        <x-toggle label="Seleccionar todos los permisos" wire:click="toggleAllPermissions" :checked="$this->isAllPermissionsSelected()" />
    </div>


    {{-- Permisos por Modelo --}}
    <h3 class="text-lg font-bold">Permisos por Modelo</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach ($models as $model)
            <div class="p-4 border rounded-lg">
                <x-collapse wire:model="show" separator class="bg-base-200">
                    <x-slot:heading>{{ Str::headline($model['name']) }}</x-slot:heading>
                    <x-slot:content class="bg-white dark:bg-gray-800 p-4">
                        <div class="flex justify-between items-center">
                            {{-- <h4 class="text-md font-bold">{{ Str::headline($model['name']) }}</h4> --}}
                            <x-toggle label="Seleccionar todos"
                                wire:click="toggleModelPermissions('{{ $model['name'] }}')" :checked="$this->isModelPermissionsSelected($model['name'])" />
                        </div>
                        <ul class="list-none ml-4 mt-5">
                            @foreach (['view_any', 'view', 'create', 'update', 'delete', 'restore', 'force_delete'] as $permission)
                                @php
                                    $permissionName = $permission . '_' . Str::snake($model['name']);
                                @endphp
                                <li>
                                    <x-checkbox label="{{ transformPermission($permissionName) }}"
                                        wire:model="selectedPermissions" value="{{ $permissionName }}"
                                        hint="{{ $permissionName }}"
                                        wire:change="syncPermission('{{ $permissionName }}')" />
                                    <hr class="my-2">
                                </li>
                            @endforeach
                        </ul>
                    </x-slot:content>
                </x-collapse>

            </div>
        @endforeach
    </div>

    {{-- Otros Permisos --}}
    <h3 class="text-lg font-bold">Otros Permisos</h3>
    <div class="p-4 border rounded-lg mb-8">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-md font-bold">Marcar permisos</h4>
            <x-toggle label="Seleccionar todos los permisos" wire:click="toggleOtherPermissions" :checked="$this->areOtherPermissionsSelected()" />
        </div>
        <ul class="list-none ml-4">
            @foreach ($otherPermissions as $permission)
                <li>
                    <x-checkbox label="{{ transformPermission($permission) }}" hint="{{ $permission }}"
                        wire:model="selectedPermissions" value="{{ $permission }}"
                        wire:change="syncPermission('{{ $permission }}')" />
                    <hr class="my-2">
                </li>
            @endforeach
            @if (empty($otherPermissions))
                <x-icon name="o-cube" label="No hay otros Permisos." />
            @endif
        </ul>

        <x-button icon="o-plus" class="mt-4" label="Agregar Permiso" wire:click="openCreateModal" />
    </div>


    {{-- Modal para crear permissions --}}
    <x-modal wire:model="create_permission_modal" title="Crear Permiso" subtitle="Añade un nuevo permiso" separator>
        <x-form wire:submit.prevent="createPermission">
            <x-input label="Nombre del Permiso" wire:model="permissionName" required inline />

            <x-slot:actions>
                <x-checkbox label="Continuar" wire:model="continuarCreando" hint="Continuar creando" />
                <x-button label="Cancelar" @click="$wire.create_permission_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>

@php
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

@section('js')
    <script>
        // window.addEventListener('evento', () => {
        //     location.reload();
        // });
    </script>
@endsection
