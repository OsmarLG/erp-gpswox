<?php

namespace App\Livewire\Security\V1;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Mary\Traits\Toast;

class Permissions extends Component
{
    use WithPagination, Toast;

    public $search = '';
    public bool $create_permission_modal = false;
    public bool $edit_permission_modal = false;
    public int|null $editing_permission_id = null;
    public string $permissionName = '';
    public bool $continuarCreando = false;

    public array $headers = [
        ['key' => 'name', 'label' => 'Nombre del Permiso', 'class' => 'text-black dark:text-white'],
    ];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function createPermission()
    {
        $this->validate([
            'permissionName' => 'required|string',
        ]);

        $permission = Permission::create(['name' => $this->permissionName]);

        if (!$this->continuarCreando) {
            $this->reset(['create_permission_modal', 'permissionName']);
        } else {
            $this->reset(['permissionName']);
        }

        $this->toast(
            type: 'success',
            title: 'Creado',
            description: 'Permiso Creado Con Éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function editPermission(int $permissionId)
    {
        $permission = Permission::with('roles')->findOrFail($permissionId);

        $this->editing_permission_id = $permission->id;
        $this->permissionName = $permission->name;
        $this->edit_permission_modal = true;
    }

    public function updatepermission()
    {
        $this->validate([
            'permissionName' => 'required|string',
        ]);

        $permission = Permission::findOrFail($this->editing_permission_id);
        $permission->update(['name' => $this->permissionName]);

        $this->reset(['edit_permission_modal', 'editing_permission_id', 'permissionName']);
        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Permiso Actualizado Con Exito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function deletePermission(int $permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        $permission->delete();

        $this->toast(
            type: 'success',
            title: 'Elimnado',
            description: 'Permiso Elimnado Con Exito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function mount()
    {
        if (!auth()->user()->hasPermissionTo('view_any_permission')) {
            return abort(403);
        }
    }

    public function render()
    {
        $permissions = Permission::with('roles')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);

        return view('livewire.security.v1.permissions', [
            'headers' => $this->headers,
            'sortBy' => $this->sortBy,
            'permissions' => $permissions,
        ]);
    }

    public function openCreateModal()
    {
        $this->reset(['permissionName']);
        $this->create_permission_modal = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
