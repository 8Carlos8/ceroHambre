<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alimento;
use App\Models\Pedido;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class PedidoController extends Controller
{
    //Función para agregar un pedido
    public function register(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        // Validación de los datos del pedido
        $validator = Validator::make($request->all(), [
            'id_usuario' => 'required|integer',
            'id_alimento' => 'required|integer',
            'cantidad' => 'required|integer',
            'estado' => 'required|string|max:255',
            'resenia' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $alimento = Alimento::find($request->id_alimento);

        if (!$alimento) {
            return response()->json(['message' => 'Alimento no encontrado'], 404);
        }
    
        if ($alimento->cantidad < $request->cantidad) {
            return response()->json(['message' => 'No hay suficiente cantidad disponible'], 400);
        }

        // Crear el pedido
        $pedido = Pedido::create([
            'id_usuario' => $request->id_usuario,
            'id_alimento' => $request->id_alimento,
            'cantidad' => $request->cantidad,
            'estado' => $request->estado,
            'resenia' => $request->resenia,
        ]);

        // Restar cantidad del alimento
        $alimento->cantidad -= $request->cantidad;
        $alimento->save();

        return response()->json(['message' => 'Pedido registrado exitosamente', 'pedido' => $pedido], 201);
    }

    //Función para actualizar un pedido
    public function update(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedido = Pedido::find($request->input('id'));

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $alimento = Alimento::find($pedido->id_alimento);

        if (!$alimento) {
            return response()->json(['message' => 'Alimento no encontrado'], 404);
        }

        // Regresar cantidad anterior
        $alimento->cantidad += $pedido->cantidad;

        // Verificar nueva cantidad disponible
        if ($alimento->cantidad < $request->cantidad) {
            return response()->json(['message' => 'No hay suficiente cantidad disponible para actualizar el pedido'], 400);
        }

        // Actualizar pedido
        $pedido->update($request->all());

        // Restar nueva cantidad
        $alimento->cantidad -= $request->cantidad;
        $alimento->save();

        $pedido->update($request->all());
        return response()->json(['message' => 'Pedido actualizado exitosamente', 'pedido' => $pedido], 200);
    }

    //Función para eliminar un pedido
    public function delete(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedido = Pedido::find($request->input('id'));

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }
        
        $alimento = Alimento::find($pedido->id_alimento);

        if ($alimento) {
            $alimento->cantidad += $pedido->cantidad;
            $alimento->save();
        }

        $pedido->delete();
        return response()->json(['message' => 'Pedido eliminado exitosamente'], 200);
    }

    //Función para cambiar el estado de un pedido
    public function cambiarEstado(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedido = Pedido::find($request->input('id'));

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $pedido->estado = $request->input('estado');
        $pedido->save();

        return response()->json(['message' => 'Estado del pedido actualizado exitosamente', 'pedido' => $pedido], 200);
    }

    //Función para ver los datos de un pedido
    public function verPedido(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedido = Pedido::with(['usuario', 'alimento'])->find($request->input('id'));

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        if ($pedido->alimento && $pedido->alimento->foto) {
            $pedido->alimento->foto = url('storage/fotos_alimentos/' . $pedido->alimento->foto);
        }

        $pedido->usuario->makeHidden(['password', 'remember_token', 'codigo_verificacion', 'email_verified_at']);

        return response()->json(['pedido' => $pedido], 200);
    }

    //Función para ver todos los pedidos
    public function verPedidos(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedidos = Pedido::with(['usuario', 'alimento'])->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos registrados'], 404);
        }
        
        // Ocultar campos en cada pedido
        foreach ($pedidos as $pedido) {
            if ($pedido->alimento) {
                if ($pedido->alimento && $pedido->alimento->foto) {
                    $pedido->alimento->foto = url('storage/fotos_alimentos/' . $pedido->alimento->foto);
                }
            }
            if ($pedido->usuario) {
                $pedido->usuario->makeHidden([
                    'password',
                    'remember_token',
                    'codigo_verificacion',
                    'email_verified_at'
                ]);
            }
        }

        return response()->json(['pedidos' => $pedidos], 200);
    }

    public function verPedidoUsuario(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);
        $id_usuario = $request->input('id_usuario');

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        // Validar si el id_usuario corresponde al usuario autenticado
        if ($user->id !== (int) $id_usuario) {
            return response()->json(['message' => 'No autorizado a ver los pedidos de este usuario'], 403);
        }

        $pedidos = Pedido::with(['usuario', 'alimento'])->where('id_usuario', $user->id)->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos registrados para este usuario'], 404);
        }
        
        // Ocultar campos en cada pedido
        foreach ($pedidos as $pedido) {
            if ($pedido->alimento) {
                if ($pedido->alimento && $pedido->alimento->foto) {
                    $pedido->alimento->foto = url('storage/fotos_alimentos/' . $pedido->alimento->foto);
                }
            }
            if ($pedido->usuario) {
                $pedido->usuario->makeHidden([
                    'password',
                    'remember_token',
                    'codigo_verificacion',
                    'email_verified_at'
                ]);
            }
        }

        return response()->json(['pedidos' => $pedidos], 200);
    }

    public function verPedidosAlimento(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedidos = Pedido::with(['usuario', 'alimento'])->where('id_alimento', $request->input('id_alimento'))->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos registrados para este alimento'], 404);
        }

        return response()->json(['pedidos' => $pedidos], 200);
    }

    public function verPedidosEstado(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedidos = Pedido::with(['usuario', 'alimento'])->where('estado', $request->input('estado'))->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos registrados con este estado'], 404);
        }

        return response()->json(['pedidos' => $pedidos], 200);
    }

    public function verPedidosFecha(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedidos = Pedido::with(['usuario', 'alimento'])->whereDate('created_at', $request->input('fecha'))->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos registrados para esta fecha'], 404);
        }

        return response()->json(['pedidos' => $pedidos], 200);
    }

    public function verPedidosRangoFecha(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedidos = Pedido::with(['usuario', 'alimento'])
            ->whereBetween('created_at', [$request->input('fecha_inicio'), $request->input('fecha_fin')])
            ->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos registrados en este rango de fechas'], 404);
        }

        return response()->json(['pedidos' => $pedidos], 200);
    }

    public function verPedidosCantidad(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedidos = Pedido::with(['usuario', 'alimento'])->where('cantidad', $request->input('cantidad'))->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos registrados con esta cantidad'], 404);
        }

        return response()->json(['pedidos' => $pedidos], 200);
    }

    public function verPedidosResenia(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pedidos = Pedido::with(['usuario', 'alimento'])->where('resenia', 'LIKE', '%' . $request->input('resenia') . '%')->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos registrados con esta reseña'], 404);
        }

        return response()->json(['pedidos' => $pedidos], 200);
    }

    // Función para agregar o actualizar una reseña a un pedido existente
    public function agregarResenia(Request $request)
    {
        // Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        // Validar parámetros
        $validator = Validator::make($request->all(), [
            'id_pedido' => 'required|integer',
            'resenia' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pedido = Pedido::find($request->input('id_pedido'));

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        // Verifica que el pedido sea del usuario autenticado
        if ($pedido->id_usuario !== $user->id) {
            return response()->json(['message' => 'No autorizado para modificar este pedido'], 403);
        }

        $pedido->resenia = $request->input('resenia');
        $pedido->save();

        return response()->json(['message' => 'Reseña agregada correctamente', 'pedido' => $pedido], 200);
    }

    private function getUserByToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken ? $accessToken->tokenable : null;
    }
}
