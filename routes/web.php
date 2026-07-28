<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EquipoController; // <-- Esta línea es la que faltaba
use App\Http\Controllers\NotificationEmailController;
use App\Http\Controllers\ReasignacionController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [EquipoController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/reasignaciones', [ReasignacionController::class, 'index'])->name('reasignaciones.index');
    Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');
    Route::put('/equipos/{equipo}', [EquipoController::class, 'update'])->name('equipos.update');
    Route::delete('/equipos/{equipo}', [EquipoController::class, 'destroy'])->name('equipos.destroy');

    Route::post('/notificacion-correos', [NotificationEmailController::class, 'store'])->name('notificacion-correos.store');
    Route::delete('/notificacion-correos/{notificationEmail}', [NotificationEmailController::class, 'destroy'])->name('notificacion-correos.destroy');
});

Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/usuarios', [UserManagementController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UserManagementController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{usuario}', [UserManagementController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UserManagementController::class, 'destroy'])->name('usuarios.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';