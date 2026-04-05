<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SecretariaController;
use App\Http\Controllers\LimosneroController;
use App\Http\Controllers\FinanzasController;
use App\Http\Controllers\MiembroController;
use App\Http\Controllers\TesoreriaController;

use Spatie\Permission\Middleware\PermissionMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    /* ---------- Auth ---------- */
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    /* ---------- Miembros ---------- */
    Route::get('/miembros', [MiembroController::class, 'index']);

    /* ---------- Finanzas ---------- */
    Route::prefix('finanzas')->group(function () {

        Route::get('/cuotas', [FinanzasController::class, 'index'])
            ->middleware(PermissionMiddleware::class . ':finanzas.view');

        Route::post('/cuotas', [FinanzasController::class, 'store'])
            ->middleware(PermissionMiddleware::class . ':finanzas.edit');

        Route::get('/eventos', [FinanzasController::class, 'eventosIndex'])
            ->middleware(PermissionMiddleware::class . ':finanzas.view');

        Route::post('/eventos', [FinanzasController::class, 'eventosStore'])
            ->middleware(PermissionMiddleware::class . ':finanzas.edit');

        Route::get('/rubros', [FinanzasController::class, 'rubrosIndex'])
            ->middleware(PermissionMiddleware::class . ':finanzas.view');

        Route::get('/cuotas/{cuota}/bitacora', [FinanzasController::class, 'cuotaBitacoraShow'])
            ->middleware(PermissionMiddleware::class . ':finanzas.view');

        Route::put('/cuotas/{cuota}/bitacora', [FinanzasController::class, 'cuotaBitacoraUpsert'])
            ->middleware(PermissionMiddleware::class . ':finanzas.edit');

        Route::get('/resumen', [FinanzasController::class, 'resumenMensual'])
            ->middleware(PermissionMiddleware::class . ':finanzas.view');
    });

    /* ---------- Secretaría / Asistencias ---------- */
    Route::get('/secretaria/trazados', [SecretariaController::class, 'trazadosIndex'])
        ->middleware(PermissionMiddleware::class . ':secretaria.view');

    Route::post('/secretaria/trazados', [SecretariaController::class, 'trazadosStore'])
        ->middleware(PermissionMiddleware::class . ':secretaria.edit');

    Route::get('/secretaria/asistencias', [SecretariaController::class, 'asistenciasIndex'])
        ->middleware(PermissionMiddleware::class . ':asistencias.view');

    Route::post('/secretaria/asistencias', [SecretariaController::class, 'asistenciasStore'])
        ->middleware(PermissionMiddleware::class . ':asistencias.edit');

    Route::get('/secretaria/envios-gran-logia', [SecretariaController::class, 'enviosIndex'])
        ->middleware(PermissionMiddleware::class . ':secretaria.view');

    Route::post('/secretaria/envios-gran-logia', [SecretariaController::class, 'enviosStore'])
        ->middleware(PermissionMiddleware::class . ':secretaria.edit');

    /* ---------- Limosnero / Hospitalario ---------- */
    Route::get('/limosnero/colectas', [LimosneroController::class, 'colectasIndex'])
        ->middleware(PermissionMiddleware::class . ':limosnero.view');

    Route::post('/limosnero/colectas', [LimosneroController::class, 'colectasStore'])
        ->middleware(PermissionMiddleware::class . ':limosnero.edit');

    Route::get('/hospitalario/donaciones', [LimosneroController::class, 'donacionesIndex'])
        ->middleware(PermissionMiddleware::class . ':hospitalario.view');

    Route::post('/hospitalario/donaciones', [LimosneroController::class, 'donacionesStore'])
        ->middleware(PermissionMiddleware::class . ':hospitalario.edit');

    /* ---------- Tesorería ---------- */
    Route::prefix('tesoreria')->group(function () {

        Route::get('/pagos', [TesoreriaController::class, 'pagosIndex'])
            ->middleware(PermissionMiddleware::class . ':tesoreria.view');

        Route::post('/pagos', [TesoreriaController::class, 'pagosStore'])
            ->middleware(PermissionMiddleware::class . ':tesoreria.edit');

        Route::get('/pagos/{pago}/recibo', [TesoreriaController::class, 'reciboShow'])
            ->middleware(PermissionMiddleware::class . ':tesoreria.view');

        Route::get('/egresos', [TesoreriaController::class, 'egresosIndex'])
            ->middleware(PermissionMiddleware::class . ':tesoreria.view');

        Route::post('/egresos', [TesoreriaController::class, 'egresosStore'])
            ->middleware(PermissionMiddleware::class . ':tesoreria.edit');

        Route::get('/categorias-egreso', [TesoreriaController::class, 'categoriasIndex'])
            ->middleware(PermissionMiddleware::class . ':tesoreria.view');

        Route::post('/categorias-egreso', [TesoreriaController::class, 'categoriasStore'])
            ->middleware(PermissionMiddleware::class . ':tesoreria.edit');

        Route::get('/resumen', [TesoreriaController::class, 'resumen'])
            ->middleware(PermissionMiddleware::class . ':tesoreria.view');
    });

    Route::get('/miembros', [MiembroController::class, 'index']);
 
/* ---------- Miembros (gestión — requiere miembros.manage) ---------- */
Route::post('/miembros', [MiembroController::class, 'store'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');
 
Route::get('/miembros/roles', [MiembroController::class, 'roles'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');
 
Route::get('/miembros/usuarios', [MiembroController::class, 'usuariosIndex'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');
 
Route::get('/miembros/{miembro}', [MiembroController::class, 'show'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');
 
Route::post('/miembros/{miembro}', [MiembroController::class, 'update'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');
 
Route::delete('/miembros/{miembro}', [MiembroController::class, 'destroy'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');
 
Route::post('/miembros/{miembro}/restaurar', [MiembroController::class, 'restaurar'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');
 
Route::post('/miembros/{miembro}/crear-usuario', [MiembroController::class, 'crearUsuario'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');
 
Route::put('/miembros/usuarios/{user}/rol', [MiembroController::class, 'cambiarRol'])
    ->middleware(PermissionMiddleware::class . ':miembros.manage');

}); // ← cierra auth:sanctum