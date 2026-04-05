<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Body: { email, password }
     * Respuesta: { user, token }
     */
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required','string'],
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    /** @var \App\Models\User $user */
    $user  = $request->user();
    $token = $user->createToken('spa')->plainTextToken;

    $roles = $user->roles()->pluck('name'); // ["superadministrador", ...]
    $perms = $user->getAllPermissions()->pluck('name'); // ["finanzas.view", "finanzas.edit", ...]

    return response()->json([
        'user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'roles' => $roles,
            'permissions' => $perms,
        ],
        'token' => $token,
    ]);
}
    /**
     * POST /api/auth/logout (auth:sanctum)
     */
    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        // elimina solo el token actual
        $user->currentAccessToken()?->delete();

        return response()->json(['message' => 'ok'], 200);
    }

    /**
     * GET /api/auth/me (auth:sanctum)
     */
public function me(Request $request)
{
    $user = $request->user();
    return response()->json([
        'id'    => $user->id,
        'name'  => $user->name,
        'email' => $user->email,
        'roles' => $user->roles()->pluck('name'),
        'permissions' => $user->getAllPermissions()->pluck('name'),
    ]);
}
}
