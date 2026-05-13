<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RedisCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RedisCacheTest extends TestCase
{
    private RedisCache $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RedisCache();
        Cache::flush();
    }

    #[Test]
    public function non_get_requests_skip_cache(): void
    {
        $request = Request::create('/test', 'POST');
        $response = $this->middleware->handle($request, fn () => response('created', 201), 'test_key');
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('created', $response->getContent());
    }

    #[Test]
    public function empty_key_skips_cache(): void
    {
        $request = Request::create('/test', 'GET');
        $response = $this->middleware->handle($request, fn () => response('ok'), '');
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function cache_miss_stores_and_returns_response(): void
    {
        $request = Request::create('/test', 'GET');
        $response = $this->middleware->handle($request, fn () => response('ok'), 'test_key');
        $this->assertEquals('MISS', $response->headers->get('X-Cache'));
        $this->assertEquals('ok', $response->getContent());
    }

    #[Test]
    public function cache_hit_returns_cached_response(): void
    {
        $cacheKey = 'test_key:' . md5('http://localhost/test');
        Cache::put($cacheKey, 'cached content', 300);
        $request = Request::create('/test', 'GET');
        $response = $this->middleware->handle($request, fn () => response('fresh'), 'test_key');
        $this->assertEquals('HIT', $response->headers->get('X-Cache'));
        $this->assertEquals('cached content', $response->getContent());
    }

    #[Test]
    public function unsuccessful_response_not_cached(): void
    {
        $request = Request::create('/test', 'GET');
        $response = $this->middleware->handle($request, fn () => response('error', 500), 'test_key');
        $this->assertEquals(500, $response->getStatusCode());
    }

    #[Test]
    public function uses_default_ttl_from_rules(): void
    {
        $request = Request::create('/dashboard', 'GET');
        $response = $this->middleware->handle($request, fn () => response('stats'), 'dashboard_stats');
        $this->assertEquals('MISS', $response->headers->get('X-Cache'));
    }

    #[Test]
    public function uses_fallback_ttl_for_unknown_key(): void
    {
        $request = Request::create('/test', 'GET');
        $response = $this->middleware->handle($request, fn () => response('data'), 'unknown_key');
        $this->assertEquals('MISS', $response->headers->get('X-Cache'));
    }
}
