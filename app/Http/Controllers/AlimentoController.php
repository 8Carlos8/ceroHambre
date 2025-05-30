<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Alimento;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

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
            'foto' => 'nullable|file|image|max:5000', // Tamaño máximo de 5MB
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

        $fotoUrl = null;
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = time() . '_' . $foto->getClientOriginalName();
            $foto->storeAs('public/imagenes', $nombreFoto);
            $fotoUrl = url('storage/imagenes/' . $nombreFoto);
        }

        $alimento = Alimento::create([
            'id_donante' => $request->id_donante,
            'nombre' => $request->nombre,
            'foto' => $fotoUrl,
            'descripcion' => $request->descripcion,
            'cantidad' => $request->cantidad,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'estado' => $request->estado,
        ]);

        return response()->json([
            'message' => 'Alimento registrado exitosamente', 
            'alimento' => $alimento
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

        // Antes de subir la nueva
        if ($alimento->foto) {
            $path = str_replace(url('storage'), 'public', $alimento->foto);
                Storage::delete($path);
        }

        // Si mandan nueva foto, la actualiza
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = time() . '_' . $foto->getClientOriginalName();
            $foto->storeAs('public/imagenes', $nombreFoto);
            $alimento->foto = url('storage/imagenes/' . $nombreFoto);
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

        // Ocultar campo binario original
        $alimento->makeHidden('foto');

        return response()->json([
            'alimento' => $alimento,
            'foto_url' => $alimento->foto
        ], 200);
    }

    //Función para ver todos los alimentos del donador
    public function verAlimentoDonador(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        $user = $this->getUserByToken($token);
        $donante = $request->input('id_donante');

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $alimentos = Alimento::with('donante')->where('id_donante', $donante)->get();

        if ($alimentos->isEmpty()) {
            return response()->json(['message' => 'No hay alimentos registrados'], 404);
        }

        // Mapear alimentos agregando imagen en base64 y ocultando el campo binario original
        $alimentosFormateados = $alimentos->map(function ($alimento) {
            return [
                'alimento' => $alimento,
                'foto_url' => $alimento->foto
            ];
        });

        return response()->json(['alimentos' => $alimentosFormateados], 200);
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

        // Mapear alimentos agregando imagen en base64 y ocultando el campo binario original
        $alimentosFormateados = $alimentos->map(function ($alimento) {
            $alimentoArray = $alimento->toArray();

            // Convertir todos los strings del alimento a UTF-8
            array_walk_recursive($alimentoArray, function (&$value) {
                if (is_string($value)) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            });

            return [
                'alimento' => $alimentoArray,
                'foto_url' => $alimento->foto
            ];
        });
        
        return response()->json(['alimentos' => $alimentosFormateados], 200);
    }

    private function getUserByToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken ? $accessToken->tokenable : null;
    }
}
