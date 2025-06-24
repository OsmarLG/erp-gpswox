<?php

namespace App\Livewire\Servicios\V1;

use App\Models\ServiceRequest;
use App\Models\Servicio;
use App\Models\Vehicle;
use App\Models\VehicleServiceRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast;

    public $selectedService;
    public $notas_operador;
    public $vehiculo;
    public $requests;
    public array $availableServices = [];

    public function mount()
    {
        $this->vehiculo = Vehicle::where('operador_id', Auth::id())->first();

        if (!$this->vehiculo) {
            abort(403, 'No tienes un vehículo asignado.');
        }

        // Cargar solicitudes del operador
        $this->requests = ServiceRequest::where('operador_id', Auth::id())
            ->where('vehicle_id', $this->vehiculo->id)
            ->with('service')
            ->get();

        $servicios = Servicio::pluck('nombre', 'id');

        // Mapea a algo como: [ ['id' => 1, 'name' => 'admin'], ... ]
        $this->availableServices = collect($servicios)->map(function ($servicioName, $servicioId) {
            return [
                'id'   => $servicioId,
                'name' => $servicioName
            ];
        })->values()->toArray();
    }

    public function submitRequest()
    {
        $this->validate([
            'selectedService' => 'required|exists:servicios,id',
        ]);

        if (ServiceRequest::where('vehicle_id', $this->vehiculo->id)->where('service_id', $this->selectedService)->where('status', ['pending'])->exists()) {
            $this->toast(
                type: 'error',
                title: 'Error',
                description: 'Ya tienes una solicitud pendiente para este servicio.',
                icon: 'o-x-circle',
                css: 'alert-error text-white text-sm',
                timeout: 3000,
            );
            return;
        }

        if (VehicleServiceRecord::where('vehicle_id', $this->vehiculo->id)->where('service_id', $this->selectedService)->where('status', ['pending', 'initiated'])->exists()) {
            $this->toast(
                type: 'error',
                title: 'Error',
                description: 'Ya tienes un registro pendiente para este servicio.',
                icon: 'o-x-circle',
                css: 'alert-error text-white text-sm',
                timeout: 3000,
            );
            return;
        }

        ServiceRequest::create([
            'vehicle_id' => $this->vehiculo->id,
            'service_id' => $this->selectedService,
            'operador_id' => Auth::id(),
            'notas_operador' => $this->notas_operador,
            'notas_admin' => '',
            'status' => 'pending',
        ]);

        $this->requests = ServiceRequest::where('operador_id', Auth::id())->where('vehicle_id', $this->vehiculo->id)->orderBy('id', 'desc')->get();

        $this->toast(
            type: 'success',
            title: 'Solicitud Enviada',
            description: 'Tu solicitud de servicio ha sido enviada correctamente.',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );

        $this->reset('selectedService', 'notas_operador');
    }

    public function render()
    {
        return view('livewire.servicios.v1.create', [
            'requests' => $this->requests,
        ]);
    }
}
