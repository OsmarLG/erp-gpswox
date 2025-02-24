<?php

namespace App\Livewire\Servicios\V1;

use Livewire\Component;
use App\Models\ServiceRequest;
use App\Models\Servicio;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast;

    public $selectedService;
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

        ServiceRequest::create([
            'vehicle_id' => $this->vehiculo->id,
            'service_id' => $this->selectedService,
            'operador_id' => Auth::id(),
            'status' => 'pending',
        ]);

        $this->requests = ServiceRequest::where('operador_id', Auth::id())->where('vehicle_id', $this->vehiculo->id)->get();

        $this->toast(
            type: 'success',
            title: 'Solicitud Enviada',
            description: 'Tu solicitud de servicio ha sido enviada correctamente.',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );

        $this->reset('selectedService');
    }

    public function render()
    {
        return view('livewire.servicios.v1.create', [
            'requests' => $this->requests,
        ]);
    }
}
