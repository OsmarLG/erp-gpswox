<div class="p-6 bg-base-100 shadow rounded-lg">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header title="Servicios" subtitle="Gestión de Servicios">
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
    <x-table :headers="$headers" :rows="$servicios" :sort-by="$sortBy" striped with-pagination>

        @scope('cell_notificar', $servicio)
            <span class="px-2 py-1 rounded-lg text-white {{ $servicio->notificar ? 'bg-green-500' : 'bg-red-500' }}">
                {{ $servicio->notificar ? 'Sí' : 'No' }}
            </span>
        @endscope

        {{-- Sobrescribe las acciones --}}
        @scope('actions', $servicio)
            <div class="flex gap-2">
                <x-button icon="o-pencil" wire:click="editServicio({{ $servicio->id }})" spinner class="btn-sm" />
                <x-button icon="o-trash" wire:click="deleteServicio({{ $servicio->id }})" spinner class="btn-sm" />
            </div>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Vacio." />
        </x-slot:empty>
    </x-table>

    {{-- Modal para crear roles --}}
    <x-modal wire:model="create_service_modal" title="Crear Servicio" subtitle="Añade un nuevo Servicio" separator>
        <x-form wire:submit.prevent="createServicio">
            <x-input label="Nombre" placeholder="Nombre del Servicio" icon="o-cog" hint="Nombre del Servicio"
                wire:model="serviceNombre" required />

            <x-input label="Periodicidad KM" placeholder="Periodicidad en KM" icon="o-map"
                hint="Periodicidad en kilómetros (KM)" wire:model="servicePeriodicidadKm" required />

            <x-input label="Periodicidad Días" placeholder="Periodicidad en Días" icon="o-calendar"
                hint="Periodicidad en días" wire:model="servicePeriodicidadDias" required />

            <x-textarea label="Observaciones" placeholder="Observaciones..." icon="o-clipboard-document"
                hint="Detalles o notas adicionales" wire:model="serviceObservaciones" rows="3" />

            <x-checkbox label="Notificar" wire:model="serviceNotificar" />

            <x-slot:actions>
                <x-checkbox label="Continuar" wire:model="continuarCreando" hint="Continuar creando" />
                <x-button label="Cancelar" @click="$wire.create_service_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal para editar servicio --}}
    <x-modal wire:model="edit_service_modal" title="Editar Servicio" subtitle="Actualiza un Servicio existente"
        separator>
        <x-form wire:submit.prevent="updateServicio">
            <x-input label="Nombre" placeholder="Nombre del Servicio" icon="o-cog" hint="Nombre del Servicio"
                wire:model="serviceNombre" />

            <x-input label="Periodicidad KM" placeholder="Periodicidad en KM" icon="o-map"
                hint="Periodicidad en kilómetros (KM)" wire:model="servicePeriodicidadKm" />

            <x-input label="Periodicidad Días" placeholder="Periodicidad en Días" icon="o-calendar"
                hint="Periodicidad en días" wire:model="servicePeriodicidadDias" />

            <x-textarea label="Observaciones" placeholder="Observaciones..." icon="o-clipboard-document"
                hint="Detalles o notas adicionales" wire:model="serviceObservaciones" rows="3" />

            <x-checkbox label="Notificar" wire:model="serviceNotificar" />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.edit_service_modal = false" />
                <x-button label="Actualizar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
