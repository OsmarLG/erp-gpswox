<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Parte;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionUserSeeder extends Seeder
{
    public function run(): void
    {
        /************************************************
         * 1. CREAR PERMISOS (Usuarios, Roles, etc.)
         ************************************************/
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
            ["name" => "view_menu_settings"],

            // NUEVOS: permisos para SERVICIOS
            ["name" => "view_menu_servicio"],
            ["name" => "view_any_servicio"],
            ["name" => "view_servicio"],
            ["name" => "create_servicio"],
            ["name" => "update_servicio"],
            ["name" => "delete_servicio"],
            ["name" => "restore_servicio"],
            ["name" => "force_delete_servicio"],

            // NUEVOS: permisos para Vehiculos
            ["name" => "view_menu_vehicle"],
            ["name" => "view_any_vehicle"],
            ["name" => "view_vehicle"],
            ["name" => "create_vehicle"],
            ["name" => "update_vehicle"],
            ["name" => "delete_vehicle"],
            ["name" => "restore_vehicle"],
            ["name" => "force_delete_vehicle"],
            
            // NUEVOS: permisos para Categorias
            ["name" => "view_menu_categoria"],
            ["name" => "view_any_categoria"],
            ["name" => "view_categoria"],
            ["name" => "create_categoria"],
            ["name" => "update_categoria"],
            ["name" => "delete_categoria"],
            ["name" => "restore_categoria"],
            ["name" => "force_delete_categoria"],
            
            // NUEVOS: permisos para Partes
            ["name" => "view_menu_parte"],
            ["name" => "view_any_parte"],
            ["name" => "view_parte"],
            ["name" => "create_parte"],
            ["name" => "update_parte"],
            ["name" => "delete_parte"],
            ["name" => "restore_parte"],
            ["name" => "force_delete_parte"],
        ];

        // Crear (o sincronizar) los permisos
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        /************************************************
         * 2. CREAR ROLES
         ************************************************/
        $masterRole   = Role::firstOrCreate(['name' => 'master']);
        $adminRole    = Role::firstOrCreate(['name' => 'admin']);
        $userRole     = Role::firstOrCreate(['name' => 'users']);
        $operadorRole = Role::firstOrCreate(['name' => 'operador']);
        $serviciosRole = Role::firstOrCreate(['name' => 'servicios']); // <--- NUEVO ROL
        $vehiclesRole = Role::firstOrCreate(['name' => 'vehicles']); // <--- NUEVO ROL
        $categoriesRole = Role::firstOrCreate(['name' => 'categories']); // <--- NUEVO ROL

        /************************************************
         * 3. ASIGNAR PERMISOS A ROLES
         ************************************************/
        // El rol "master" obtiene todos
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

        // 4. Permisos para el rol "operador"
        //    (ejemplo: permisos de menú + lectura/edición básica que quieras darle)
        $operadorPermissions = Permission::whereIn('name', [
            'view_menu_profile',
            'view_menu_dashboard',
            'view_any_notifications',
            'view_notifications',
            'mark_as_read_notifications',
            'mark_as_unread_notifications',
            'delete_notifications',
            'view_menu_notifications',
            // Agrega aquí otros permisos que consideres necesarios
        ])->get();

        $operadorRole->givePermissionTo($operadorPermissions);

        // Rol "servicios": asignarle permisos de servicios
        $serviciosPermissions = Permission::where('name', 'LIKE', '%servicio%')->get();
        // Por ejemplo, si quieres que "servicios" pueda ver, crear y actualizar servicios:
        // Filtras o los asocias todos
        $serviciosRole->givePermissionTo($serviciosPermissions);

        // Rol "vehicles": asignarle permisos de vehicles
        $vehiclesPermissions = Permission::where('name', 'LIKE', '%vehicle%')->get();
        // Por ejemplo, si quieres que "vehicles" pueda ver, crear y actualizar vehicles:
        // Filtras o los asocias todos
        $vehiclesRole->givePermissionTo($vehiclesPermissions);

        $adminPermissions = $adminPermissions->merge($serviciosPermissions);
        $adminPermissions = $adminPermissions->merge($vehiclesPermissions);

        $adminRole->givePermissionTo($adminPermissions);

        /************************************************
         * 4. CREAR USUARIOS DE PRUEBA
         ************************************************/
        $master = User::create([
            'username' => 'master',
            'name'     => 'Osmar',
            'apellidos' => 'Liera',
            'fecha_nacimiento' => '2001-07-26',
            'email'    => 'master@app.liartechnologies.com',
            'avatar'   => null,
            'password' => Hash::make('Osmarsito0603'),
        ]);
        $master->assignRole($masterRole);

        $admin = User::create([
            'username' => 'admin',
            'name'     => 'Admin',
            'apellidos' => 'User',
            'fecha_nacimiento' => '1985-06-20',
            'email'    => 'admin@app.liartechnologies.com',
            'avatar'   => null,
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        $user = User::create([
            'username' => 'user',
            'name'     => 'User',
            'apellidos' => 'Demo',
            'fecha_nacimiento' => '1995-02-10',
            'email'    => 'user@app.liartechnologies.com',
            'avatar'   => null,
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($userRole);

        $operador = User::create([
            'username' => 'operador',
            'name'     => 'Operador',
            'apellidos' => 'Pérez',
            'fecha_nacimiento' => '1992-05-15',
            'email'    => 'operador@app.liartechnologies.com',
            'avatar'   => null,
            'password' => Hash::make('password'),
        ]);
        $operador->assignRole($operadorRole);

        // NUEVO: usuario para el rol "servicios"
        $serviciosUser = User::firstOrCreate(
            ['username' => 'servicios'],
            [
                'name'     => 'Servicio',
                'apellidos' => 'Manager',
                'fecha_nacimiento' => '1990-01-01',
                'email'    => 'servicios@app.liartechnologies.com',
                'avatar'   => null,
                'password' => Hash::make('password'),
            ]
        );
        $serviciosUser->assignRole($serviciosRole);

        /************************************************
         * 5. (Opcional) SEMBRAR LA TABLA "servicios"
         ************************************************/
        $listaServicios = [
            [
                'nombre' => 'Niveles de aceite',
                'periodicidad_km' => null,
                'periodicidad_dias' => 7,
                'observaciones' => 'Verificación semanal',
                'notificar' => true,
            ],
            [
                'nombre' => 'Cambio Aceite (motor y caja), Anticongelante, frenos',
                'periodicidad_km' => 10000,
                'periodicidad_dias' => null,
                'observaciones' => '',
                'notificar' => true,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Afinación',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => null,
                'observaciones' => '',
                'notificar' => true,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Clutch',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Aire Acondicionado',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Amortiguadores delanteros',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Amortiguadores traseros',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Cadena distribución',
                'periodicidad_km' => 50000,
                'periodicidad_dias' => 250,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Bateria',
                'periodicidad_km' => null,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Filtro gasolina',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Frenos delanteros',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
                'notificar' => true,
            ],
            [
                'nombre' => 'Frenos traseros',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
                'notificar' => true,
            ],
            [
                'nombre' => 'Parabrisas',
                'periodicidad_km' => null,
                'periodicidad_dias' => 730,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Limpiadores',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Dirección',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Suspensión delantera',
                'periodicidad_km' => 20000,
                'periodicidad_dias' => 90,
                'observaciones' => '',
                'notificar' => true,
            ],
            [
                'nombre' => 'Suspensión trasera',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
                'notificar' => true,
            ],
            [
                'nombre' => 'Soporte Motor',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Soporte tansmision',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Alineación',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Balanceo',
                'periodicidad_km' => 80000,
                'periodicidad_dias' => 365,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Reemplazo llanta DD',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Reemplazo llanta DI',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Reemplazo llanta TD',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Reemplazo llanta TI',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Reemplazo llanta refaccion',
                'periodicidad_km' => 160000,
                'periodicidad_dias' => 730,
                'observaciones' => '',
            ],
            [
                'nombre' => 'Luces exteriores',
                'periodicidad_km' => 40000,
                'periodicidad_dias' => 180,
                'observaciones' => '',
            ],
        ];

        // Insertar/actualizar cada uno
        foreach ($listaServicios as $svc) {
            Servicio::create($svc);
        }

        $categorias = [
            'Refacciones' => [],
            'Herramientas' => ['Gato'],
            'Servicio' => ['Radio'],
            'Vestiduras' => ['Delanteras', 'Traseras', 'Intermedias'],
            'Luces' => ['Delanteras', 'Traseras'],
            'Indicadores' => ['KM', 'Tablero'],
            'Llantas' => ['LL DD', 'LL DI', 'LL TD', 'LL TI'],
            'Motor' => ['Foto Motor', 'Batería superior'],
            'Carroceria' => ['Frente Derecho', 'Frente Izquierdo', 'Trasero Izquierdo', 'Trasero Derecho', 
            'Lateral Izquierda', 'Lateral Derecha', 'Video Exterior', 'Video Interior'],
            'Documentos' => ['Tarjeta de circulación', 'TAG de casetas'],
        ];

        foreach ($categorias as $categoriaNombre => $partes) {
            $categoria = Categoria::create(['nombre' => $categoriaNombre]);
            foreach ($partes as $parteNombre) {
                Parte::create(['nombre' => $parteNombre, 'categoria_id' => $categoria->id]);
            }
        }
    }
}
