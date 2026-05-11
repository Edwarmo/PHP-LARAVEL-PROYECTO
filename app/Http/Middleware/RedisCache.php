<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RedisCache
{
    protected array $rules = [
        'user_sessions' => 86400,    // 24h
        'menu_cache'    => 3600,     // 1h
        'recent_orders'  => 1800,    // 30m
        'dashboard_stats' => 300,    // 5m
    ];

    public function handle(Request $request, Closure $next, string $key = '', int $ttl = null): Response
    {
        if (!$key || $request->method() !== 'GET') {
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
