<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'failed_login' => [
                    'message' => 'Las credenciales no coinciden con nuestros registros.'
                ]
            ]);
        }

        return response()->json([
            'successful_login' => [
                'token' => $user->createToken('auth_token')->plainTextToken,
            ]
        ]);
    }

    public function loginUI()
    {
        return response()->json([
            'displaying_login' => [
                'action' => 'api/login',
                'method' => 'POST',
                'is_page' => true,
                'is_modal' => true,
                'email' => [
                    'label' => 'Correo:',
                    'type' => 'email'
                ],
                'password' => [
                    'label' => 'Contraseña:',
                    'type' => 'password'
                ],
                'login' => [
                    'text' => 'Iniciar sesión',
                    'type' => 'submit'
                ],
                'register' => [
                    'text' => 'Registrarse',
                    'type' => 'button',
                ]
            ],
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
