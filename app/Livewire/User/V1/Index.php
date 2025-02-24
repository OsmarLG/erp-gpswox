<?php

namespace App\Livewire\User\V1;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Index extends Component
{
    use WithPagination, Toast;

    // Búsqueda y modales
    public $search = '';
    public bool $create_user_modal = false;
    public bool $edit_user_modal = false;

    // Campos para crear/editar
    public ?int $editing_user_id = null;
    public string $userName = '';
    public string $userLastName = '';
    public string $userEmail = '';
    public string $userUsername = '';
    public string $userPassword = '';
    public string $userPassword_confirmation = '';

    // Roles y Permisos disponibles (para selects/checkboxes)
    public array $availableRoles = [];
    public array $availablePermissions = [];

    // Roles y Permisos seleccionados en el form
    public array $selectedRoles = [];
    public array $selectedPermissions = [];

    public bool $continuarCreando = false;

    // Cabeceras de la tabla
    public array $headers = [
        ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
        ['key' => 'avatar', 'label' => 'Avatar', 'class' => 'w-1'],
        ['key' => 'name', 'label' => 'Nombre', 'class' => 'text-black dark:text-white'],
        ['key' => 'apellidos', 'label' => 'Apellido', 'class' => 'text-black dark:text-white'],
        ['key' => 'username', 'label' => 'Username', 'class' => 'text-black dark:text-white'],
        ['key' => 'fecha_nacimiento', 'label' => 'Fecha de Nacimiento', 'class' => 'text-black dark:text-white'],
        ['key' => 'edad', 'label' => 'Edad', 'class' => 'text-black dark:text-white'],
        ['key' => 'email', 'label' => 'Email', 'class' => 'text-black dark:text-white'],
        ['key' => 'roles', 'label' => 'Roles', 'class' => 'text-black dark:text-white'],
    ];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    /**
     * Revisa permisos al montar el componente
     */
    public function mount()
    {
        // Verifica que el usuario tenga permiso de ver usuarios
        if (!auth()->user() || !auth()->user()->hasPermissionTo('view_any_user')) {
            abort(403);
        }

        // Carga todos los roles y permisos disponibles (Spatie)
        // Obtiene todos los roles
        $roles = Role::pluck('name', 'id'); // [1 => 'admin', 2 => 'master', ...]

        // Mapea a algo como: [ ['id' => 1, 'name' => 'admin'], ... ]
        $this->availableRoles = collect($roles)->map(function ($roleName, $roleId) {
            return [
                'id'   => $roleId,
                'name' => $roleName
            ];
        })->values()->toArray();

        // Igual con permisos, si quieres la misma forma:
        $permissions = Permission::pluck('name', 'id');
        $this->availablePermissions = collect($permissions)->map(function ($permName, $permId) {
            return [
                'id'   => $permId,
                'name' => $permName
            ];
        })->values()->toArray();
    }

    /**
     * Abre el modal de crear usuario (limpia variables)
     */
    public function openCreateModal()
    {
        $this->reset([
            'userName',
            'userLastName',
            'userEmail',
            'userUsername',
            'userPassword',
            'userPassword_confirmation',
            'selectedRoles',
            'selectedPermissions',
        ]);
        $this->create_user_modal = true;
    }

    /**
     * Crea un nuevo usuario con roles y/o permisos
     */
    public function createUser()
    {
        $this->validate([
            'userName'     => 'required|string',
            'userLastName'     => 'required|string',
            'userUsername'    => 'required|string|unique:users,username',
            'userEmail'    => 'required|email|unique:users,email',
            'userPassword' => 'required|min:6|confirmed',
            // 'userPassword_confirmation' => 'required_with:userPassword|same:userPassword|min:6'
        ]);

        // Creamos el usuario
        $user = User::create([
            'name'     => $this->userName,
            'apellidos'     => $this->userLastName,
            'username'    => $this->userUsername,
            'email'    => $this->userEmail,
            'password' => Hash::make($this->userPassword),
        ]);

        // Asignar roles (con syncRoles)
        if (!empty($this->selectedRoles)) {
            // Obtenemos los nombres de esos roles
            $rolesNames = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();
            $user->syncRoles($rolesNames);
        }

        // Asignar permisos (con syncPermissions)
        if (!empty($this->selectedPermissions)) {
            $permissionsNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
            $user->syncPermissions($permissionsNames);
        }

        if (!$this->continuarCreando) {
            $this->reset([
                'create_user_modal',
                'userName',
                'userLastName',
                'userUsername',
                'userEmail',
                'userPassword',
                'userPassword_confirmation',
                'selectedRoles',
                'selectedPermissions'
            ]);
        } else {
            $this->reset(['userName', 'userUsername', 'userLastName', 'userEmail', 'userPassword', 'userPassword_confirmation', 'selectedRoles', 'selectedPermissions']);
        }

        $this->success('Usuario creado con éxito!');
    }

    /**
     * Abre el modal de edición de un usuario existente
     */
    public function editUser(int $userId)
    {
        $user = User::findOrFail($userId);

        $this->editing_user_id = $user->id;
        $this->userName  = $user->name;
        $this->userLastName  = $user->apellidos;
        $this->userUsername = $user->username;
        $this->userEmail = $user->email;
        $this->userPassword = ''; // vacío, solo se setea si se cambia

        // Obtenemos roles y permisos actuales del usuario en IDs
        $this->selectedRoles = $user->roles()->pluck('id')->toArray();
        $this->selectedPermissions = $user->permissions()->pluck('id')->toArray();

        $this->edit_user_modal = true;
    }

    /**
     * Actualiza datos del usuario
     */
    public function updateUser()
    {
        $this->validate([
            'userName'     => 'required|string',
            'userLastName'     => 'string',
            'userUsername'    => 'required|string|unique:users,username,' . $this->editing_user_id,
            'userEmail'    => 'required|email|unique:users,email,' . $this->editing_user_id,
            'userPassword' => 'nullable|min:6|confirmed',
            // 'userPassword_confirmation' => 'required_with:userPassword|same:userPassword|min:6'
        ]);

        $user = User::findOrFail($this->editing_user_id);

        $data = [
            'name'  => $this->userName,
            'apellidos'  => $this->userLastName,
            'username' => $this->userUsername,
            'email' => $this->userEmail,
        ];

        // Si hay password, se actualiza
        if (!empty($this->userPassword)) {
            $data['password'] = Hash::make($this->userPassword);
        }

        $user->update($data);

        // Sincronizar roles y permisos
        $rolesNames = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();
        $user->syncRoles($rolesNames);

        $permissionsNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
        $user->syncPermissions($permissionsNames);

        $this->reset([
            'edit_user_modal',
            'editing_user_id',
            'userName',
            'userLastName',
            'userUsername',
            'userEmail',
            'userPassword',
            'userPassword_confirmation',
            'selectedRoles',
            'selectedPermissions'
        ]);

        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Usuario Actualizado Con Éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    /**
     * Elimina un usuario
     */
    public function deleteUser(int $userId)
    {
        $user = User::findOrFail($userId);

        // Evitar eliminar a usuario master, o a ti mismo, según tu lógica
        if ($user->id === auth()->id()) {
            $this->error('No se puede eliminar a ti mismo');
            return;
        }
        if ($user->hasRole('master')) {
            $this->error('No se puede eliminar a un usuario master');
            return;
        }

        $user->delete();

        $this->toast(
            type: 'success',
            title: 'Eliminado',
            description: 'Usuario Eliminado Con Éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    /**
     * Redirecciona a la vista "show" (detalle) del usuario
     */
    public function viewUser(string $userId)
    {
        return redirect()->route('users.show', $userId);
    }

    /**
     * Renderiza la vista con la lista de usuarios
     */
    public function render()
    {
        if (auth()->user()->hasRole(['master'])) {
            $users = User::with('roles', 'permissions')
                ->where('name', 'like', '%' . $this->search . '%')
                ->orderBy(...array_values($this->sortBy))
                ->paginate(10);
        } else {
            $users = User::with('roles', 'permissions')
                ->whereHas('roles', function ($query) {
                    $query->where('name', '!=', 'master');
                })
                ->where('name', 'like', '%' . $this->search . '%')
                ->orderBy(...array_values($this->sortBy))
                ->paginate(10);
        }

        return view('livewire.user.v1.index', [
            'headers' => $this->headers,
            'sortBy'  => $this->sortBy,
            'users'   => $users,
        ]);
    }
}
