<?php

namespace App\Livewire\Servicios\V1;

use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Servicio;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, Toast;

    // Búsqueda y modales
    public $search = '';
    public bool $create_service_modal = false;
    public bool $edit_service_modal = false;

    // Campos para crear/editar
    public ?int $editing_service_id = null;
    public string $serviceNombre = '';
    public string $servicePeriodicidadKm = '';
    public string $servicePeriodicidadDias = '';
    public string $serviceObservaciones = '';
    public bool $serviceNotificar = false;

    public bool $continuarCreando = false;

    // Cabeceras de la tabla
    public array $headers = [
        ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
        ['key' => 'nombre', 'label' => 'Nombre', 'class' => 'w-1'],
        ['key' => 'periodicidad_km', 'label' => 'Periodicidad KM', 'class' => 'text-black dark:text-white'],
        ['key' => 'periodicidad_dias', 'label' => 'Periodicidad Dias', 'class' => 'text-black dark:text-white'],
        ['key' => 'observaciones', 'label' => 'Observaciones', 'class' => 'text-black dark:text-white'],
        ['key' => 'notificar', 'label' => 'Notificar', 'class' => 'text-black dark:text-white'],
    ];
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public function mount()
    {
        // Verifica que el servicio tenga permiso de ver servicios
        if (!auth()->user() || !auth()->user()->hasPermissionTo('view_any_servicio')) {
            abort(403);
        }
    }

    /**
     * Abre el modal de crear servicio (limpia variables)
     */
    public function openCreateModal()
    {
        $this->reset([
            'serviceNombre',
            'servicePeriodicidadKm',
            'servicePeriodicidadDias',
            'serviceObservaciones',
            'serviceNotificar',
        ]);
        $this->create_service_modal = true;
    }

    /**
     * Crea un nuevo servicio con roles y/o permisos
     */
    public function createServicio()
    {
        $this->validate([
            'serviceNombre'     => 'required|string',
            'servicePeriodicidadKm'     => 'required|string',
            'servicePeriodicidadDias'    => 'required|string',
            'serviceObservaciones' => 'nullable|string',
            'serviceNotificar' => 'boolean',
        ]);

        // Creamos el servicio
        $service = Servicio::create([
            'nombre'     => $this->serviceNombre,
            'periodicidad_km'     => $this->servicePeriodicidadKm,
            'periodicidad_dias'    => $this->servicePeriodicidadDias,
            'observaciones'    => $this->serviceObservaciones,
            'notificar'    => $this->serviceNotificar,
        ]);

        if (!$this->continuarCreando) {
            $this->reset([
                'serviceNombre',
                'servicePeriodicidadKm',
                'servicePeriodicidadDias',
                'serviceObservaciones',
                'serviceNotificar',
            ]);
            $this->create_service_modal = false;
        } else {
            $this->reset(['serviceNombre', 'servicePeriodicidadKm', 'servicePeriodicidadDias', 'serviceObservaciones', 'serviceNotificar']);
        }

        $this->success('Servicio creado con éxito!');
    }

    /**
     * Abre el modal de edición de un servicio existente
     */
    public function editServicio(int $serviceId)
    {
        $service = Servicio::findOrFail($serviceId);

        $this->editing_service_id = $service->id;
        $this->serviceNombre  = $service->nombre;
        $this->servicePeriodicidadKm  = $service->periodicidad_km ?? 0;
        $this->servicePeriodicidadDias = $service->periodicidad_dias ?? 0;
        $this->serviceObservaciones = $service->observaciones;
        $this->serviceNotificar = $service->notificar;

        $this->edit_service_modal = true;
    }

    /**
     * Actualiza datos del servicio
     */
    public function updateServicio()
    {
        $this->validate([
            'serviceNombre'     => 'nullable|string',
            'servicePeriodicidadKm'     => 'nullable|string',
            'servicePeriodicidadDias'    => 'nullable|string',
            'serviceObservaciones'    => 'nullable|string',
            'serviceNotificar'    => 'boolean',     
        ]);

        $service = Servicio::findOrFail($this->editing_service_id);

        $data = [
            'nombre'  => $this->serviceNombre,
            'periodicidad_km'  => $this->servicePeriodicidadKm,
            'periodicidad_dias' => $this->servicePeriodicidadDias,
            'observaciones' => $this->serviceObservaciones,         
            'notificar' => $this->serviceNotificar,
        ];

        $service->update($data);

        $this->reset([
            'serviceNombre',
            'servicePeriodicidadKm',
            'servicePeriodicidadDias',
            'serviceObservaciones',
            'serviceNotificar',
        ]);

        $this->edit_service_modal = false;

        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Servicio Actualizado Con Éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    /**
     * Elimina un servicio
     */
    public function deleteServicio(int $serviceId)
    {
        $service = Servicio::findOrFail($serviceId);

        $service->delete();

        $this->toast(
            type: 'success',
            title: 'Eliminado',
            description: 'Servicio Eliminado Con Éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function render()
    {
        $servicios = Servicio::where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);

        return view('livewire.servicios.v1.index', [
            'headers' => $this->headers,
            'sortBy' => $this->sortBy,
            'servicios' => $servicios,
        ]);
    }
}
