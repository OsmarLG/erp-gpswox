<?php

namespace App\Livewire\Vehiculos\V1;

use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VehicleServiceRecord;
use App\Models\VehicleOperatorHistory;
use App\Notifications\ServiceNotification;

class Index extends Component
{
    use WithPagination, Toast;

    // Búsqueda y modales
    public $search = '';
    public bool $create_vehicle_modal = false;
    public bool $edit_vehicle_modal = false;

    // Campos para crear/editar
    public ?int $editing_vehicle_id = null;
    public string $nombre_unidad = '';
    public string $placa = '';
    public string $tipo_marca = '';
    public ?int $operador_id = null;
    public string $vin = '';
    public string $gpswox_id = '';
    public $no_tarjeta_circulacion = '';
    public $vigencia_tarjeta = '';
    public $tag_numero = '';
    public $tag_gasolina_id = '';
    public $verificacion_vencimiento = '';
    public bool $get_datos_gpswox = false;

    // Listado de operadores disponibles
    public array $availableOperators = [];

    public bool $continuarCreando = false;

    // Cabeceras de la tabla
    public array $headers = [
        ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
        ['key' => 'nombre_unidad', 'label' => 'Nombre Unidad', 'class' => 'text-black dark:text-white'],
        ['key' => 'placa', 'label' => 'Placa', 'class' => 'text-black dark:text-white'],
        ['key' => 'tipo_marca', 'label' => 'Tipo/Marca', 'class' => 'text-black dark:text-white'],
        ['key' => 'operador.name', 'label' => 'Operador', 'class' => 'text-black dark:text-white'],
        ['key' => 'gpswox_id', 'label' => 'ID GPSWOX', 'class' => 'text-black dark:text-white'],
    ];
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public function mount()
    {
        // Verifica que el usuario tenga permiso de ver vehículos
        if (!auth()->user() || !auth()->user()->hasPermissionTo('view_any_vehicle')) {
            abort(403);
        }

        // Carga operadores disponibles
        $this->availableOperators = collect([['id' => null, 'name' => '--- Selecciona un operador ---']])
            ->merge(
                User::role('operador')
                    ->pluck('name', 'id')
                    ->map(fn($name, $id) => ['id' => $id, 'name' => $name])
                    ->values()
            )
            ->toArray();
    }

    public function openCreateModal()
    {
        $this->reset([
            'nombre_unidad',
            'placa',
            'tipo_marca',
            'operador_id',
            'vin',
            'gpswox_id',
            'no_tarjeta_circulacion',
            'vigencia_tarjeta',
            'tag_numero',
            'tag_gasolina_id',
            'verificacion_vencimiento',
        ]);
        $this->create_vehicle_modal = true;
    }

    public function createVehiculo()
    {
        $this->validate([
            'nombre_unidad' => 'required|string',
            'placa' => 'nullable|string',
            'tipo_marca' => 'nullable|string',
            'operador_id' => 'nullable|exists:users,id',
            'vin' => 'nullable|string',
            'gpswox_id' => 'nullable|string',
            'no_tarjeta_circulacion' => 'nullable|string',
            'vigencia_tarjeta' => 'nullable|string',
            'tag_numero' => 'nullable|string',
            'tag_gasolina_id' => 'nullable|string',
            'verificacion_vencimiento' => 'nullable|string',
        ]);

        // Creamos el vehículo
        $vehicle = Vehicle::create([
            'nombre_unidad' => $this->nombre_unidad,
            'placa' => $this->placa,
            'tipo_marca' => $this->tipo_marca,
            'operador_id' => $this->operador_id,
            'vin' => $this->vin,
            'gpswox_id' => $this->gpswox_id,
            'no_tarjeta_circulacion' => $this->no_tarjeta_circulacion,
            'vigencia_tarjeta' => $this->vigencia_tarjeta ?: null,
            'tag_numero' => $this->tag_numero,
            'tag_gasolina_id' => $this->tag_gasolina_id,
            'verificacion_vencimiento' => $this->verificacion_vencimiento ?: null,
        ]);

        // Crear el registro en el historial si se asigna un operador al crear el vehículo
        if ($this->operador_id) {
            VehicleOperatorHistory::create([
                'vehicle_id' => $vehicle->id,
                'operador_id' => $this->operador_id,
                'fecha_asignacion' => now(),
            ]);
        }

        if (!$this->continuarCreando) {
            $this->reset([
                'nombre_unidad',
                'placa',
                'tipo_marca',
                'operador_id',
                'vin',
                'gpswox_id',
                'no_tarjeta_circulacion',
                'vigencia_tarjeta',
                'tag_numero',
                'tag_gasolina_id',
                'verificacion_vencimiento',
            ]);
            $this->create_vehicle_modal = false;
        } else {
            $this->reset([
                'nombre_unidad',
                'placa',
                'tipo_marca',
                'operador_id',
                'vin',
                'gpswox_id',
                'no_tarjeta_circulacion',
                'vigencia_tarjeta',
                'tag_numero',
                'tag_gasolina_id',
                'verificacion_vencimiento'
            ]);
        }

        $this->success('Vehículo creado con éxito!');
    }

    public function editVehiculo(int $vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $this->editing_vehicle_id = $vehicle->id;
        $this->nombre_unidad = $vehicle->nombre_unidad;
        $this->placa = $vehicle->placa;
        $this->tipo_marca = $vehicle->tipo_marca;
        $this->operador_id = $vehicle->operador_id;
        $this->vin = $vehicle->vin;
        $this->gpswox_id = $vehicle->gpswox_id;
        $this->no_tarjeta_circulacion = $vehicle->no_tarjeta_circulacion;
        $this->vigencia_tarjeta = $vehicle->vigencia_tarjeta;
        $this->tag_numero = $vehicle->tag_numero;
        $this->tag_gasolina_id = $vehicle->tag_gasolina_id;
        $this->verificacion_vencimiento = $vehicle->verificacion_vencimiento;
        $this->get_datos_gpswox = $vehicle->get_datos_gpswox;

        $this->edit_vehicle_modal = true;
    }

    public function updateVehiculo()
    {
        $this->validate([
            'nombre_unidad' => 'nullable|string',
            'placa' => 'nullable|string',
            'tipo_marca' => 'nullable|string',
            'operador_id' => 'nullable|exists:users,id',
            'vin' => 'nullable|string',
            'gpswox_id' => 'nullable|string',
            'no_tarjeta_circulacion' => 'nullable|string',
            'vigencia_tarjeta' => 'nullable|string',
            'tag_numero' => 'nullable|string',
            'tag_gasolina_id' => 'nullable|string',
            'verificacion_vencimiento' => 'nullable|string',
            'get_datos_gpswox' => 'nullable|boolean',
        ]);

        $vehicle = Vehicle::findOrFail($this->editing_vehicle_id);

        // Registrar historial si el operador cambia
        if ($vehicle->operador_id !== $this->operador_id) {
            // Cerrar historial anterior si existe
            $currentHistory = VehicleOperatorHistory::where('vehicle_id', $vehicle->id)
                ->whereNull('fecha_liberacion')
                ->first();

            if ($currentHistory) {
                $currentHistory->update(['fecha_liberacion' => now()]);
            }

            // Crear un nuevo registro de historial
            if ($this->operador_id) {
                VehicleOperatorHistory::create([
                    'vehicle_id' => $vehicle->id,
                    'operador_id' => $this->operador_id,
                    'fecha_asignacion' => now(),
                ]);
            }

            // Obtener los registros de servicio pendientes y reasignar operador
            $pendingServices = VehicleServiceRecord::where('vehicle_id', $vehicle->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingServices as $serviceRecord) {
                $serviceRecord->update(['operador_id' => $this->operador_id]);

                // Enviar notificación al nuevo operador
                if ($this->operador_id) {
                    $newOperator = User::find($this->operador_id);
                    if ($newOperator) {
                        $newOperator->notify(new ServiceNotification($vehicle, $serviceRecord->service));
                    }
                }
            }
        }

        $vehicle->update([
            'nombre_unidad' => $this->nombre_unidad,
            'placa' => $this->placa,
            'tipo_marca' => $this->tipo_marca,
            'operador_id' => $this->operador_id,
            'vin' => $this->vin,
            'gpswox_id' => $this->gpswox_id,
            'no_tarjeta_circulacion' => $this->no_tarjeta_circulacion,
            'vigencia_tarjeta' => $this->vigencia_tarjeta ?: null,
            'tag_numero' => $this->tag_numero,
            'tag_gasolina_id' => $this->tag_gasolina_id,
            'verificacion_vencimiento' => $this->verificacion_vencimiento ?: null,
            'get_datos_gpswox' => $this->get_datos_gpswox,
        ]);

        $this->reset([
            'nombre_unidad',
            'placa',
            'tipo_marca',
            'operador_id',
            'vin',
            'gpswox_id',
            'no_tarjeta_circulacion',
            'vigencia_tarjeta',
            'tag_numero',
            'tag_gasolina_id',
            'verificacion_vencimiento',
            'get_datos_gpswox',
        ]);

        $this->edit_vehicle_modal = false;

        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Vehículo actualizado con éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function deleteVehiculo(int $vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $vehicle->delete();

        $this->toast(
            type: 'success',
            title: 'Eliminado',
            description: 'Vehículo eliminado con éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    /**
     * Redirecciona a la vista "show" (detalle) del vehiculo
     */
    public function viewVehiculo(string $vehicleId)
    {
        return redirect()->route('vehiculos.show', $vehicleId);
    }

    public function render()
    {
        $vehicles = Vehicle::where('nombre_unidad', 'like', '%' . $this->search . '%')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);

        return view('livewire.vehiculos.v1.index', [
            'headers' => $this->headers,
            'sortBy' => $this->sortBy,
            'vehicles' => $vehicles,
        ]);
    }
}
