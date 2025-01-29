<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            ["name" => "view_any_user"],
            ["name" => "create_user"],
            ["name" => "update_user"],
            ["name" => "view_user"],
            ["name" => "delete_user"],
            ["name" => "restore_user"],
            ["name" => "force_delete_user"],
            ["name" => "view_any_role"],
            ["name" => "view_role"],
            ["name" => "create_role"],
            ["name" => "update_role"],
            ["name" => "delete_role"],
            ["name" => "restore_role"],
            ["name" => "force_delete_role"],
            ["name" => "create_permission"],
            ["name" => "view_any_permission"],
            ["name" => "view_permission"],
            ["name" => "update_permission"],
            ["name" => "force_delete_permission"],
            ["name" => "restore_permission"],
            ["name" => "delete_permission"],
            ["name" => "asignar_role"],
            ["name" => "asignar_permission"],
            ["name" => "quitar_role"],
            ["name" => "quitar_permission"],
            ["name" => "view_menu_security"],
            ["name" => "view_menu_profile"],
            ["name" => "view_menu_dashboard"],
            ["name" => "view_any_notifications"],
            ["name" => "view_notifications"],
            ["name" => "mark_as_read_notifications"],
            ["name" => "mark_as_unread_notifications"],
            ["name" => "delete_notifications"],
            ["name" => "view_menu_notifications"],
            ["name" => "view_menu_users"],
            ["name" => "view_menu_logs"],
            ["name" => "view_menu_settings"]
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Crear roles
        $masterRole = Role::create(['name' => 'master']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'users']);

        // Asignar permisos a roles
        $masterRole->givePermissionTo(Permission::all());

        // Permisos para el rol "users" (crear y permisos básicos relacionados con usuarios)
        $userPermissions = Permission::where('name', 'LIKE', '%user%')
            ->whereNotIn('name', ['force_delete_user', 'restore_user', 'asignar_role', 'quitar_role', 'asignar_permission', 'quitar_permission'])
            ->get();

        // Agregar permisos de asignar y quitar roles/permisos al admin
        $additionalUsersPermissions = Permission::whereIn('name', ['view_menu_profile', 'view_menu_dashboard', 'view_any_notifications', 'view_notifications', 'mark_as_read_notifications', 'mark_as_unread_notifications', 'delete_notifications', 'view_menu_notifications'])->get();

        $userPermissions = $userPermissions->merge($additionalUsersPermissions);

        $userRole->givePermissionTo($userPermissions);

        // Permisos para el rol "admin" (todo lo relacionado con usuarios, pero sin forzar ni restaurar)
        $adminPermissions = Permission::where('name', 'LIKE', '%user%')
            ->whereNotIn('name', ['force_delete_user', 'restore_user'])
            ->get();

        // Agregar permisos de asignar y quitar roles/permisos al admin
        $additionalAdminPermissions = Permission::whereIn('name', ['asignar_permission', 'quitar_permission', 'asignar_role', 'quitar_role', 'view_menu_profile', 'view_menu_dashboard', 'view_any_notifications', 'view_notifications', 'mark_as_read_notifications', 'mark_as_unread_notifications', 'delete_notifications', 'view_menu_notifications'])->get();

        $adminPermissions = $adminPermissions->merge($additionalAdminPermissions);

        $adminRole->givePermissionTo($adminPermissions);

        // Crear usuarios
        $master = User::create([
            'username' => 'master',
            'name' => 'Osmar Liera',
            'email' => 'master@app.liartechnologies.com',
            'avatar' => null,
            'password' => Hash::make('Osmarsito0603'),
        ]);
        $master->assignRole($masterRole);

        $admin = User::create([
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@app.liartechnologies.com',
            'avatar' => null,
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        $user = User::create([
            'username' => 'user',
            'name' => 'User',
            'email' => 'user@app.liartechnologies.com',
            'avatar' => null,
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($userRole);
    }
}
