<?php

namespace App\Livewire\Security\V1;

use Mary\Traits\Toast;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Roles extends Component
{
    use WithPagination, Toast;

    public $search = '';
    public bool $create_role_modal = false;
    public bool $edit_role_modal = false;
    public int|null $editing_role_id = null;
    public string $roleName = '';
    public string $newPermissionName = '';
    public array $selectedPermissions = [];
    public $permissions;
    public array $expanded = [2];
    public bool $continuarCreando = false;

    public array $headers = [
        ['key' => 'name', 'label' => 'Nombre del Rol', 'class' => 'text-black dark:text-white'],
        ['key' => 'count_permissions_column', 'label' => 'Permisos', 'class' => 'text-black dark:text-white'],
    ];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function createRole()
    {
        $this->validate([
            'roleName' => 'required|string',
            'selectedPermissions' => 'array',
        ]);

        $role = Role::create(['name' => $this->roleName]);
        $role->syncPermissions($this->selectedPermissions);

        if (!$this->continuarCreando) {
            $this->reset(['create_role_modal', 'roleName', 'selectedPermissions']);
        } else {
            $this->reset(['roleName', 'selectedPermissions']);
        }

        $this->success('Rol Creado Con Exito');
    }

    public function createPermission()
    {
        $this->validate(['newPermissionName' => 'required|string|unique:permissions,name']);

        $permission = Permission::create(['name' => $this->newPermissionName]);
        $this->permissions = Permission::pluck('name', 'id')->toArray();

        $this->reset('newPermissionName');
        $this->toast(
            type: 'success',
            title: 'Creado',
            description: 'Rol Creado Con Exito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function editRole(int $roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        $this->editing_role_id = $role->id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->edit_role_modal = true;
    }

    public function updateRole()
    {
        $this->validate([
            'roleName' => 'required|string',
            'selectedPermissions' => 'array',
        ]);

        $role = Role::findOrFail($this->editing_role_id);
        $role->update(['name' => $this->roleName]);
        $role->syncPermissions($this->selectedPermissions);

        $this->reset(['edit_role_modal', 'editing_role_id', 'roleName', 'selectedPermissions']);
        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Rol Actualizado Con Exito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function deleteRole(int $roleId)
    {
        $role = Role::findOrFail($roleId);
        $role->delete();

        $this->toast(
            type: 'success',
            title: 'Eliminado',
            description: 'Rol Elimnado Con Exito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function render()
    {
        $roles = Role::with('permissions')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);

        $this->permissions = Permission::all();

        // Obtener todos los modelos dentro de App\Models
        $modelDirectory = app_path('Models');
        $models = collect(scandir($modelDirectory))
            ->filter(fn($file) => str_ends_with($file, '.php'))
            ->map(fn($file) => [
                'name' => str_replace('.php', '', $file),
                'namespace' => 'App\\Models\\' . str_replace('.php', '', $file),
            ]);

        $defaultPermissions = ['view_any', 'view', 'create', 'update', 'delete', 'restore', 'force_delete'];

        foreach ($models as $model) {
            foreach ($defaultPermissions as $permission) {
                $permissionName = $permission . '_' . Str::snake($model['name']);

                // Verificar si el permiso ya existe, si no, crearlo
                if (!Permission::where('name', $permissionName)->exists()) {
                    Permission::create(['name' => $permissionName]);
                }
            }
        }

        return view('livewire.security.v1.roles', [
            'headers' => $this->headers,
            'sortBy' => $this->sortBy,
            'roles' => $roles,
            'permissions' => $this->permissions,
            'models' => $models,
        ]);
    }

    public function openCreateModal()
    {
        $this->reset(['roleName', 'selectedPermissions']);
        $this->create_role_modal = true;
    }
}
