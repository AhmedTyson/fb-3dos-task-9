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
            "email"=>["required", "email", "exists:users,email"],
            "password"=>["required", "confirmed", "min:8"],
            "token"=>["required"]
        ]);
        // 2. change user password
        $status = Password::reset($request->only("email", "password", "token"), 
        function(User $user, string $password){
            $user->update([
                "password" => Hash::make($password),
            ]);
        });

        // 3. return response
        return response()->json([
            "message"=> $status == Password::PASSWORD_RESET
                ? 200
                : 422
        ]);
    }
}