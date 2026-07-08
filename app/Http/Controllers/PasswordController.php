<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordController extends Controller
{
    

    /**
     * 1. endpoint forgetpassword email (send reset link)
     * 2. reset password change -> change password
     */


    public function forgetPassword(Request $request){
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);
        // generate rest link, send via email
        $status= Password::sendResetLink($request->only('email'));
        if($status==Password::RESET_LINK_SENT){
            return response()->json(["message"=>$status]);
        }
        return response()->json(["message"=>$status],422);
    }


    public function resetPassword(Request $request){
        $request->validate([
            "email"    => ["required", "email", "exists:users,email"],
            "password" => ["required", "min:8"],
            "token"    => ["required"]
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if token is valid
        if (!Password::broker()->tokenExists($user, $request->token)) {
            return response()->json([
                "message" => "passwords.token"
            ], 422);
        }

        // 2. change user password
        $user->forceFill([
            "password"       => Hash::make($request->password),
            "remember_token" => Str::random(60),
        ])->save();

        // Invalidate old token
        Password::broker()->deleteToken($user);

        // 3. return response
        return response()->json([
            "message" => 200
        ]);
    }
}