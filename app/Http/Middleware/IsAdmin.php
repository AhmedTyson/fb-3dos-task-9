<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->userOrFail();

        if ($user->role !== UserRole::Admin) {
            return response()->json(['message' => 'Forbidden. Admin access required.'], 403);
        }

        return $next($request);
    }
}
