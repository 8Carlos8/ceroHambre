<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Alimento;
use Laravel\Sanctum\PersonalAccessToken;

class AlimentoController extends Controller
{
    //Función agregar alimento
    public function register(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $validator = Validator::make($request->all(), [
            'id_donante' => 'required|integer',
            'nombre' => 'required|string|max:255',
            'foto' => 'nullable|file|image|max:255',
            'descripcion' => 'required|string|max:255',
            'cantidad' => 'required|integer',
            'fecha_vencimiento' => 'required|date',
            'estado' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fotoData = null;
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $fotoData = file_get_contents($foto->getRealPath()); // Solo el contenido binario
        }

        $alimento = Alimento::create([
            'id_donante' => $request->id_donante,
            'nombre' => $request->nombre,
            'foto' => $fotoData,
            'descripcion' => $request->descripcion,
            'cantidad' => $request->cantidad,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'estado' => $request->estado,
        ]);

        return response()->json([
            'message' => 'Alimento registrado exitosamente', 
            'alimento' => $alimento->makeHidden('foto')
        ], 201);
    }

    //Función para actualizar alimento
    public function update(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $alimento = Alimento::find($request->input('id'));

        if (!$alimento) {
            return response()->json(['message' => 'Alimento no encontrado'], 404);
        }

        // Actualizar campos básicos del alimento
        $alimento->nombre = $request->input('nombre', $alimento->nombre);
        $alimento->descripcion = $request->input('descripcion', $alimento->descripcion);
        $alimento->cantidad = $request->input('cantidad', $alimento->cantidad);
        $alimento->fecha_vencimiento = $request->input('fecha_vencimiento', $alimento->fecha_vencimiento);
        $alimento->estado = $request->input('estado', $alimento->estado);

        // Si mandan nueva foto, la actualiza
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $fotoData = file_get_contents($foto->getRealPath());
            $alimento->foto = $fotoData;
        }

        $alimento->save();

        return response()->json(['message' => 'Alimento actualizado exitosamente', 'alimento' => $alimento], 200);
    }

    //Función para eliminar alimento
    public function delete(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $alimento = Alimento::find($request->input('id'));

        if (!$alimento) {
            return response()->json(['message' => 'Alimento no encontrado'], 404);
        }

        $alimento->delete();
        return response()->json(['message' => 'Alimento eliminado exitosamente'], 200);
    }

    //Función para ver los datos del alimento
    public function verAlimento(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $alimento = Alimento::with('donante')->find($request->input('id'));

        if (!$alimento) {
            return response()->json(['message' => 'Alimento no encontrado'], 404);
        }

        return response()->json(['alimento' => $alimento], 200);
    }

    //Función para ver todos los alimentos
    public function verAlimentos(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $alimentos = Alimento::with('donante')->get();

        if ($alimentos->isEmpty()) {
            return response()->json(['message' => 'No hay alimentos registrados'], 404);
        }

        return response()->json(['alimentos' => $alimentos], 200);
    }

    private function getUserByToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken ? $accessToken->tokenable : null;
    }
}
