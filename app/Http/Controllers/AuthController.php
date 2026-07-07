<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => UserRole::Customer,
            ]);
            $token = JWTAuth::fromUser($user);
        } catch (Exception $ex) {
            return response()->json([
                'message'   => 'Registration failed',
                'exception' => $ex->getMessage()
            ], 500);
        }
        
        return response()->json([
            'message' => 'User created successfully',
            'token'   => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        return response()->json([
            'message' => 'User logged in successfully',
            'token'   => $token,
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'message' => 'User profile fetched successfully',
            'user'    => $request->user(),
        ], 200);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'message' => 'User logged out successfully',
        ], 200);
    }
}
