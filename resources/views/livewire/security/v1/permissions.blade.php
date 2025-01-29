<div class="p-6 bg-base-100 shadow rounded-lg">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header title="Permisos" subtitle="Gestión de Permisos">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Buscar..." />
        </x-slot:middle>
        <x-slot:actions>
            <?php $filters = [
                [
                    'id' => 1,
                    'name' => 'Ascendente',
                ],
                [
                    'id' => 2,
                    'name' => 'Descendente',
                ],
            ]; ?>
            {{-- <x-select label="Ordenar" icon="o-funnel" :options="$filters" inline /> --}}

            <x-button icon="o-plus" class="btn-primary" wire:click="openCreateModal" />
        </x-slot:actions>
    </x-header>

    {{-- Tabla --}}
    <x-table :headers="$headers" :rows="$permissions" :sort-by="$sortBy" striped with-pagination>
        @scope('header_name', $header)
            {{ $header['label'] }} <x-icon name="s-question-mark-circle" />
        @endscope

        {{-- Sobrescribe la celda del nombre del permiso --}}
        @scope('cell_name', $permission)
            <x-badge :value="$permission->name" class="badge-primary" />
        @endscope

        {{-- Sobrescribe las acciones --}}
        @scope('actions', $permission)
            <div class="flex gap-2">
                <x-button icon="o-pencil" wire:click="editPermission({{ $permission->id }})" spinner class="btn-sm" />
                <x-button icon="o-trash" wire:click="deletePermission({{ $permission->id }})" spinner class="btn-sm" />
            </div>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Vacio." />
        </x-slot:empty>
    </x-table>

    {{-- Modal para crear permissions --}}
    <x-modal wire:model="create_permission_modal" title="Crear Permiso" subtitle="Añade un nuevo permiso" separator>
        <x-form wire:submit.prevent="createPermission">
            <x-input label="Nombre del Permiso" wire:model="permissionName" required />

            <x-slot:actions>
                <x-checkbox label="Continuar" wire:model="continuarCreando" hint="Continuar creando" />
                <x-button label="Cancelar" @click="$wire.create_permission_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal para editar permissions --}}
    <x-modal wire:model="edit_permission_modal" title="Editar Permiso" subtitle="Actualiza un permiso existente"
        separator>
        <x-form wire:submit.prevent="updatepermission">
            <x-input label="Nombre del Permiso" wire:model="permissionName" required />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.edit_permission_modal = false" />
                <x-button label="Actualizar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
