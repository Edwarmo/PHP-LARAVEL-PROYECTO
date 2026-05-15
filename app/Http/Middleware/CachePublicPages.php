<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicPages
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && !$request->user()) {
            $response->headers->set('Cache-Control', 'public, max-age=3600, s-maxage=86400');
        }

        return $response;
    }
}
