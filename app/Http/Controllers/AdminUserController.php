<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\AdminUserCollection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $limit = min((int) $request->query('per_page', 15), 100);

        $users = User::withCount('orders')
            ->where('role', UserRole::Customer)
            ->paginate($limit);

        return (new AdminUserCollection($users))
            ->response()
            ->header('X-Pagination-Total-Count', $users->total())
            ->header('X-Pagination-Current-Page', $users->currentPage())
            ->header('X-Pagination-Per-Page', $users->perPage())
            ->header('X-Pagination-Last-Page', $users->lastPage());
    }
}
