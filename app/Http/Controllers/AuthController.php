<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request){
        try {
            $validated = $request->validate([
                "name" => 'required|string|max:50',
                "email" => 'required|email|unique:users,email',
                "password" => 'required|string|min:8'
            ]);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => UserRole::Customer,
            ]);
            $token = auth('api')->login($user);
        } catch (Exception $ex) {
            return response()->json(["exception" => $ex->getMessage()]);
        }
        return response()->json([
            'message' => "user created",
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        return response()->json([
            'message' => 'user logged in successfully',
            'token'   => $token,
        ]);
    }

    public function me()
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'user'    => $user,
        ]);
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'User Logged out Successfully',
        ]);
    }
}
