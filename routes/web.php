<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\CargaController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\ReporteController;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('/plantillas', PlantillaController::class);

    Route::resource('/cargas', CargaController::class)->except(['edit', 'update']);

    Route::resource('/envios', EnvioController::class)->except(['edit', 'update']);
    Route::get('/envios/{envio}/progreso', [EnvioController::class, 'progreso'])->name('envios.progreso');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
});
