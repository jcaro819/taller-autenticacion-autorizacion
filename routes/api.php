<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\MetricaController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
| Todas estas rutas llevan el prefijo /api (bootstrap/app.php).
*/

// ---- Rutas públicas ----
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1') // máx. 10 intentos por minuto (fuerza bruta)
    ->name('login');
Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');

// ---- Rutas protegidas: requieren "Authorization: Bearer <token>" (RNF 1.3) ----
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil', [UserController::class, 'perfil'])->name('perfil');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::put('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
    Route::get('/admin/metricas', [MetricaController::class, 'index'])->name('admin.metricas');
});
