<?php

namespace App\Livewire\Vehiculos\V1;

use App\Models\File;
use App\Models\Parte;
use App\Models\ServiceRequest;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOperatorHistory;
use App\Models\VehiclePart;
use App\Models\VehicleRequest;
use App\Models\VehicleServiceKilometer;
use App\Models\VehicleServiceRecord;
use App\Models\VehicleServiceRecordDetail;
use App\Notifications\ServiceNotification;
use App\Services\HttpRequestService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\VehicleRequestArchivo;

class Show extends Component
{
    use Toast, WithPagination, WithFileUploads;

    public $selectedTab = 'info-tab';
    public $vehiculo;
    public ?float $odometerValue = null;
    public bool $viewImageModal = false;
    public ?string $viewImageModalUrl = null;
    public $newOperatorId;
    public $vin_file = '';
    public $service_id, $last_km, $fecha_realizacion_servicio, $availableServices;

    protected $paginationTheme = 'tailwind';

    public $selectedField = '';
    public $selectedPart = '';
    public $files;

    public $nombre_unidad;
    public $placa;
    public $tipo_marca;
    public $vin;
    public $telefono_seguro;
    public $get_datos_gpswox;
    public $no_tarjeta_circulacion;
    public $vigencia_tarjeta;
    public $tag_numero;
    public $tag_gasolina_id;
    public $verificacion_vencimiento;
    public $fecha_bateria;
    public $rines_medida;
    public $medida_llantas;
    public $poliza_no;
    public $compania_seguros;
    public $poliza_vigencia;
    public $costo_poliza;
    public $pago;
    public $gpswox_id;
    public $id_gps1;
    public $tel_gps1;
    public $imei_gps1;
    public $vigencia_gps1;
    public $saldo_gps1;
    public $id_gps2;
    public $tel_gps2;
    public $imei_gps2;
    public $vigencia_gps2;
    public $saldo_gps2;
    public $asegurado;
    public $comentarios_seguro;
    public $tarjeta_estado;
    public $nombre_tarjeta;
    public $marca_bateria;
    public $comentarios_gps;

    public $vehicleFields = [
        'nombre_unidad',
        'placa',
        'tipo_marca',
        'vin',
        'telefono_seguro',
        'get_datos_gpswox',
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
        'asegurado',
        'comentarios_seguro',
        'tarjeta_estado',
        'nombre_tarjeta',
        'marca_bateria',
        'comentarios_gps',
    ];

    public $notas_operador;
    public $notas_admin;

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

        $this->nombre_unidad = $vehiculo->nombre_unidad;
        $this->placa = $vehiculo->placa;
        $this->tipo_marca = $vehiculo->tipo_marca;
        $this->vin = $vehiculo->vin;
        $this->telefono_seguro = $vehiculo->telefono_seguro;
        $this->get_datos_gpswox = (bool) $vehiculo->get_datos_gpswox;
        $this->no_tarjeta_circulacion = $vehiculo->no_tarjeta_circulacion;
        $this->vigencia_tarjeta = $vehiculo->vigencia_tarjeta;
        $this->tag_numero = $vehiculo->tag_numero;
        $this->tag_gasolina_id = $vehiculo->tag_gasolina_id;
        $this->verificacion_vencimiento = $vehiculo->verificacion_vencimiento;
        $this->fecha_bateria = $vehiculo->fecha_bateria;
        $this->rines_medida = $vehiculo->rines_medida;
        $this->medida_llantas = $vehiculo->medida_llantas;
        $this->poliza_no = $vehiculo->poliza_no;
        $this->compania_seguros = $vehiculo->compania_seguros;
        $this->poliza_vigencia = $vehiculo->poliza_vigencia;
        $this->costo_poliza = $vehiculo->costo_poliza;
        $this->pago = $vehiculo->pago;
        $this->gpswox_id = $vehiculo->gpswox_id;
        $this->id_gps1 = $vehiculo->id_gps1;
        $this->tel_gps1 = $vehiculo->tel_gps1;
        $this->imei_gps1 = $vehiculo->imei_gps1;
        $this->vigencia_gps1 = $vehiculo->vigencia_gps1;
        $this->saldo_gps1 = $vehiculo->saldo_gps1;
        $this->id_gps2 = $vehiculo->id_gps2;
        $this->tel_gps2 = $vehiculo->tel_gps2;
        $this->imei_gps2 = $vehiculo->imei_gps2;
        $this->vigencia_gps2 = $vehiculo->vigencia_gps2;
        $this->saldo_gps2 = $vehiculo->saldo_gps2;
        $this->asegurado = $vehiculo->asegurado;
        $this->comentarios_seguro = $vehiculo->comentarios_seguro;
        $this->tarjeta_estado = $vehiculo->tarjeta_estado;
        $this->nombre_tarjeta = $vehiculo->nombre_tarjeta;
        $this->marca_bateria = $vehiculo->marca_bateria;
        $this->comentarios_gps = $vehiculo->comentarios_gps;

        foreach ($this->vehicleFields as $field) {
            $this->{$field} = $vehiculo->{$field};
        }

        // Obtener datos del GPSWOX
        $response = HttpRequestService::makeRequest('post', HttpRequestService::BASE_GPSWOX_URL . 'get_devices', [
            'user_api_hash' => HttpRequestService::API_GPSWOX_TOKEN,
            'id' => $vehiculo->gpswox_id,
        ]);

        // Filtrar el sensor de odómetro
        $this->odometerValue = collect($response[0]['items'][0]['sensors'] ?? [])
            ->firstWhere('type', 'odometer')['val'] ?? null;

        // Solo si hay odómetro válido
        if (!is_null($this->odometerValue)) {
            $todosLosServicios = Servicio::all();

            foreach ($todosLosServicios as $servicio) {
                $vehicleServiceKilometer = VehicleServiceKilometer::where('vehicle_id', $vehiculo->id)
                    ->where('service_id', $servicio->id)
                    ->first();

                if ($vehicleServiceKilometer) {
                    // Solo actualizar current_km
                    $vehicleServiceKilometer->update([
                        'current_km' => $this->odometerValue,
                    ]);
                } else {
                    // Crear registro nuevo
                    VehicleServiceKilometer::create([
                        'vehicle_id' => $vehiculo->id,
                        'service_id' => $servicio->id,
                        'last_km' => $this->odometerValue,
                        'current_km' => $this->odometerValue,
                    ]);
                }
            }
        }

        $this->availableServices = Servicio::all();
    }

    public function saveVehicleFields()
    {
        if (!auth()->user()->hasRole(['admin', 'master'])) {
            abort(403);
        }

        $data = [];

        foreach ($this->vehicleFields as $field) {
            $value = $this->{$field};

            // Si el campo es saldo_gps1 o saldo_gps2 y viene vacío, cámbialo a null o 0.0
            if (in_array($field, ['saldo_gps1', 'saldo_gps2', 'tel_gps1', 'tel_gps2', 'imei_gps1', 'imei_gps2', 'vigencia_gps1', 'vigencia_gps2']) && $value === '') {
                $data[$field] = null; // o 0.0 si prefieres guardar ceros
            } else {
                $data[$field] = $value;
            }
        }

        $this->vehiculo->update($data);

        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Los campos del vehículo fueron actualizados.',
            icon: 'o-check-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function uploadEvidence($partId)
    {
        if (!auth()->user()->hasRole(['master', 'admin'])) {
            return abort(403);
        }

        if (!$this->files) {
            $this->toast('error', 'Error', 'Debes seleccionar un archivo.');
            return;
        }

        $filePath = $this->files->store('vehicle_request_archivos', 'public');

        $request = VehicleRequest::create([
            'vehicle_id' => $this->vehiculo->id,
            'parte_id' => $partId,
            'operador_id' => $this->vehiculo->operador_id ?? auth()->user()->id,
            'type' => 'part',
            'status' => 'finished',
        ]);

        VehicleRequestArchivo::create([
            'path' => $filePath,
            'description' => 'Evidencia subida por ' . auth()->user()->name,
            'vehicle_request_id' => $request->id,
            'operador_id' => $this->vehiculo->operador_id ?? auth()->user()->id,
            'vehicle_id' => $this->vehiculo->id,
            'parte_id' => $partId,
            'type' => $this->files->getClientOriginalExtension(),
        ]);

        $this->reset('files');

        $this->toast('success', 'Evidencia subida', 'El archivo fue guardado correctamente.');
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
                    if ($newOperator && $serviceRecord->service && $serviceRecord->service->notificar == true) {
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
                    if ($newOperator && $request->type == 'part' && $request->service && $request->service->notificar == true) {
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
        $request->update(['status' => 'accepted', 'notas_admin' => $this->notas_admin]);

        // Crear un nuevo registro en `vehicle_service_records`
        VehicleServiceRecord::create([
            'vehicle_id' => $request->vehicle_id,
            'service_id' => $request->service_id,
            'operador_id' => $request->operador_id,
            'status' => 'pending',
            'solicitud_id' => $request->id,
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
        $request->update(['status' => 'rejected', 'notas_admin' => $this->notas_admin]);

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
                'notas_admin' => $this->notas_admin,
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
                'notas_admin' => $this->notas_admin,
            ]);
        }

        $this->reset('notas_admin');
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
        // $parts = Parte::with(['categoria', 'files', 'vehicleRequests', 'archivos'])
        //     ->orderBy('categoria_id')
        //     ->get();

        $parts = Parte::with(['categoria', 'vehicleRequests'])
            ->get()
            ->each(function ($parte) {
                $parte->setRelation('archivos_filtrados', $parte->archivosDelVehiculo($this->vehiculo->id)->get());
            });

        $requests = VehicleRequest::where('vehicle_id', $this->vehiculo->id)
            ->whereIn('status', ['pending', 'initiated'])
            ->get();

        return view('livewire.vehiculos.v1.show', compact(
            'serviceRecords',
            'operators',
            'parts',
            'requests'
        ));
    }


    public function storeServiceRecord()
    {
        $this->validate([
            'service_id' => 'required|exists:servicios,id',
            'last_km' => 'required|numeric|min:0',
            'fecha_realizacion_servicio' => 'required|date',
        ]);

        // Obtener datos del GPSWOX
        $response = HttpRequestService::makeRequest('post', HttpRequestService::BASE_GPSWOX_URL . 'get_devices', [
            'user_api_hash' => HttpRequestService::API_GPSWOX_TOKEN,
            'id' => $this->vehiculo->gpswox_id,
        ]);

        // Filtrar el sensor de odómetro
        $current_odometerValue = collect($response[0]['items'][0]['sensors'] ?? [])
            ->firstWhere('type', 'odometer')['val'] ?? null;

        $record = VehicleServiceRecord::create([
            'vehicle_id' => $this->vehiculo->id,
            'service_id' => $this->service_id,
            'operador_id' => $this->vehiculo->operador_id ?? auth()->id(),
            'status' => 'completed',
            'fecha_realizacion' => $this->fecha_realizacion_servicio,
            'valor_kilometraje' => $this->last_km,
        ]);

        VehicleServiceKilometer::updateOrCreate([
            'vehicle_id' => $this->vehiculo->id,
            'service_id' => $this->service_id,
        ], [
            'last_km' => $this->last_km,
            'current_km' => $current_odometerValue,
        ]);

        VehicleServiceRecordDetail::create([
            'vehicle_service_record_id' => $record->id,
            'operador_id' => $this->vehiculo->operador_id ?? auth()->id(),
            'detalle' => 'Servicio registrado',
        ]);

        $this->reset(['service_id', 'last_km', 'fecha_realizacion_servicio']);

        $this->toast('success', 'Servicio registrado', 'El servicio fue guardado correctamente.');
    }

    public function storeSolicitudServiceRecord()
    {

        if (!$this->vehiculo->operador_id) {
            $this->toast('error', 'Error', 'El vehículo no tiene operador asignado.');
            return;
        }

        if (VehicleServiceRecord::where('vehicle_id', $this->vehiculo->id)->where('service_id', $this->service_id)->where('status', ['pending', 'initiated'])->exists()) {
            $this->toast('error', 'Error', 'Ya existe una solicitud para este servicio.');
            return;
        }

        $this->validate([
            'service_id' => 'required|exists:servicios,id',
            'notas_admin' => 'nullable|string',
        ]);

        $record = VehicleServiceRecord::create([
            'vehicle_id' => $this->vehiculo->id,
            'service_id' => $this->service_id,
            'operador_id' => $this->vehiculo->operador_id ?? auth()->id(),
            'status' => 'pending',
        ]);

        if ($this->notas_admin) {
            VehicleServiceRecordDetail::create([
                'vehicle_service_record_id' => $record->id,
                'operador_id' => $this->vehiculo->operador_id ?? auth()->id(),
                'detalle' => 'Solicitud de servicio, notas: ' . $this->notas_admin,
            ]);
        }

        $service = Servicio::find($this->service_id);

        $this->vehiculo->operador->notify(new ServiceNotification($this->vehiculo, $service));
        $this->reset(['service_id', 'notas_admin']);

        $this->toast('success', 'Solicitud enviada', 'La solicitud fue enviada correctamente.');
    }
}
