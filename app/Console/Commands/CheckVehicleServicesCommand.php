<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use App\Models\Servicio;
use App\Models\VehicleServiceKilometer;
use App\Models\VehicleServiceRecord;
use Illuminate\Support\Facades\Http;
use App\Notifications\ServiceNotification;
use App\Services\HttpRequestService;

class CheckVehicleServicesCommand extends Command
{
    protected $signature = 'services:check';
    protected $description = 'Verifica los servicios de vehículos según kilometraje o periodicidad de días.';

    public function handle()
    {
        // Obtener todos los vehículos que tengan un gpswox_id válido
        $vehicles = Vehicle::whereNotNull('gpswox_id')->where('gpswox_id', '!=', '')
            ->where('get_datos_gpswox', true)->get();

        foreach ($vehicles as $vehicle) {
            // Obtener datos del GPSWOX
            $response = Http::post(HttpRequestService::BASE_GPSWOX_URL . 'get_devices', [
                'user_api_hash' => HttpRequestService::API_GPSWOX_TOKEN,
                'id' => $vehicle->gpswox_id,
            ]);

            if (!$response->successful()) {
                $this->warn("Error en la solicitud para el vehículo {$vehicle->placa}");
                continue;
            }

            $gpsData = $response->json();

            // Filtrar el sensor de odómetro
            $odometerValue = collect($gpsData[0]['items'][0]['sensors'] ?? [])
                ->firstWhere('type', 'odometer')['val'] ?? null;

            if (!$odometerValue) {
                $this->warn("No se pudo obtener el odómetro para el vehículo {$vehicle->placa}");
                continue;
            }

            // Verificar los servicios del vehículo
            $services = Servicio::all();

            foreach ($services as $service) {
                // Crear o actualizar el registro de kilometraje del servicio
                $record = VehicleServiceKilometer::firstOrCreate(
                    ['vehicle_id' => $vehicle->id, 'service_id' => $service->id],
                    ['current_km' => $odometerValue]
                );

                if ($record->exists) {
                    $record->update(['current_km' => $odometerValue]);
                }

                $serviceRecordCreated = false;

                // Verificar periodicidad por kilometraje
                if ($service->periodicidad_km !== null) {
                    $km_difference = $record->current_km - $record->last_km;

                    if ($km_difference >= $service->periodicidad_km) {
                        $this->info('yes');
                        $this->createServiceRecord($vehicle, $service);
                        $record->update(['last_km' => $record->current_km]); // Actualizar `last_km`
                        $serviceRecordCreated = true;
                    }
                }

                // Verificar periodicidad por días solo si no se ha creado ya un registro y si tiene periodicidad por días
                if (!$serviceRecordCreated && $service->periodicidad_dias !== null) {
                    $lastRecord = VehicleServiceRecord::where('vehicle_id', $vehicle->id)
                        ->where('service_id', $service->id)
                        // ->where('status', 'finished')
                        ->latest('created_at')
                        ->first();

                    if (
                        !$lastRecord ||
                        now()->diffInDays($lastRecord->created_at) >= $service->periodicidad_dias
                    ) {
                        $this->createServiceRecord($vehicle, $service);
                    }
                }
            }
        }

        $this->info('Verificación de servicios completada.');
    }

    /**
     * Crea un registro de servicio pendiente y notifica al operador si `notificar` es true.
     */
    private function createServiceRecord(Vehicle $vehicle, Servicio $service)
    {
        VehicleServiceRecord::firstOrCreate(
            ['vehicle_id' => $vehicle->id, 'service_id' => $service->id, 'status' => 'pending'],
            ['operador_id' => $vehicle->operador_id]
        );

        // Verificar si el servicio tiene `notificar` en true antes de enviar la notificación
        if ($service->notificar && $vehicle->operador_id) {
            $vehicle->operador->notify(new ServiceNotification($vehicle, $service));
            $this->info("Notificación enviada al operador del vehículo {$vehicle->placa} para el servicio {$service->nombre}.");
        } else {
            $this->info("Notificación omitida para el servicio {$service->nombre} del vehículo {$vehicle->placa}.");
        }
    }
}
