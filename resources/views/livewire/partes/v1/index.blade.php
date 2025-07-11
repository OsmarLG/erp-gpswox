<div class="p-6 bg-base-100 shadow rounded-lg">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header title="Partes" subtitle="Gestión de Partes">
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
    <x-table :headers="$headers" :rows="$partes" :sort-by="$sortBy" striped with-pagination>
        {{-- Sobrescribe las acciones --}}
        @scope('actions', $parte)
        <div class="flex gap-2">
            <x-button icon="o-pencil" wire:click="editParte({{ $parte->id }})" spinner class="btn-sm" />
            
            <x-button
                icon="o-trash"
                class="btn-sm"
                spinner
                x-data
                x-on:click.prevent="if (confirm('¿Estás seguro de eliminar esta parte?')) { $wire.deleteParte({{ $parte->id }}) }"
            />
        </div>
        
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Vacio." />
        </x-slot:empty>
    </x-table>

    {{-- Modal para crear partes --}}
    <x-modal wire:model="create_parte_modal" title="Crear Parte" subtitle="Añade una nueva Parte" separator>
        <x-form wire:submit.prevent="createParte">
            <x-input label="Nombre" placeholder="Nombre de la Parte" icon="o-cog" hint="Nombre de la Parte"
                wire:model="parteNombre" required />

            <x-select label="Categoría" wire:model="categoria_id" :options="$availableCategorias" required />

            <x-slot:actions>
                <x-checkbox label="Continuar" wire:model="continuarCreando" hint="Continuar creando" />
                <x-button label="Cancelar" @click="$wire.create_parte_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal para editar parte --}}
    <x-modal wire:model="edit_parte_modal" title="Editar Parte" subtitle="Actualiza una Parte existente" separator>
        <x-form wire:submit.prevent="updateParte">
            <x-input label="Nombre" placeholder="Nombre de la Parte" icon="o-cog" hint="Nombre de la Parte"
                wire:model="parteNombre" />

            <x-select label="Categoría" wire:model="categoria_id" :options="$availableCategorias" required />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.edit_parte_modal = false" />
                <x-button label="Actualizar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
