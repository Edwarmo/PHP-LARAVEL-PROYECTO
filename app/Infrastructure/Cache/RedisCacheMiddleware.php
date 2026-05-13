<?php

namespace App\Infrastructure\Cache;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RedisCacheMiddleware
{
    protected array $rules = [
        'user_sessions' => 86400,
        'menu_cache'    => 3600,
        'recent_orders'  => 1800,
        'dashboard_stats' => 300,
    ];

    public function handle(Request $request, Closure $next, string $key = '', int $ttl = null): Response
    {
        if (! $key || $request->method() !== 'GET') {
            return $next($request);
        }

        $ttl ??= $this->rules[$key] ?? 300;
        $cacheKey = "{$key}:" . md5($request->fullUrl());

        if (Cache::has($cacheKey)) {
            return response(Cache::get($cacheKey))
                ->header('X-Cache', 'HIT')
                ->header('X-Cache-Key', $cacheKey);
        }

        $response = $next($request);
        $content = $response->getContent();

        if ($response->isSuccessful()) {
            Cache::put($cacheKey, $content, $ttl);
        }

        return $response->header('X-Cache', 'MISS');
    }
}
