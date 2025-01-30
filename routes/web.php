<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiciosController;
use App\Http\Controllers\PermissionController;

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    });
});
