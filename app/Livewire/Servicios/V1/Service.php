<?php

namespace App\Livewire\Servicios\V1;

use App\Models\VehicleServiceRecord;
use Livewire\Component;
use App\Models\VehicleServiceRecordDetail;
use App\Models\VehicleServiceRecordDetailArchivo;
use App\Models\File;
use App\Models\VehicleServiceKilometer;
use App\Services\HttpRequestService;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

class Service extends Component
{
    use WithFileUploads;
    use Toast;

    public $idServicio;
    public $servicio;
    public $servicioAnterior;
    public $servicioSiguiente;
    public $detalle;
    public $files;

    public function mount($servicio)
    {
        $this->idServicio = $servicio;
        $this->servicio = VehicleServiceRecord::findOrFail($servicio);

        // Servicio Anterior
        $this->servicioAnterior = VehicleServiceRecord::query()
            ->where('vehicle_id', $this->servicio->vehicle_id)
            ->where('service_id', $this->servicio->service_id)
            ->where('status', 'completed')
            ->where('id', '<', $this->servicio->id)
            ->orderBy('id', 'desc')
            ->first();


        // // Servicio Siguiente
        $this->servicioSiguiente = VehicleServiceRecord::query()
            ->where('vehicle_id', $this->servicio->vehicle_id)
            ->where('service_id', $this->servicio->service_id)
            ->where('status', 'completed')
            ->where('id', '>', $this->servicio->id)
            ->orderBy('id', 'asc')
            ->first();

        // $ultimoServicioTabla = VehicleServiceRecord::where('status', 'completed')
        //     ->orderBy('created_at', 'desc')
        //     ->first();

        // dd($this->idServicio, $this->servicio, $this->servicioAnterior, $ultimoServicioTabla);

        $usuario = Auth::user();

        // Si el usuario es admin o master, puede ver cualquier servicio
        if ($usuario->hasRole(['master', 'admin'])) {
            return view('servicios.service', ['servicio' => $servicio]);
        }

        // Si el usuario es operador, solo puede ver si el vehículo está asignado a él
        if ($usuario->hasRole('operador') && $this->servicio->vehicle->operador_id === $usuario->id) {
            return view('servicios.service', ['servicio' => $servicio]);
        }

        // Si no tiene permiso, lo redirigimos con un mensaje de error
        abort(403, 'No tienes permiso para ver este servicio.');
    }

    public function agregarDetalle()
    {
        $this->validate([
            'detalle' => 'required|string',
            'files' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:20480',
        ]);

        // Verificar si es el primer detalle agregado y cambiar el estado
        if ($this->servicio->detalles()->count() === 0) {
            $this->servicio->update(['status' => 'initiated']);
        }

        // Crear detalle del servicio
        $detalle = VehicleServiceRecordDetail::create([
            'vehicle_service_record_id' => $this->servicio->id,
            'operador_id' => Auth::id(),
            'detalle' => $this->detalle,
        ]);

        if ($this->files) {
            $file = $this->files;
            $path = $file->store('service_archivos', 'public');
            VehicleServiceRecordDetailArchivo::create([
                'path' => $path,
                'description' => $this->detalle,
                'vehicle_service_record_detail_id' => $detalle->id,
                'operador_id' => Auth::id(),
                'type' => $file->getClientOriginalExtension(),
            ]);
        }

        // Resetear campos después de guardar
        $this->reset('detalle', 'files');
        $this->servicio->refresh();
    }

    public function finalizarServicio()
    {
        if (Auth::user()->hasRole(['master', 'admin'])) {

            // Obtener datos del GPSWOX
            $response = HttpRequestService::makeRequest('post', HttpRequestService::BASE_GPSWOX_URL . 'get_devices', [
                'user_api_hash' => HttpRequestService::API_GPSWOX_TOKEN,
                'id' => $this->servicio->vehicle->gpswox_id,
            ]);

            // Filtrar el sensor de odómetro
            $odometerValue = collect($response[0]['items'][0]['sensors'] ?? [])
                ->firstWhere('type', 'odometer')['val'] ?? null;

            $this->servicio->update(['status' => 'completed', 'fecha_realizacion' => now(), 'valor_kilometraje' => $odometerValue]);

            VehicleServiceKilometer::updateOrCreate([
                'vehicle_id' => $this->servicio->vehicle_id,
                'service_id' => $this->servicio->service_id,
                'last_km' => $this->servicio->valor_kilometraje,
                'current_km' => $odometerValue,
            ]);
        }

        $this->success('Servicio finalizado con éxito!');
    }

    public function cambiarAInitiated()
    {
        if (Auth::user()->hasRole(['master', 'admin'])) {
            $this->servicio->update(['status' => 'initiated', 'fecha_realizacion' => null, 'valor_kilometraje' => null]);
            $this->success('Servicio reabierto correctamente.');
        }
    }

    public function eliminarDetalle($detalleId)
    {
        $detalle = VehicleServiceRecordDetail::findOrFail($detalleId);
        $isOperador = $detalle->operador_id == Auth::id();

        if (Auth::user()->hasRole(['master', 'admin']) || $isOperador) {

            // Eliminar archivos relacionados
            VehicleServiceRecordDetailArchivo::where('vehicle_service_record_detail_id', $detalleId)
                ->delete();

            // Eliminar el detalle
            $detalle->delete();

            // Refrescar la vista
            $this->servicio->refresh();
        }
    }

    public function render()
    {
        // Agregar imágenes y videos a cada detalle del servicio
        foreach ($this->servicio->detalles as $detalle) {
            // Obtener imágenes
            $detalle->imagenes = VehicleServiceRecordDetailArchivo::where('vehicle_service_record_detail_id', $detalle->id)
                ->whereIn('type', ['jpg', 'jpeg', 'png']) // Solo imágenes
                ->pluck('path')
                ->map(fn($path) => asset('storage/' . $path))
                ->toArray();

            // Obtener videos
            $detalle->videos = VehicleServiceRecordDetailArchivo::where('vehicle_service_record_detail_id', $detalle->id)
                ->whereIn('type', ['mp4']) // Solo videos
                ->pluck('path')
                ->map(fn($path) => asset('storage/' . $path))
                ->toArray();
        }

        return view('livewire.servicios.v1.service', [
            'servicio' => $this->servicio,
            'detalles' => $this->servicio->detalles,
        ]);
    }
}
