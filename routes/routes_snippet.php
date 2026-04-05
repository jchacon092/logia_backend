<?php
// ─────────────────────────────────────────────────────────────────────────────
// AGREGAR al inicio de routes/api.php (en los use statements):
// ─────────────────────────────────────────────────────────────────────────────
 use App\Http\Controllers\TesoreriaController;
 use Spatie\Permission\Middleware\PermissionMiddleware;
use Illuminate\Support\Facades\Route;
// ─────────────────────────────────────────────────────────────────────────────
// AGREGAR dentro del grupo Route::middleware('auth:sanctum')->group(...)
// ─────────────────────────────────────────────────────────────────────────────
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
