<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donante;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;


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
            'logo' => 'nullable|file|image|max:5000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
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

            $fotoUrl = null;
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $nombreLogo = time() . '_' . $logo->getClientOriginalName();
                $logo->storeAs('public/imagenes/logos', $nombreLogo);
                $fotoUrl = url('storage/imagenes/logos/' . $nombreLogo);
            }

            Donante::create([
                'id_usuario' => $user->id,
                'tipo_asociacion' => $request->tipo_asociacion,
                'logo' => $fotoUrl,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        }

        // Generar código de verificación
        $verificationCode = rand(100000, 999999);

        // Guardar el código de verificación en la base de datos
        $user->codigo_verificacion = $verificationCode;
        $user->save();
        
        // Enviar correo con el código de verificación
        $user->notify(new VerifyEmailNotification($verificationCode));

        //Regresa una respuesta exitosa
        return response()->json(['message' => 'Usuario registrado exitosamente. Se ha enviado un correo para verificar tu cuenta.', 'user' => $user], 201);
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

                // Antes de subir la nueva
                if ($donante->logo) {
                    $path = str_replace(url('storage'), 'public', $donante->logo);
                    Storage::delete($path);
                }
                
                if ($request->hasFile('logo')) {
                    $logo = $request->file('logo');
                    $nombreLogo = time() . '_' . $logo->getClientOriginalName();
                    $logo->storeAs('public/imagenes/logos', $nombreLogo);
                    $donante->logo = url('storage/imagenes/logos/' . $nombreLogo);
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
        // Validar los datos recibidos
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ]);

        // Buscar el usuario por correo
        $user = User::where('correo', $request->correo)->first();

        // Verifica si el usuario existe y si la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Crear un token de acceso
        $token = $user->createToken('token')->plainTextToken;

        // Armar la respuesta
        $response = [
            'token' => $token,
            'id' => $user->id
        ];

        // Si es rol 2 (donante), buscar el ID del donante
        if ($user->rol == 2) {
            $donante = \App\Models\Donante::where('id_usuario', $user->id)->first();
            $response['id_donante'] = $donante ? $donante->id : null;
        }

        return response()->json($response);
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

    public function verificarCorreo(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric',
            'codigo_verificacion' => 'required|numeric',
        ]);

        // Buscar el usuario por ID
        $user = User::find($request->id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        // Verificar si el correo ya está verificado
        if ($user->email_verified_at) {
            return response()->json(['message' => 'El correo ya está verificado.'], 200);
        }

        // Verificar el código de verificación
        if ($user->codigo_verificacion != $request->codigo_verificacion) {
            return response()->json(['message' => 'Código de verificación incorrecto.'], 400);
        }

        // Código correcto, verificar el correo
        $user->email_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Correo verificado con éxito.'], 200);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'codigo_verificacion' => 'required|numeric',
        ]);

        $user = User::where('correo', $request->correo)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        // Verificar el código almacenado en la base de datos
        if ($user->codigo_verificacion != $request->codigo_verificacion) {
            return response()->json(['message' => 'Código de verificación incorrecto.'], 400);
        }

        //dd($user);  // Muestra los datos del usuario después de la actualización
        //dd($user->codigo_verificacion, $request->codigo_verificacion);

        // Limpiar el código de verificación
        $user->codigo_verificacion = null;
        $user->save();

        return response()->json(['message' => 'Correo verificado exitosamente.'], 200);
    }

    public function solicitarContraseña(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
        ]);

        // Buscar el usuario por email
        $user = User::where('correo', $request->correo)->first();

        if (!$user) {
            return response()->json(['message' => 'El correo no está registrado.'], 404);
        }

        // Generar un código de verificación de 6 dígitos
        $codigo = rand(100000, 999999);

        // Guardar el código en la base de datos (campo `codigo_verificacion`)
        $user->codigo_verificacion = $codigo;
        $user->save();

        // Enviar el correo con el código de recuperación
        Mail::raw("Tu código de recuperación es: $codigo", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Recuperación de contraseña');
        });

        return response()->json(['message' => 'Se ha enviado un correo con el código de recuperación.'], 200);
    }

    public function cambiarContraseña(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'codigo_verificacion' => 'required|numeric',
            'new_password' => 'required|string|min:8',
        ]);

        // Buscar el usuario
        $user = User::where('correo', $request->correo)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        // Validar que el código de verificación sea correcto
        if ($user->codigo_verificacion != $request->codigo_verificacion) {
            return response()->json(['message' => 'Código de verificación incorrecto.'], 400);
        }

        // Actualizar la contraseña
        $user->password = Hash::make($request->new_password);
        $user->codigo_verificacion = null; // Limpiar el código de verificación
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada con éxito.'], 200);
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
