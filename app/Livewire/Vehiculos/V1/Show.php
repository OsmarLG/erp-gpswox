<?php

namespace App\Livewire\Vehiculos\V1;

use App\Models\Parte;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOperatorHistory;
use App\Models\VehiclePart;
use App\Models\VehicleRequest;
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

    public $selectedField = '';
    public $selectedPart = '';

    public $vehicleFields = [
        'placa',
        'tipo_marca',
        'nombre_unidad',
        'operador_id',
        'vin',
        'no_tarjeta_circulacion',
        'vigencia_tarjeta',
        'tag_numero',
        'tag_gasolina_id',
        'verificacion_vencimiento',
        'fecha_bateria',
        'rines_medida',
        'medida_llantas',
        'poliza_no',
        'compania_seguros',
        'poliza_vigencia',
        'costo_poliza',
        'pago',
        'telefono_seguro',
        'gpswox_id',
        'id_gps1',
        'tel_gps1',
        'imei_gps1',
        'vigencia_gps1',
        'saldo_gps1',
        'id_gps2',
        'tel_gps2',
        'imei_gps2',
        'vigencia_gps2',
        'saldo_gps2',
    ];

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
            
            // Actualizar operador en registros de solicitud pendientes
            $pendingRequests = VehicleRequest::where('vehicle_id', $this->vehiculo->id)
                ->whereIn('status', ['pending', 'initiated', 'finished'])
                ->get();

            foreach ($pendingRequests as $request) {
                $request->update(['operador_id' => $this->newOperatorId]);

                // Enviar notificación al nuevo operador
                if ($this->newOperatorId) {
                    $newOperator = User::find($this->newOperatorId);
                    if ($newOperator) {
                        // $newOperator->notify(new RequestNotification($this->vehiculo, $request->type));
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

    public function initService(int $serviceId)
    {
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

    public function requestModification($type, $value = null)
    {
        if ($type === 'field') {
            if (VehicleRequest::where('vehicle_id', $this->vehiculo->id)
                ->where('type', 'field')
                ->where('field', $this->selectedField)
                ->where('status', 'pending')
                ->exists()
            ) {
                $this->toast('warning', 'Solicitud Existente', 'Ya existe una solicitud pendiente para este campo.');
                return;
            }

            VehicleRequest::create([
                'vehicle_id' => $this->vehiculo->id,
                'field' => $this->selectedField,
                'operador_id' => $this->vehiculo->operador_id,
                'status' => 'pending',
                'type' => 'field',
            ]);
        } elseif ($type === 'part') {
            if (VehicleRequest::where('vehicle_id', $this->vehiculo->id)
                ->where('type', 'part')
                ->where('parte_id', $value)
                ->where('status', 'pending')
                ->exists()
            ) {
                $this->toast('warning', 'Solicitud Existente', 'Ya existe una solicitud pendiente para esta parte.');
                return;
            }

            VehicleRequest::create([
                'vehicle_id' => $this->vehiculo->id,
                'parte_id' => $value,
                'operador_id' => $this->vehiculo->operador_id,
                'status' => 'pending',
                'type' => 'part',
            ]);
        }

        $this->toast('success', 'Solicitud Enviada', 'Se ha solicitado la modificación.');
    }

    public function showRequest($requestId)
    {
        $request = VehicleRequest::find($requestId);

        if (!$request) {
            $this->toast(
                type: 'error',
                title: 'No encontrado',
                description: 'Solicitud no encontrada.',
                icon: 'o-x-circle',
                css: 'alert-error text-white text-sm',
                timeout: 3000
            );
        }

        return redirect()->route('servicios.request', $requestId);
    }

    public function deleteRequest($requestId)
    {
        $request = VehicleRequest::find($requestId);

        if ($request) {
            $request->delete();

            $this->toast(
                type: 'success',
                title: 'Eliminado',
                description: 'Solicitud eliminada correctamente.',
                icon: 'o-trash',
                css: 'alert-success text-white text-sm',
                timeout: 3000
            );
        } else {
            $this->toast(
                type: 'error',
                title: 'No encontrado',
                description: 'Solicitud no encontrada.',
                icon: 'o-x-circle',
                css: 'alert-error text-white text-sm',
                timeout: 3000
            );
        }
    }

    public function render()
    {
        $operators = User::whereHas('roles', fn($q) => $q->whereIn('name', ['operador']))->get();
        $serviceRecords = VehicleServiceRecord::where('vehicle_id', $this->vehiculo->id)->orderBy('id', 'desc')->paginate(10);
        $parts = Parte::with(['categoria', 'files', 'vehicleRequests'])
            ->orderBy('categoria_id') // Ordena por categoría
            ->get();

        $requests = VehicleRequest::where('vehicle_id', $this->vehiculo->id)->whereIn('status', ['pending', 'initiated'])->get();

        return view('livewire.vehiculos.v1.show', compact('serviceRecords', 'operators', 'parts', 'requests'));
    }
}
