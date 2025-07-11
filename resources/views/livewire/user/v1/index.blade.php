<div class="p-6 bg-base-100 shadow rounded-lg">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header title="Usuarios" subtitle="Gestión de Usuarios">
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
    <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" striped with-pagination>
        @scope('header_name', $header)
            {{ $header['label'] }} <x-icon name="s-question-mark-circle" />
        @endscope

        @scope('cell_avatar', $user)
            @if ($user->avatar)
                <x-avatar :image="asset('storage/' . $user->avatar)" class="!w-10" />
            @else
                <?php
                $words = explode(' ', $user->name);
                
                $initials = '';
                foreach ($words as $word) {
                    if (!empty($word)) {
                        $initials .= strtoupper($word[0]);
                    }
                }
                ?>
                <x-avatar placeholder="{{ $initials }}" class="!w-10" />
            @endif
        @endscope

        {{-- Sobrescribe la celda del nombre del User --}}
        @scope('cell_name', $user)
            <x-badge :value="$user->name" class="badge-primary" />
        @endscope

        @scope('cell_fecha_nacimiento', $user)
            <x-badge :value="optional($user->fecha_nacimiento)?->format('d/m/Y')" />
        @endscope

        @scope('cell_edad', $user)
            <x-badge :value="$user->edad . ' años'" />
        @endscope

        @scope('cell_roles', $user)
            <div class="flex flex-wrap gap-1">
                @foreach ($user->roles as $role)
                    <x-badge :value="$role->name" class="bg-red-200 text-black dark:bg-slate-800 dark:text-white" />
                @endforeach
            </div>
        @endscope

        {{-- Sobrescribe las acciones --}}
        @scope('actions', $user)
            <div class="flex gap-2">
                <x-button icon="o-eye" wire:click="viewUser({{ $user->id }})" spinner class="btn-sm" />
                <x-button icon="o-pencil" wire:click="editUser({{ $user->id }})" spinner class="btn-sm" />
                <x-button icon="o-trash" class="btn-sm" spinner x-data
                    x-on:click.prevent="if (confirm('¿Estás seguro de eliminar este usuario?')) { $wire.deleteUser({{ $user->id }}) }" />
            </div>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Vacio." />
        </x-slot:empty>
    </x-table>

    {{-- Modal para crear roles --}}
    <x-modal wire:model="create_user_modal" title="Crear Usuario" subtitle="Añade un nuevo Usuario" separator>
        <x-form wire:submit.prevent="createUser">
            <x-input label="Nombre" placeholder="Tú nombre" icon="o-user" hint="Tú Nombre" wire:model="userName"
                required />
            <x-input label="Apellidos" placeholder="Tus Apelldios" icon="o-user" hint="Tu Apellido"
                wire:model="userLastName" required />
            <x-input label="Username" wire:model="userUsername" required placeholder="Tú Nombre de Usuario"
                icon="o-user" hint="Tú Usuario" />
            <x-input label="Email" wire:model="userEmail" type="email" required placeholder="Tú Correo"
                icon="o-envelope" hint="Tú Nombre de Correo" />
            <x-password label="Contraseña" wire:model="userPassword" type="password" placeholder="Contraseña segura"
                hint="Ingresa tu Contraseña" clearable required />
            <x-password label="Confirmar Contraseña" wire:model="userPassword_confirmation" type="password"
                placeholder="Contraseña segura" hint="Confirma tu Contraseña" clearable required />

            <div class="mb-4">
                <x-choices label="Roles" allow-all wire:model="selectedRoles" :options="$availableRoles" />
            </div>

            <div class="mb-4">
                <x-choices label="Permisos" allow-all wire:model="selectedPermissions" :options="$availablePermissions" />
            </div>

            <x-slot:actions>
                <x-checkbox label="Continuar" wire:model="continuarCreando" hint="Continuar creando" />
                <x-button label="Cancelar" @click="$wire.create_user_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal para editar usuarios --}}
    <x-modal wire:model="edit_user_modal" title="Editar Usuario" subtitle="Actualiza un Usuario existente" separator>
        <x-form wire:submit.prevent="updateUser">
            <x-input label="Nombre Completo" placeholder="Tú nombre" icon="o-user" hint="Tú Nombre Completo"
                wire:model="userName" required />
            <x-input label="Apellidos" placeholder="Tus Apelldios" icon="o-user" hint="Tu Apellido"
                wire:model="userLastName" />
            <x-input label="Username" wire:model="userUsername" required placeholder="Tú Nombre de Usuario"
                icon="o-user" hint="Tú Usuario" />
            <x-input label="Email" wire:model="userEmail" type="email" required placeholder="Tú Correo"
                icon="o-envelope" hint="Tú Nombre de Correo" />
            <x-password label="Contraseña" wire:model="userPassword" type="password" placeholder="Contraseña segura"
                hint="Ingresa tu Contraseña" clearable />
            <x-password label="Confirmar Contraseña" wire:model="userPassword_confirmation" type="password"
                placeholder="Contraseña segura" hint="Confirma tu Contraseña" clearable />

            <div class="mb-4">
                <x-choices label="Roles" allow-all wire:model="selectedRoles" :options="$availableRoles" />
            </div>

            <div class="mb-4">
                <x-choices label="Permisos" allow-all wire:model="selectedPermissions" :options="$availablePermissions" />
            </div>

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.edit_user_modal = false" />
                <x-button label="Actualizar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
