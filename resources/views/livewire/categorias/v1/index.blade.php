<div class="p-6 bg-base-100 shadow rounded-lg">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header title="Categorias" subtitle="Gestión de Categorias">
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
    <x-table :headers="$headers" :rows="$categorias" :sort-by="$sortBy" striped with-pagination>
        @scope('cell_count_partes_column', $categoria)
            <button wire:click="showPartes({{ $categoria->id }})" class="text-blue-500 underline">
                {{ $categoria->partes->count() }}
            </button>
        @endscope


        {{-- Sobrescribe las acciones --}}
        @scope('actions', $categoria)
            <div class="flex gap-2">
                <x-button icon="o-pencil" wire:click="editCategoria({{ $categoria->id }})" spinner class="btn-sm" />
                <x-button icon="o-trash" wire:click="deleteCategoria({{ $categoria->id }})" spinner class="btn-sm" />
            </div>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Vacio." />
        </x-slot:empty>
    </x-table>

    {{-- Modal para crear categorias --}}
    <x-modal wire:model="create_categoria_modal" title="Crear Categoria" subtitle="Añade una nueva Categoria"
        separator>
        <x-form wire:submit.prevent="createCategoria">
            <x-input label="Nombre" placeholder="Nombre de la Categoria" icon="o-cog"
                hint="Nombre de la Categoria" wire:model="categoriaNombre" required />

            <x-slot:actions>
                <x-checkbox label="Continuar" wire:model="continuarCreando" hint="Continuar creando" />
                <x-button label="Cancelar" @click="$wire.create_categoria_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal para editar categoria --}}
    <x-modal wire:model="edit_categoria_modal" title="Editar Categoria" subtitle="Actualiza una Categoria existente"
        separator>
        <x-form wire:submit.prevent="updateCategoria">
            <x-input label="Nombre" placeholder="Nombre de la Categoria" icon="o-cog" hint="Nombre de la Categoria"
                wire:model="categoriaNombre" />                  

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.edit_categoria_modal = false" />
                <x-button label="Actualizar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="show_partes_modal" title="Partes de {{ $categoriaNombreSeleccionada }}" subtitle="Listado de partes" separator>
        <x-table :headers="[['key' => 'id', 'label' => '#'], ['key' => 'nombre', 'label' => 'Nombre']]" :rows="$partes">
            <x-slot:empty>
                <x-icon name="o-cube" label="No hay partes registradas." />
            </x-slot:empty>
        </x-table>
    
        <x-slot:actions>
            <x-button label="Cerrar" @click="$wire.show_partes_modal = false" />
        </x-slot:actions>
    </x-modal>    
</div>
