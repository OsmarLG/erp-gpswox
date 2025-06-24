<?php

namespace App\Livewire;

use App\Models\ServiceRequest;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Models\VehicleServiceKilometer;
use App\Models\VehicleServiceRecord;
use App\Notifications\ServiceNotification;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class Dashboard extends Component
{
    use WithPagination, Toast;
    protected $paginationTheme = 'tailwind';

    public $pageField = 1;
    public $pagePart = 1;

    public array $myChart = [];

    // Stats
    public int $totalUsers = 0;
    public int $totalOperadores = 0;
    public int $totalRoles = 0;
    public int $totalPermissions = 0;
    public int $totalServices = 0;
    public int $totalServicesOperador = 0;
    public int $totalVehicles = 0;
    public int $usersTodayCount = 0;

    // Últimos usuarios registrados hoy
    public $usersToday = [];

    // Cambiar tipo de gráfico (pie/bar)
    public function switch()
    {
        $type = $this->myChart['type'] === 'bar' ? 'pie' : 'bar';
        Arr::set($this->myChart, 'type', $type);
    }

    // Si quieres generar datos aleatorios al vuelo
    public function randomize()
    {
        // Obtenemos las 'labels'
        $labels = $this->myChart['data']['labels'];

        // Generamos valores aleatorios para roles y permisos
        $randomRoles = [];
        $randomPerms = [];

        foreach ($labels as $label) {
            $randomRoles[] = fake()->numberBetween(0, 20);
            $randomPerms[] = fake()->numberBetween(0, 50);
        }

        // Ajustamos el dataset en el array
        Arr::set($this->myChart, 'data.datasets.0.data', $randomRoles);
        Arr::set($this->myChart, 'data.datasets.1.data', $randomPerms);
    }

    public function mount()
    {
        if (auth()->user()->hasRole(['master', 'admin'])) {
            $this->loadAdminStats();
        }
    }

    protected function loadAdminStats()
    {
        $this->totalUsers = User::count();
        $this->totalOperadores = User::whereHas('roles', fn($q) => $q->where('name', 'operador'))->count();
        $this->totalRoles = Role::count();
        $this->totalPermissions = Permission::count();
        $this->totalServices = VehicleServiceRecord::count();
        $this->totalVehicles = Vehicle::count();

        $today = Carbon::today();
        // $this->usersTodayCount = User::whereDate('created_at', $today)->count();
        // $this->usersToday = User::whereDate('created_at', $today)->orderBy('created_at', 'desc')->take(5)->get();

        $topUsers = User::all()->map(function ($user) {
            $user->roles_count = $user->roles()->count();
            $user->permissions_count = $user->getAllPermissions()->count();
            $user->sum_rp = $user->roles_count + $user->permissions_count;
            return $user;
        })->sortByDesc('sum_rp')->take(5);

        $this->myChart = [
            'type' => 'pie',
            'data' => [
                'labels' => $topUsers->pluck('name')->toArray(),
                'datasets' => [
                    ['label' => 'Roles', 'data' => $topUsers->pluck('roles_count')->toArray()],
                    ['label' => 'Permisos', 'data' => $topUsers->pluck('permissions_count')->toArray()],
                ],
            ],
        ];
    }

    public function initService(int $serviceId)
    {
        return redirect()->route('servicios.service', $serviceId);
    }

    public function initRequest(int $serviceId)
    {
        $service = VehicleRequest::findOrFail($serviceId);

        $service->update(['status' => 'initiated']);

        return redirect()->route('servicios.request', $serviceId);
    }

    public function showRequest(int $serviceId)
    {
        return redirect()->route('servicios.request', $serviceId);
    }

    public function showRequest2($requestId)
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

    protected function getGlobalVehicleRequestsFieldProperty()
    {
        return VehicleRequest::with(['vehicle', 'operador'])
            ->where('type', 'field')
            ->whereIn('status', ['pending', 'initiated'])
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'pageField');
    }

    protected function getGlobalVehicleRequestsPartProperty()
    {
        return VehicleRequest::with(['vehicle', 'operador', 'parte'])
            ->where('type', 'part')
            ->whereIn('status', ['pending', 'initiated'])
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'pagePart');
    }

    protected function getServicesRequests()
    {
        return ServiceRequest::with(['vehicle', 'operador', 'service'])
            ->whereIn('status', ['pending'])
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'pageService');
    }

    // public function getUpcomingServices()
    // {
    //     $records = VehicleServiceKilometer::with(['vehicle.operador', 'service'])->get();

    //     $result = [];

    //     foreach ($records as $r) {
    //         $servicio = $r->service;
    //         if (!$servicio) continue;

    //         $faltanKm = $servicio->periodicidad_km
    //             ? ($servicio->periodicidad_km - ($r->current_km - $r->last_km))
    //             : null;

    //         $kmTranscurridos = $r->current_km - $r->last_km;

    //         $ultimoServicio = VehicleServiceRecord::where('vehicle_id', $r->vehicle_id)
    //             ->where('service_id', $r->service_id)
    //             ->whereNotNull('fecha_realizacion')
    //             ->orderByDesc('fecha_realizacion')
    //             ->first();

    //         $faltanDias = null;
    //         if ($servicio->periodicidad_dias && $ultimoServicio) {
    //             $fechaProxima = $ultimoServicio->fecha_realizacion->copy()->addDays($servicio->periodicidad_dias);
    //             $faltanDias = now()->diffInDays($fechaProxima, false);
    //         }

    //         if (
    //             ($faltanKm !== null && $faltanKm <= 500) ||
    //             ($faltanDias !== null && $faltanDias <= 7)
    //         ) {
    //             $result[] = [
    //                 'vehiculo' => $r->vehicle,
    //                 'operador' => $r->vehicle->operador,
    //                 'servicio' => $servicio,
    //                 'faltan_km' => max(0, intval($faltanKm)),
    //                 'faltan_dias' => round(max(0, $faltanDias)),
    //             ];
    //         }
    //     }

    //     return collect($result);
    // }

    public function getUpcomingServices()
    {
        $vehiculos = Vehicle::with('operador')->get();
        $servicios = Servicio::all();
        $result = [];

        foreach ($vehiculos as $vehiculo) {
            foreach ($servicios as $servicio) {

                // Buscamos el último servicio realizado
                $ultimoServicio = VehicleServiceRecord::where('vehicle_id', $vehiculo->id)
                    ->where('service_id', $servicio->id)
                    ->whereNotNull('fecha_realizacion')
                    ->orderByDesc('fecha_realizacion')
                    ->first();

                if (!$ultimoServicio) {
                    continue; // Si nunca se ha realizado, lo ignoramos (podríamos cambiar esta lógica si lo deseas)
                }

                // Obtenemos el current_km desde VehicleServiceKilometer
                $kmData = VehicleServiceKilometer::where('vehicle_id', $vehiculo->id)
                    ->where('service_id', $servicio->id)
                    ->first();

                $currentKm = $kmData ? $kmData->current_km : null;

                $kmTranscurridos = $currentKm !== null ? $currentKm - $ultimoServicio->valor_kilometraje : null;
                $faltanKm = ($servicio->periodicidad_km && $kmTranscurridos !== null)
                    ? $servicio->periodicidad_km - $kmTranscurridos
                    : null;
                $porcentajeKm = ($servicio->periodicidad_km && $kmTranscurridos !== null && $servicio->periodicidad_km > 0)
                    ? round(($kmTranscurridos / $servicio->periodicidad_km) * 100)
                    : null;

                $diasTranscurridos = now()->diffInDays($ultimoServicio->fecha_realizacion);

                if ($diasTranscurridos < 0) {
                    $diasTranscurridos *= -1;
                }

                $faltanDias = $servicio->periodicidad_dias ? $servicio->periodicidad_dias - $diasTranscurridos : null;


                $porcentajeDias = ($servicio->periodicidad_dias && $servicio->periodicidad_dias > 0)
                    ? round(($diasTranscurridos / $servicio->periodicidad_dias) * 100)
                    : null;

                // dd($servicio->id, $diasTranscurridos, $faltanDias, $porcentajeDias);

                // Ahora validamos cuándo mostrar
                $mostrarPorKm = $kmTranscurridos !== null && $kmTranscurridos >= 1000;
                $mostrarPorDias = $diasTranscurridos >= 5;

                if ($mostrarPorKm || $mostrarPorDias) {

                    // Estados por km
                    $estadoKm = null;
                    if ($porcentajeKm !== null) {
                        $estadoKm = $porcentajeKm >= 100 ? 'VENCIDO' : ($porcentajeKm >= 95 ? 'URGENTE' : ($porcentajeKm >= 90 ? 'PROXIMO' : null));
                    }

                    // Estados por días
                    $estadoDias = null;
                    if ($porcentajeDias !== null) {
                        $estadoDias = $porcentajeDias >= 100 ? 'VENCIDO' : ($porcentajeDias >= 95 ? 'URGENTE' : ($porcentajeDias >= 90 ? 'PROXIMO' : null));
                    }

                    // Prioridad combinada
                    $prioridades = ['VENCIDO', 'URGENTE', 'PROXIMO'];
                    $estadoPrioridad = null;

                    foreach ($prioridades as $prioridad) {
                        if ($estadoKm === $prioridad || $estadoDias === $prioridad) {
                            $estadoPrioridad = $prioridad;
                            break;
                        }
                    }

                    if ($estadoPrioridad) {
                        $result[] = [
                            'vehiculo' => $vehiculo,
                            'operador' => $vehiculo->operador,
                            'servicio' => $servicio,
                            'km_transcurridos' => max(0, intval($kmTranscurridos ?? 0)),
                            'faltan_km' => intval($faltanKm ?? 0),
                            'porcentaje_km' => $porcentajeKm,
                            'estado_km' => $estadoKm,
                            'dias_transcurridos' => intval($diasTranscurridos ?? 0),
                            'faltan_dias' => intval($faltanDias ?? 0),
                            'porcentaje_dias' => $porcentajeDias,
                            'estado_dias' => $estadoDias,
                            'estado_prioridad' => $estadoPrioridad,
                            'ultimo_servicio' => $ultimoServicio,
                        ];
                    }
                }
            }
        }

        return collect($result)
            ->sortBy([
                fn($item) => array_search($item['estado_prioridad'], ['VENCIDO', 'URGENTE', 'PROXIMO']),
                fn($item) => min($item['faltan_km'] ?? PHP_INT_MAX, $item['faltan_dias'] ?? PHP_INT_MAX),
            ])->groupBy('estado_prioridad');
    }

    public function render()
    {
        $servicesPending = [];
        $servicesCompleted = [];
        $vehicleRequestsPending = [];

        if (auth()->user()->hasRole(['master', 'admin'])) {
            $servicesPending = VehicleServiceRecord::where('status', 'pending')->orWhere('status', 'initiated')->orderBy('id', 'desc')->paginate(10);
            $servicesCompleted = VehicleServiceRecord::where('status', 'completed')->orderBy('id', 'desc')->paginate(10);
        }

        if (auth()->user()->hasRole('operador') && auth()->user()->vehiculo) {
            $servicesPending = VehicleServiceRecord::with(['service', 'vehicle', 'detalles'])
                ->where('vehicle_id', auth()->user()->vehiculo->id)
                ->where('operador_id', auth()->user()->id)
                ->where(function ($q) {
                    $q->where('status', 'pending')->orWhere('status', 'initiated');
                })
                ->orderBy('id', 'desc')
                ->paginate(10);

            $servicesCompleted = VehicleServiceRecord::with(['service', 'vehicle', 'detalles'])
                ->where('vehicle_id', auth()->user()->vehiculo->id)
                ->where('operador_id', auth()->user()->id)
                ->where('status', 'completed')
                ->orderBy('id', 'desc')
                ->paginate(10);

            $vehicleRequestsPending = VehicleRequest::with(['vehicle', 'operador', 'parte'])
                ->where('vehicle_id', auth()->user()->vehiculo->id)
                ->whereIn('status', ['pending', 'initiated'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        return view('livewire.dashboard', [
            'servicesPending' => $servicesPending,
            'servicesCompleted' => $servicesCompleted,
            'vehicleRequestsPending' => $vehicleRequestsPending,
            'globalVehicleRequestsField' => $this->getGlobalVehicleRequestsFieldProperty(),
            'globalVehicleRequestsPart' => $this->getGlobalVehicleRequestsPartProperty(),
            'upcomingServices' => $this->getUpcomingServices(),
            'servicesRequests' => $this->getServicesRequests(),
        ]);
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
}
