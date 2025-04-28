<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donante;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    //Función para el registro
    public function register(Request $request)
    {
        //Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'correo' => 'required|string|email|max:255|unique:users',
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:15',
            'direccion' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'rol' => 'required|integer',
            'estado' => 'required|integer',
            // Validar también campos de donante solo si aplica
            'tipo_asociacion' => 'nullable|string|max:255',
            'logo' => 'nullable|file|image|max:255',
        ]);

        //Si la validación falla, devolver un error
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        //Crear el usuario
        $user = User::create([
            'correo' => $request->correo,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'estado' => $request->estado,
        ]);

        if ($request->rol == 2) { // Si el rol es de donante

            $logoData = null;
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoData = file_get_contents($logo->getRealPath()); // Solo el contenido binario
            }


            Donante::create([
                'id_usuario' => $user->id,
                'tipo_asociacion' => $request->tipo_asociacion,
                'logo' => $logoData,
            ]);
        }

        //Regresa una respuesta exitosa
        return response()->json(['message' => 'Usuario registrado exitosamente', 'user' => $user], 201);
    }

    //Función para actualizar el usuario
    public function update(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        //Se busca el usuario por ID
        $user = User::find($request->input('id'));

        //Valida si el usuario existe
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        //Actualizar campos básicos del usuario
        $user->correo = $request->input('correo', $user->correo);
        $user->nombre = $request->input('nombre', $user->nombre);
        $user->telefono = $request->input('telefono', $user->telefono);
        $user->direccion = $request->input('direccion', $user->direccion);
        $user->rol = $request->input('rol', $user->rol);
        $user->estado = $request->input('estado', $user->estado);

        //Si llega password nueva, la encripta
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        // Si el usuario es donante, actualizar logo si se manda
        if ($user->rol == 2) {
            $donante = Donante::where('id_usuario', $user->id)->first();

            if ($donante) {
                if ($request->hasFile('logo')) {
                    $logo = $request->file('logo');
                    $logoData = file_get_contents($logo->getRealPath());
                    $donante->logo = $logoData;
                }

                if ($request->filled('tipo_asociacion')) {
                    $donante->tipo_asociacion = $request->input('tipo_asociacion');
                }

                $donante->save();
            }
        }
        //Regresa una respuesta exitosa
        return response()->json(['message' => 'Usuario actualizado exitosamente', 'user' => $user], 200);
    }

    //Función para eliminar el usuario
    public function delete(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        //Se busca el usuario por ID
        $user = User::find($request->input('id'));

        //Valida si el usuario existe
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        //Elimina el usuario
        $user->delete();
        //Regresa una respuesta exitosa
        return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
    }

    //Función para ver los datos del usuario
    public function verUsuario(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        //Validar ID
        $id = $request->input('id');

        //Busca el usuario por ID
        $user = User::find($id);
        //Valida si el usuario existe
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        //Regresa una respuesta con los datos del usuario
        return response()->json(['user' => $user], 200);
    }

    //Función para ver la lista de usuarios
    public function listaUsuarios(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        //Busca la lista de usuarios
        try {
            $users = User::all();
        } catch (\Exception $e) {
            //Si ocurre un error al obtener la lista de usuarios, regresa un error
            return response()->json(['message' => 'Error al obtener la lista de usuarios', 'error' => $e->getMessage()], 500);
        }

        //Regresa una respuesta con la lista de usuarios
        return response()->json(['users' => $users], 200);
    }

    //Función para el inicio de sesión
    public function login(Request $request)
    {
        //Validar los datos recibidos
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ]);

        //Buscar el usuario por correo
        $user = User::where('correo', $request->correo)->first();

        //Verifica si el usuario existe y si la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        //Crear un token de acceso y devolverlo con el ID del usuario
        $token = $user->createToken('token')->plainTextToken;
        return response()->json([
            'token' => $token,
            'id' => $user->id
        ]);
    }

    //Función para cerrar sesión
    public function logout(Request $request)
    {
        //Validar token
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        //Eliminar el token de acceso
        $accessToken = PersonalAccessToken::findToken($token);
        if ($accessToken) {
            $accessToken->delete();
        }

        //Regresa una respuesta exitosa
        return response()->json(['message' => 'Sesión cerrada exitosamente'], 200);
    }

    //Función para validar el token
    private function validateToken($token)
    {
        //Verifica si el token es válido
        $accessToken = PersonalAccessToken::findToken($token);
        // Verifica si el token existe y si pertenece a un usuario
        return $accessToken && $accessToken->tokenable_type === 'App\Models\User';
    }
}
