<div class="p-6 bg-base-100 shadow rounded-lg">
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <x-header title="Vehiculos" subtitle="Gestión de Vehiculos">
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
    <x-table :headers="$headers" :rows="$vehicles" :sort-by="$sortBy" striped with-pagination>

        @scope('cell_KilometrajeGpswox', $vehiculo)
        {{ $vehiculo->kilometraje_gpswox ? number_format($vehiculo->kilometraje_gpswox, 0, '.', ',') . ' Km' : 'N/A' }}
        @endscope   

        {{-- Sobrescribe las acciones --}}
        @scope('actions', $vehiculo)
            <div class="flex gap-2">
                <x-button icon="o-eye" wire:click="viewVehiculo({{ $vehiculo->id }})" spinner class="btn-sm" />
                <x-button icon="o-pencil" wire:click="editVehiculo({{ $vehiculo->id }})" spinner class="btn-sm" />
                <x-button icon="o-trash" wire:click="deleteVehiculo({{ $vehiculo->id }})" spinner class="btn-sm" />
            </div>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Vacio." />
        </x-slot:empty>
    </x-table>

    {{-- Modal para crear roles --}}
    <x-modal wire:model="create_vehicle_modal" title="Crear Vehiculo" subtitle="Añade un nuevo Vehiculo" separator>
        <x-form wire:submit.prevent="createVehiculo">
            <x-input label="Nombre" placeholder="Nombre del Vehiculo" icon="o-truck" hint="Nombre del Vehiculo"
                wire:model="nombre_unidad" required />
            <x-input label="Placa" wire:model="placa" placeholder="Placa del Vehículo" />
            <x-input label="Tipo/Marca" wire:model="tipo_marca" placeholder="Marca del Vehículo" />
            <x-select label="Operador" wire:model="operador_id" :options="$availableOperators" option-value="id"
                option-label="name" />
            <x-input label="VIN" wire:model="vin" placeholder="VIN del Vehículo" />
            <x-input label="ID GPSWOX" wire:model="gpswox_id" placeholder="ID de GPSWOX" />
            <x-input label="No. Tarjeta de Circulación" wire:model="no_tarjeta_circulacion"
                placeholder="Tarjeta de Circulación" />
            <x-datetime label="Vigencia Tarjeta" wire:model="vigencia_tarjeta" icon="o-calendar" />
            <x-input label="TAG Número" wire:model="tag_numero" placeholder="TAG Número" clearable />
            <x-input label="TAG Gasolina" wire:model="tag_gasolina_id" placeholder="TAG Gasolina" clearable />
            <x-datetime label="Verificación Vencimiento" wire:model="verificacion_vencimiento" icon="o-calendar" />

            <x-slot:actions>
                <x-checkbox label="Continuar" wire:model="continuarCreando" hint="Continuar creando" />
                <x-button label="Cancelar" @click="$wire.create_vehicle_modal = false" />
                <x-button label="Guardar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal para editar vehiculo --}}
    <x-modal wire:model="edit_vehicle_modal" title="Editar Vehiculo" subtitle="Actualiza un Vehiculo existente"
        separator>
        <x-form wire:submit.prevent="updateVehiculo">
            <x-input label="Nombre" placeholder="Nombre del Vehiculo" icon="o-truck" hint="Nombre del Vehiculo"
                wire:model="nombre_unidad" />
            <x-input label="Placa" wire:model="placa" placeholder="Placa del Vehículo" />
            <x-input label="Tipo/Marca" wire:model="tipo_marca" placeholder="Marca del Vehículo" />
            <x-select label="Operador" wire:model="operador_id" :options="$availableOperators" option-value="id"
                option-label="name" />
            <x-input label="VIN" wire:model="vin" placeholder="VIN del Vehículo" />
            <x-input label="ID GPSWOX" wire:model="gpswox_id" placeholder="ID de GPSWOX" />
            <x-input label="No. Tarjeta de Circulación" wire:model="no_tarjeta_circulacion"
                placeholder="Tarjeta de Circulación" />
            <x-datetime label="Vigencia Tarjeta" wire:model="vigencia_tarjeta" icon="o-calendar" />
            <x-input label="TAG Número" wire:model="tag_numero" placeholder="TAG Número" clearable />
            <x-input label="TAG Gasolina" wire:model="tag_gasolina_id" placeholder="TAG Gasolina" clearable />
            <x-datetime label="Verificación Vencimiento" wire:model="verificacion_vencimiento" icon="o-calendar" />
            <x-checkbox label="Obtener datos de GPSWOX" wire:model="get_datos_gpswox" />

            <x-slot:actions>
                <x-button label="Cancelar" @click="$wire.edit_vehicle_modal = false" />
                <x-button label="Actualizar" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
