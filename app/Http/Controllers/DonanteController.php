<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donante;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Alimento;

class DonanteController extends Controller
{
    //Función para actualizar el donante
    public function update(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $donante = Donante::with('usuario')->find($request->input('id'));

        if (!$donante) {
            return response()->json(['message' => 'Donante no encontrado'], 404);
        }

        $donante->update([
            'tipo_asociacion' => $request->input('tipo_asociacion'),
            'logo' => $request->input('logo')
        ]);

        // Actualizar datos del usuario relacionado
        if ($donante->usuario) {
            $donante->usuario->correo = $request->input('correo');
            $donante->usuario->nombre = $request->input('nombre');
            $donante->usuario->telefono = $request->input('telefono');
            $donante->usuario->direccion = $request->input('direccion');
            $donante->usuario->password = $request->input('password');
            $donante->usuario->rol = $request->input('rol');
            $donante->usuario->estado = $request->input('estado');

            if ($request->filled('password')) {
                $donante->usuario->password = Hash::make($request->input('password'));
            }

            $donante->usuario->save();
        }

        return response()->json(['message' => 'Donante actualizado exitosamente', 'donante' => $donante], 200);
    }

    //Función para eliminar el donante
    public function delete(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $donante = Donante::find($request->input('id'));

        if (!$donante) {
            return response()->json(['message' => 'Donante no encontrado'], 404);
        }
        
        Alimento::where('id_donante', $donante->id)->delete();

        $donante->delete();
        return response()->json(['message' => 'Donante eliminado exitosamente'], 200);
    }

    //Función para ver los datos del donante
    public function verDonante(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $donante = Donante::with('usuario')->find($request->input('id'));

        if (!$donante) {
            return response()->json(['message' => 'Donante no encontrado'], 404);
        }

        return response()->json(['donante' => $donante], 200);
    }

    //Función para ver todos los donantes
    public function verDonantes(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $donantes = Donante::with('usuario')->get();

        if ($donantes->isEmpty()) {
            return response()->json(['message' => 'No hay donantes registrados'], 404);
        }

        return response()->json(['donantes' => $donantes], 200);
    }

    private function getUserByToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken ? $accessToken->tokenable : null;
    }
}
