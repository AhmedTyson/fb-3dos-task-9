<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name'     => $request->validated('name'),
                'email'    => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'role'     => UserRole::Customer,
            ]);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] !== 1062) {
                throw $e;
            }

            return response()->json([
                'message' => 'Email already taken',
                'data'    => null,
            ], 422);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User created successfully',
            'data'    => ['token' => $token],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $token = JWTAuth::attempt($request->validated());

        if (!$token) {
            return response()->json([
                'message' => 'Invalid email or password',
                'data'    => null,
            ], 401);
        }

        return response()->json([
            'message' => 'User logged in successfully',
            'data'    => ['token' => $token],
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'message' => 'User profile fetched successfully',
            'data'    => new UserResource($request->user()),
        ], 200);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'message' => 'User logged out successfully',
            'data'    => null,
        ], 200);
    }
}
