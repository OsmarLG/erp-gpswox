<?php

namespace App\Livewire\Security\V2\Roles;

use Mary\Traits\Toast;
use Livewire\Component;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\On;

class Show extends Component
{
    use Toast;

    public $role;
    public $selectedPermissions = [];
    public $models = [];
    public $otherPermissions = [];
    public bool $create_permission_modal = false;
    public string $permissionName = '';
    public bool $continuarCreando = false;

    public function mount()
    {
        if (!auth()->user()->hasPermissionTo('view_role')) {
            return abort(403);
        }
    }

    public function loadPermissions()
    {
        // Obtener todos los modelos en App\Models
        $modelDirectory = app_path('Models');
        $this->models = collect(scandir($modelDirectory))
            ->filter(fn($file) => str_ends_with($file, '.php'))
            ->map(fn($file) => [
                'name' => str_replace('.php', '', $file),
                'namespace' => 'App\\Models\\' . str_replace('.php', '', $file),
            ]);

        // Definir permisos predeterminados
        $defaultPermissions = ['view_any', 'view', 'create', 'update', 'delete', 'restore', 'force_delete'];

        foreach ($this->models as $model) {
            foreach ($defaultPermissions as $permission) {
                $permissionName = $permission . '_' . Str::snake($model['name']);
                if (!Permission::where('name', $permissionName)->exists()) {
                    Permission::create(['name' => $permissionName]);
                }
            }
        }

        // Cargar permisos ya asignados al rol
        $this->selectedPermissions = $this->role->permissions->pluck('name')->toArray();

        // Cargar permisos que no están relacionados con modelos
        $this->otherPermissions = Permission::whereNotIn('name', $this->getAllModelPermissions())
            ->orderBy('name', 'asc') // Ordenar por nombre de forma ascendente
            ->pluck('name')
            ->toArray();
    }

    public function getAllModelPermissions()
    {
        $permissions = [];
        foreach ($this->models as $model) {
            $permissions = array_merge($permissions, [
                'view_any_' . Str::snake($model['name']),
                'view_' . Str::snake($model['name']),
                'create_' . Str::snake($model['name']),
                'update_' . Str::snake($model['name']),
                'delete_' . Str::snake($model['name']),
                'restore_' . Str::snake($model['name']),
                'force_delete_' . Str::snake($model['name']),
            ]);
        }
        return $permissions;
    }

    public function togglePermission($permission)
    {
        if (in_array($permission, $this->selectedPermissions)) {
            $this->role->revokePermissionTo($permission);
            $this->selectedPermissions = array_diff($this->selectedPermissions, [$permission]);
        } else {
            $this->role->givePermissionTo($permission);
            $this->selectedPermissions[] = $permission;
        }
    }

    public function render()
    {
        $this->loadPermissions();

        return view('livewire.security.v2.roles.show', [
            'models' => $this->models,
            'otherPermissions' => $this->otherPermissions,
        ]);
    }

    public function openCreateModal()
    {
        $this->reset(['permissionName']);
        $this->create_permission_modal = true;
    }

    public function toggleAllPermissions()
    {
        $allPermissions = Permission::pluck('name')->toArray();

        if ($this->isAllPermissionsSelected()) {
            // Desmarcar todos los permisos
            $this->role->revokePermissionTo($allPermissions);
            $this->selectedPermissions = [];
        } else {
            // Seleccionar todos los permisos
            $this->role->syncPermissions($allPermissions);
            $this->selectedPermissions = $allPermissions;
        }

        $this->lunchToast();
    }

    public function toggleModelPermissions($modelName)
    {
        $permissionsForModel = [
            'view_any_' . Str::snake($modelName),
            'view_' . Str::snake($modelName),
            'create_' . Str::snake($modelName),
            'update_' . Str::snake($modelName),
            'delete_' . Str::snake($modelName),
            'restore_' . Str::snake($modelName),
            'force_delete_' . Str::snake($modelName),
        ];

        if ($this->isModelPermissionsSelected($modelName)) {
            // Desmarcar todos los permisos del modelo
            $this->role->revokePermissionTo($permissionsForModel);
            $this->selectedPermissions = array_diff($this->selectedPermissions, $permissionsForModel);
        } else {
            // Seleccionar todos los permisos del modelo
            $this->role->givePermissionTo($permissionsForModel);
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $permissionsForModel));
        }

        $this->lunchToast();
    }

    public function syncPermission($permission)
    {
        if (in_array($permission, $this->selectedPermissions)) {
            $this->role->givePermissionTo($permission);
        } else {
            $this->role->revokePermissionTo($permission);
        }

        $this->lunchToast();
    }

    private function lunchToast()
    {
        $this->toast(
            type: 'success',
            title: 'Cambios Guardados',
            description: 'Los cambios se han guardado con éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function createPermission()
    {
        $this->validate(['permissionName' => 'required|string|unique:permissions,name']);

        $permission = Permission::create(['name' => $this->permissionName]);
        $this->role->givePermissionTo($permission);

        if (!$this->continuarCreando) {
            $this->reset(['create_permission_modal', 'permissionName']);
        } else {
            $this->reset(['permissionName']);
        }

        $this->toast(
            type: 'success',
            title: 'Creado',
            description: 'Permiso Creado Con Exito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function isAllPermissionsSelected()
    {
        $allPermissions = Permission::pluck('name')->toArray();
        return empty(array_diff($allPermissions, $this->selectedPermissions));
    }

    public function isModelPermissionsSelected($modelName)
    {
        $permissionsForModel = [
            'view_any_' . Str::snake($modelName),
            'view_' . Str::snake($modelName),
            'create_' . Str::snake($modelName),
            'update_' . Str::snake($modelName),
            'delete_' . Str::snake($modelName),
            'restore_' . Str::snake($modelName),
            'force_delete_' . Str::snake($modelName),
        ];

        return empty(array_diff($permissionsForModel, $this->selectedPermissions));
    }

    public function areOtherPermissionsSelected()
    {
        // Si no hay permisos en otrosPermisos, retornar false
        if (empty($this->otherPermissions)) {
            return false;
        }

        // Retornar true si todos los permisos en otrosPermisos están seleccionados
        return empty(array_diff($this->otherPermissions, $this->selectedPermissions));
    }


    public function toggleOtherPermissions()
    {
        if ($this->areOtherPermissionsSelected()) {
            // Desmarcar todos los permisos
            $this->role->revokePermissionTo($this->otherPermissions);
            $this->selectedPermissions = array_diff($this->selectedPermissions, $this->otherPermissions);
        } else {
            // Seleccionar todos los permisos
            $this->role->givePermissionTo($this->otherPermissions);
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $this->otherPermissions));
        }

        $this->lunchToast();
    }
}
