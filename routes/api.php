<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DocumentoController;
use App\Http\Controllers\API\BandejaController;
use App\Http\Controllers\API\CatalogoController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\Admin\AreaController;
use App\Http\Controllers\API\Admin\TipoDocumentoController;
use App\Http\Controllers\API\Admin\UsuarioController;

/* --- Rutas de Autenticación --- */
Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

/* --- Rutas Protegidas --- */
Route::group(['middleware' => 'auth:api'], function ($router) {

    // --- Rutas Específicas (DEBEN IR PRIMERO) ---
    // Estas rutas deben declararse ANTES de las rutas con parámetros dinámicos
    // para que el router de Laravel no las confunda.
    Route::get('dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('documentos/recibidos', [BandejaController::class, 'index']);
    Route::get('documentos/pendientes', [BandejaController::class, 'getPendientes']);
    Route::get('documentos/siguiente-correlativo', [DocumentoController::class, 'getSiguienteCorrelativo']);

    // --- Rutas de Recursos de Documentos ---
    Route::get('documentos', [DocumentoController::class, 'index']);
    Route::post('documentos', [DocumentoController::class, 'store']);
    // Las rutas con parámetros dinámicos como {documento} van al final.
    Route::get('documentos/{documento}', [DocumentoController::class, 'show']);
    Route::post('documentos/{documento}/derivar', [DocumentoController::class, 'derivar']);
    Route::post('documentos/{documento}/recepcionar', [DocumentoController::class, 'recepcionar']);

    // --- Rutas de Catálogos ---
    Route::get('catalogos/tipos-documento', [CatalogoController::class, 'getTiposDocumento']);
    Route::get('catalogos/areas', [CatalogoController::class, 'getAreas']);
});

/* --- Rutas de Administración --- */
Route::group([
    'middleware' => ['auth:api', 'is_admin'],
    'prefix' => 'admin'
], function ($router) {
    Route::apiResource('areas', AreaController::class);
    Route::apiResource('tipos-documento', TipoDocumentoController::class);
    Route::apiResource('usuarios', UsuarioController::class);
});
