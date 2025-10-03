<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\EmpleadosController;
use App\Http\Controllers\MarcacionController;
use App\Http\Controllers\IotmonitorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Empresas
Route::get('/empresas', [EmpresasController::class, 'index']); 
Route::post('/empresas', [EmpresasController::class, 'store']);
Route::put('/empresas/{id}', [EmpresasController::class, 'update']);
Route::delete('/empresas/{id}', [EmpresasController::class, 'destroy']);
Route::post('/empresas/login', [EmpresasController::class, 'login']);

// Empleados
Route::get('/empleados', [EmpleadosController::class, 'index']); 
Route::get('/empleados', [EmpleadosController::class, 'search']);
Route::post('/empleados', [EmpleadosController::class, 'store']);
Route::put('/empleados/{id}', [EmpleadosController::class, 'update']);
Route::delete('/empleados/{id}', [EmpleadosController::class, 'destroy']);
Route::post('/empleados/login', [EmpleadosController::class, 'login']);

// Marcaciones
Route::get('/marcacion', [MarcacionController::class, 'search']); 
Route::get('/marcacion/dni/{dni}', [MarcacionController::class, 'getByDni']); 
Route::get('/marcacion/empresa/{empresa_id}', [MarcacionController::class, 'empleadosPorEmpresaConMarcacion']); // NUEVA: empleados con marcaciones por empresa
Route::post('/marcacion', [MarcacionController::class, 'store']); 
Route::put('/marcacion/{id}', [MarcacionController::class, 'update']);
Route::delete('/marcacion/{id}', [MarcacionController::class, 'destroy']);
Route::post('/marcacion/login', [MarcacionController::class, 'login']); 
Route::post('/marcarAsistencia', [MarcacionController::class, 'marcarAsistencia']);



Route::apiResource('iot', IotmonitorController::class);
Route::get('/iot/search', [IotmonitorController::class, 'search']); 

// Ruta de prueba general para verificar que la API responde correctamente
Route::get('/', function () {
    return response()->json(['message' => 'API de Asistencia con Firebase activa ✅']);
});

// Ruta para probar conexión con Firebase (usando el comando o una solicitud manual)
Route::get('/firebase/test', function () {
    try {
        $firebase = app('firebase.database');
        return response()->json(['message' => 'Conexión exitosa con Firebase 🔥']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error al conectar con Firebase', 'details' => $e->getMessage()], 500);
    }
});
