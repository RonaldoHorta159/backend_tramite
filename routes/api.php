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

    Route::get('dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('documentos/recibidos', [BandejaController::class, 'index']);
    Route::get('documentos/pendientes', [BandejaController::class, 'getPendientes']);
    Route::get('documentos/siguiente-correlativo/{area}', [DocumentoController::class, 'getSiguienteCorrelativo']);
    Route::get('documentos/por-area/{area}', [DocumentoController::class, 'indexPorAreaUsuario']);
    Route::get('documentos', [DocumentoController::class, 'index']);
    Route::post('documentos', [DocumentoController::class, 'store']);
    Route::get('documentos/{documento}', [DocumentoController::class, 'show']);
    Route::post('documentos/{documento}/derivar', [DocumentoController::class, 'derivar']);
    Route::post('documentos/{documento}/recepcionar', [DocumentoController::class, 'recepcionar']);

    // --- RUTAS MODIFICADAS Y AÑADIDAS ---
    // Se elimina la ruta 'finalizarConRespuesta' y se reemplaza por estas dos
    Route::post('documentos/{documento}/responder', [DocumentoController::class, 'responder']);
    Route::post('documentos/{documento}/finalizar', [DocumentoController::class, 'finalizar']);
    // --- FIN DE LA MODIFICACIÓN ---

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
    Route::get('documentos/por-area/{area}', [DocumentoController::class, 'indexPorArea']);
});
