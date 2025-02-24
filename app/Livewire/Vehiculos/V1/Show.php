<?php

namespace App\Livewire\Vehiculos\V1;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOperatorHistory;
use App\Models\VehicleServiceRecord;
use App\Notifications\ServiceNotification;
use App\Services\HttpRequestService;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast, WithPagination;

    public $selectedTab = 'info-tab';
    public $vehiculo;
    public ?float $odometerValue = null;
    public bool $viewImageModal = false;
    public ?string $viewImageModalUrl = null;
    public $newOperatorId;
    public $vin_file = '';

    protected $paginationTheme = 'tailwind';

    public function openViewImageModal($imageUrl)
    {
        $this->viewImageModalUrl = $imageUrl;
        $this->viewImageModal = true;
    }

    public function closeViewImageModal()
    {
        $this->viewImageModal = false;
        $this->viewImageModalUrl = null;
    }

    public function mount(Vehicle $vehiculo)
    {
        $this->vehiculo = $vehiculo;

        // Obtener datos del GPSWOX
        $response = HttpRequestService::makeRequest('post', HttpRequestService::BASE_GPSWOX_URL . 'get_devices', [
            'user_api_hash' => HttpRequestService::API_GPSWOX_TOKEN,
            'id' => $vehiculo->gpswox_id,
        ]);

        // Filtrar el sensor de odómetro
        $this->odometerValue = collect($response[0]['items'][0]['sensors'] ?? [])
            ->firstWhere('type', 'odometer')['val'] ?? null;
    }

    public function changeOperator()
    {
        if (!auth()->user()->hasRole(['master', 'admin'])) {
            return abort(403);
        }

        // Registrar historial si el operador cambia
        if ($this->vehiculo->operador_id !== $this->newOperatorId) {
            // Cerrar historial anterior si existe
            $currentHistory = VehicleOperatorHistory::where('vehicle_id', $this->vehiculo->id)
                ->whereNull('fecha_liberacion')
                ->first();

            if ($currentHistory) {
                $currentHistory->update(['fecha_liberacion' => now()]);
            }

            // Crear un nuevo registro de historial
            if ($this->newOperatorId) {
                VehicleOperatorHistory::create([
                    'vehicle_id' => $this->vehiculo->id,
                    'operador_id' => $this->newOperatorId,
                    'fecha_asignacion' => now(),
                ]);
            }

            // Actualizar operador en registros de servicio pendientes
            $pendingServices = VehicleServiceRecord::where('vehicle_id', $this->vehiculo->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingServices as $serviceRecord) {
                $serviceRecord->update(['operador_id' => $this->newOperatorId]);

                // Enviar notificación al nuevo operador
                if ($this->newOperatorId) {
                    $newOperator = User::find($this->newOperatorId);
                    if ($newOperator) {
                        $newOperator->notify(new ServiceNotification($this->vehiculo, $serviceRecord->service));
                    }
                }
            }
        }

        $this->vehiculo->update(['operador_id' => $this->newOperatorId]);

        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Operador de Vehículo actualizado con éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function initService(int $serviceId) {
        return redirect()->route('servicios.service', $serviceId);
    }

    public function approveRequest($requestId)
    {
        $request = ServiceRequest::findOrFail($requestId);
        $request->update(['status' => 'accepted']);

        // Crear un nuevo registro en `vehicle_service_records`
        VehicleServiceRecord::create([
            'vehicle_id' => $request->vehicle_id,
            'service_id' => $request->service_id,
            'operador_id' => $request->operador_id,
            'status' => 'pending',
        ]);

        $request->operador->notify(new ServiceNotification($request->vehicle, $request->service));

        $this->toast(
            type: 'success',
            title: 'Aprobada',
            description: 'Solicitud aprobada con éxito.',
            icon: 'o-check',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function rejectRequest($requestId)
    {
        $request = ServiceRequest::findOrFail($requestId);
        $request->update(['status' => 'rejected']);

        $this->toast(
            type: 'error',
            title: 'Rechazada',
            description: 'Solicitud rechazada correctamente.',
            icon: 'o-x-circle',
            css: 'alert-error text-white text-sm',
            timeout: 3000,
        );
    }

    public function render()
    {
        $operators = User::whereHas('roles', fn($q) => $q->whereIn('name', ['operador']))->get();
        $serviceRecords = VehicleServiceRecord::where('vehicle_id', $this->vehiculo->id)->orderBy('id', 'desc')->paginate(10);

        return view('livewire.vehiculos.v1.show', compact('serviceRecords', 'operators'));
    }
}
