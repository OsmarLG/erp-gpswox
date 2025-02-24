<?php

namespace App\Livewire\Security\V2\Roles;

use Mary\Traits\Toast;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Index extends Component
{
    use WithPagination, Toast;

    public $search = '';
    public bool $create_role_modal = false;
    public bool $edit_role_modal = false;
    public int|null $editing_role_id = null;
    public string $roleName = '';
    public bool $continuarCreando = false;

    public array $headers = [
        ['key' => 'name', 'label' => 'Nombre del Rol', 'class' => 'text-black dark:text-white'],
        ['key' => 'count_permissions_column', 'label' => 'Permisos', 'class' => 'text-black dark:text-white'],
    ];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function mount()
    {
        if (!auth()->user()->hasPermissionTo('view_any_role')) {
            return abort(403);
        }
    }

    public function createRole()
    {
        $this->validate([
            'roleName' => 'required|string'
        ]);

        $role = Role::create(['name' => $this->roleName]);

        if (!$this->continuarCreando) {
            $this->reset(['create_role_modal', 'roleName']);
        } else {
            $this->reset(['roleName']);
        }

        $this->success('Rol Creado Con Exito');
    }

    public function viewRole(string $roleId)
    {
        return redirect()->route('roles.show', $roleId);
    }

    public function editRole(int $roleId)
    {
        $role = Role::findOrFail($roleId);

        $this->editing_role_id = $role->id;
        $this->roleName = $role->name;
        $this->edit_role_modal = true;
    }

    public function updateRole()
    {
        $this->validate([
            'roleName' => 'required|string'
        ]);

        $role = Role::findOrFail($this->editing_role_id);
        $role->update(['name' => $this->roleName]);

        $this->reset(['edit_role_modal', 'editing_role_id', 'roleName']);
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

    public function openCreateModal()
    {
        $this->reset(['roleName']);
        $this->create_role_modal = true;
    }

    public function render()
    {
        $roles = Role::with('permissions')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);

        return view('livewire.security.v2.roles.index', [
            'headers' => $this->headers,
            'sortBy' => $this->sortBy,
            'roles' => $roles,
        ]);
    }
}
