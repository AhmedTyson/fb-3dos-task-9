<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheJsonResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 15): Response
    {
        $format = strtolower($request->query('format', 'json'));
        if ($format !== 'json' || !$request->isMethod('GET')) {
            return $next($request);
        }

        $cacheKey = 'api_cache_' . md5($request->fullUrl() . '_' . ($request->user()?->id ?? 'guest'));

        $cached = Cache::get($cacheKey, false);
        if ($cached !== false) {
            return response()->json([
                'message' => $cached['message'] ?? '',
                'source'  => 'redis',
                'data'    => $cached['data'] ?? null,
            ]);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && $response instanceof JsonResponse) {
            $original = $response->getData(true);

            Cache::put($cacheKey, $original, now()->addMinutes($ttl));

            $response->setData([
                'message' => $original['message'] ?? '',
                'source'  => 'database',
                'data'    => $original['data'] ?? null,
            ]);
        }

        return $response;
    }
}
