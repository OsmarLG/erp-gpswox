<?php

namespace App\Livewire;

use App\Models\Servicio;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Models\VehicleServiceRecord;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

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
        $this->totalServices = Servicio::count();
        $this->totalVehicles = Vehicle::count();

        $today = Carbon::today();
        $this->usersTodayCount = User::whereDate('created_at', $today)->count();
        $this->usersToday = User::whereDate('created_at', $today)->orderBy('created_at', 'desc')->take(5)->get();

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
            $servicesPending = VehicleServiceRecord::where('vehicle_id', auth()->user()->vehiculo->id)
                ->where('operador_id', auth()->user()->id)
                ->where('status', 'pending')
                ->orWhere('status', 'initiated')
                ->orderBy('id', 'desc')
                ->paginate(10);

            $servicesCompleted = VehicleServiceRecord::where('vehicle_id', auth()->user()->vehiculo->id)
                ->where('operador_id', auth()->user()->id)
                ->where('status', 'completed')
                ->orderBy('id', 'desc')
                ->paginate(10);

            $vehicleRequestsPending = VehicleRequest::where('vehicle_id', auth()->user()->vehiculo->id)
                ->whereIn('status', ['pending', 'initiated'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        return view('livewire.dashboard', compact('servicesPending', 'servicesCompleted', 'vehicleRequestsPending'));
    }
}
