<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\AdminUserCollection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('per_page', 15), 100);

        $users = User::where('role', UserRole::Customer)->paginate($limit);

        return response()->json([
            'message' => 'Customers fetched successfully',
            'data'    => new AdminUserCollection($users),
        ]);
    }
}
