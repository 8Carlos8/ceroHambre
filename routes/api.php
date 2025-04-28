<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlimentoController;
use App\Http\Controllers\DonanteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\UserController;
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

//Pedido
Route::post('/pedido/register', [PedidoController::class, 'register']);
Route::post('/pedido/update', [PedidoController::class, 'update']);
Route::post('/pedido/delete', [PedidoController::class, 'delete']);
Route::post('/pedido/cambiarEstado', [PedidoController::class, 'cambiarEstado']);
Route::post('/pedido/verPedido', [PedidoController::class, 'verPedido']);
Route::post('/pedido/verPedidos', [PedidoController::class, 'verPedidos']);
Route::post('/pedido/vePedidoUsuario', [PedidoController::class, 'verPedidoUsuario']);
Route::post('/pedido/verPedidosAlimento', [PedidoController::class, 'verPedidosAlimento']);
Route::post('/pedido/verPedidosEstado', [PedidoController::class, 'verPedidosEstado']);
Route::post('/pedido/verPedidosFecha', [PedidoController::class, 'verPedidosFecha']);
Route::post('/pedido/verPedidosRangoFecha', [PedidoController::class, 'verPedidosRangoFecha']);
Route::post('/pedido/verPedidosCantidad', [PedidoController::class, 'verPedidosCantidad']);
Route::post('/pedido/verPedidosResenia', [PedidoController::class, 'verPedidosResenia']);




