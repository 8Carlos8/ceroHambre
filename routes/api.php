<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlimentoController;
use App\Http\Controllers\DonanteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Usuario
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/update', [UserController::class, 'update']);
Route::post('/delete', [UserController::class, 'delete']);
Route::post('/verUsuario', [UserController::class, 'verUsuario']);
Route::post('/listaUsuarios', [UserController::class, 'listaUsuarios']);
Route::post('/verificarCorreo', [UserController::class, 'verificarCorreo']);
Route::post('/solicitarContraseña', [UserController::class, 'solicitarContraseña']);
Route::post('/cambiarContraseña', [UserController::class, 'cambiarContraseña']);

// Ruta para verificar el correo (sin autenticación)
Route::get('/verify-email/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])  // Aquí se agrega 'auth'
    ->name('verification.verify');

//Donante
Route::post('/donante/update', [DonanteController::class, 'update']);
Route::post('/donante/delete', [DonanteController::class, 'delete']);
Route::post('/donante/verDonante', [DonanteController::class, 'verDonante']);
Route::post('/donante/verDonantes', [DonanteController::class, 'verDonantes']);

//Alimento
Route::post('/alimento/register', [AlimentoController::class, 'register']);
Route::post('/alimento/update', [AlimentoController::class, 'update']);
Route::post('/alimento/delete', [AlimentoController::class, 'delete']);
Route::post('/alimento/verAlimento', [AlimentoController::class, 'verAlimento']);
Route::post('/alimento/verAlimentos', [AlimentoController::class, 'verAlimentos']);
Route::post('/alimento/verAlimentoDonador', [AlimentoController::class, 'verAlimentoDonador']);

//Pedido
Route::post('/pedido/register', [PedidoController::class, 'register']);
Route::post('/pedido/update', [PedidoController::class, 'update']);
Route::post('/pedido/delete', [PedidoController::class, 'delete']);
Route::post('/pedido/cambiarEstado', [PedidoController::class, 'cambiarEstado']);
Route::post('/pedido/verPedido', [PedidoController::class, 'verPedido']);
Route::post('/pedido/verPedidos', [PedidoController::class, 'verPedidos']);
Route::post('/pedido/verPedidoUsuario', [PedidoController::class, 'verPedidoUsuario']);
Route::post('/pedido/verPedidosAlimento', [PedidoController::class, 'verPedidosAlimento']);
Route::post('/pedido/verPedidosEstado', [PedidoController::class, 'verPedidosEstado']);
Route::post('/pedido/verPedidosFecha', [PedidoController::class, 'verPedidosFecha']);
Route::post('/pedido/verPedidosRangoFecha', [PedidoController::class, 'verPedidosRangoFecha']);
Route::post('/pedido/verPedidosCantidad', [PedidoController::class, 'verPedidosCantidad']);
Route::post('/pedido/verPedidosResenia', [PedidoController::class, 'verPedidosResenia']);
Route::post('/pedido/agregarResenia', [PedidoController::class, 'agregarResenia']);