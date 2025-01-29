<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class Dashboard extends Component
{
    public array $myChart = [];

    // Stats
    public int $totalUsers = 0;
    public int $totalRoles = 0;
    public int $totalPermissions = 0;
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
        // 1. Stats: totales
        $this->totalUsers       = User::count();
        $this->totalRoles       = Role::count();
        $this->totalPermissions = Permission::count();

        // 2. Cantidad de usuarios registrados hoy
        $today = Carbon::today();
        $this->usersTodayCount = User::whereDate('created_at', $today)->count();

        // 3. Top 5 usuarios (por roles + permisos)
        $topUsers = User::all()->map(function ($user) {
            // Roles directos
            $user->roles_count = $user->roles()->count();

            // Todos los permisos (directos + heredados)
            $user->permissions_count = $user->getAllPermissions()->count();

            // sum_rp = roles + permisos
            $user->sum_rp = $user->roles_count + $user->permissions_count;

            return $user;
        })->sortByDesc('sum_rp')->take(5);

        $labels    = $topUsers->pluck('name')->toArray();
        $rolesData = $topUsers->pluck('roles_count')->toArray();
        $permsData = $topUsers->pluck('permissions_count')->toArray();

        // 4. Construir Chart con 2 datasets (Roles y Permisos)
        // Por defecto lo dejamos en "pie", puedes poner "bar" si prefieres
        $this->myChart = [
            'type' => 'pie',  // 'pie' / 'bar' / 'doughnut', etc.
            'data' => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label' => 'Roles',
                        'data'  => $rolesData,
                    ],
                    [
                        'label' => 'Permisos',
                        'data'  => $permsData,
                    ]
                ]
            ],
        ];

        // 5. Lista de usuarios registrados hoy (máx. 5)
        $this->usersToday = User::whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
