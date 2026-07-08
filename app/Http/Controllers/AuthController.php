<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;


class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $user = User::create([
            "name"=>$request->name,
            "email"=>$request->email,
            "password"=>$request->password,
        ]);

        $token = Auth::login($user);

        return response()->json([
        "status" => "success",
        "message" => "User registered successfully",
        "user" => $user,
        "token" => $token
        ],201);
    }



    public function login(LoginRequest $request){
        
        $credentials = $request->validated();

        if (!$token = Auth::attempt($credentials)) {

            return response()->json([
                "status" => "error",
                "message" => "Invalid credentials"
            ], 401);
        }

        return response()->json([
            "status" => "success",
            "token" => $token
        ]);

    }

    public function logout(){

        Auth::logout();

        return response()->json([
            "status" => "success",
            "message" => "Logged out successfully"
        ]);
    }

    public function me()
    {
        return response()->json([
            "status" => "success",
            "user" => Auth::user()
        ]);
    }
    
}
