<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PartesController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiciosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiculosController;
use Illuminate\Support\Facades\Route;



// Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('login');
        // Route::get('/register', [AuthController::class, 'register'])->name('register');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

// Redirigir la raíz dependiendo del estado de autenticación
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Rutas de dashboard (autenticadas)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/inicio', [DashboardController::class, 'index'])->name('dashboard');

    //Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
    });

    // Grupo de rutas para Roles y Permisos
    Route::prefix('security')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });

    // Grupo de rutas para Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
    });

    // Grupo de rutas para Servicios
    Route::prefix('servicios')->group(function () {
        Route::get('/', [ServiciosController::class, 'index'])->name('servicios.index');
        Route::get('/service/{id}', [ServiciosController::class, 'service'])->name('servicios.service');
        Route::get('/request/{id}', [ServiciosController::class, 'request'])->name('servicios.request');
        Route::get('/create', [ServiciosController::class, 'create'])->name('servicios.create');
    });

    // Grupo de rutas para Vehículos
    Route::prefix('vehiculos')->group(function () {
        Route::get('/', [VehiculosController::class, 'index'])->name('vehiculos.index');
        Route::get('/{id}', [VehiculosController::class, 'show'])->name('vehiculos.show');
    });

    // Grupo de rutas para Categorias
    Route::prefix('categorias')->group(function () {
        Route::get('/', [CategoriaController::class, 'index'])->name('categorias.index');
    });

    // Grupo de rutas para Partes
    Route::prefix('partes')->group(function () {
        Route::get('/', [PartesController::class, 'index'])->name('partes.index');
    });
});
