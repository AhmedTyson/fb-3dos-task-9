<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::where('role', UserRole::Customer)->get();

        return response()->json([
            'users' => $users
        ]);
    }
}