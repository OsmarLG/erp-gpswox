<div class="p-6 bg-base-100 shadow rounded-lg">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header title="Roles" subtitle="Gestión de Roles">
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
    <x-table :headers="$headers" :rows="$roles" :sort-by="$sortBy" striped with-pagination>
        @scope('header_name', $header)
            {{ $header['label'] }} <x-icon name="s-question-mark-circle" />
        @endscope

        {{-- Sobrescribe la celda del nombre del rol --}}
        @scope('cell_name', $role)
            <x-badge :value="$role->name" class="badge-primary" />
        @endscope

        {{-- Sobrescribe la celda del nombre del rol --}}
        @scope('header_count_permissions_column', $header)
            {{ $header['label'] }}
        @endscope

        @scope('cell_count_permissions_column', $role)
            <u>{{ $role->permissions->count() }}</u>
        @endscope

        {{-- Sobrescribe las acciones --}}
        @scope('actions', $role)
            <div class="flex gap-2">
                <x-button icon="o-eye" wire:click="viewRole({{ $role->id }})" spinner class="btn-sm" />
                <x-button icon="o-pencil" wire:click="editRole({{ $role->id }})" spinner class="btn-sm" />
                <x-button icon="o-trash" wire:click="deleteRole({{ $role->id }})" spinner class="btn-sm" />
            </div>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Vacio." />
        </x-slot:empty>
    </x-table>

    {{-- Modal para crear roles --}}
    <x-modal wire:model="create_role_modal" title="Crear Rol" subtitle="Añade un nuevo rol" separator>
        <x-form wire:submit.prevent="createRole">
            <x-input label="Nombre del Rol" wire:model="roleName" required />

            <x-slot:actions>
                <x-checkbox label="Continuar" wire:model="continuarCreando" hint="Continuar creando" />
                <x-button label="Cancelar" @click="$wire.create_role_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal para editar roles --}}
    <x-modal wire:model="edit_role_modal" title="Editar Rol" subtitle="Actualiza un rol existente" separator>
        <x-form wire:submit.prevent="updateRole">
            <x-input label="Nombre del Rol" wire:model="roleName" required />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.edit_role_modal = false" />
                <x-button label="Actualizar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
