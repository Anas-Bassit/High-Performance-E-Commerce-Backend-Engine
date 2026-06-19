<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductService
{
    private const POPULAR_PRODUCTS_CACHE_KEY = 'products:popular';
    private const POPULAR_PRODUCTS_CACHE_TTL = 60;

    public function popularNoCache(): array
    {
        $start = microtime(true);

        $products = Product::query()
            ->orderByDesc('sold_count')
            ->limit(10)
            ->get();

        $durationMs = round((microtime(true) - $start) * 1000, 2);

        return [
            'source' => 'database',
            'cached' => false,
            'duration_ms' => $durationMs,
            'products' => $products,
        ];
    }

    public function popular(): array
    {
        $start = microtime(true);

        $wasCached = Cache::has(self::POPULAR_PRODUCTS_CACHE_KEY);

        $products = Cache::remember(
            self::POPULAR_PRODUCTS_CACHE_KEY,
            self::POPULAR_PRODUCTS_CACHE_TTL,
            function () {
                Log::info('Cache miss: loading popular products from database');

                return Product::query()
                    ->orderByDesc('sold_count')
                    ->limit(10)
                    ->get();
            }
        );

        $durationMs = round((microtime(true) - $start) * 1000, 2);

        return [
            'source' => $wasCached ? 'redis_cache' : 'database_then_cached',
            'cached' => $wasCached,
            'cache_key' => self::POPULAR_PRODUCTS_CACHE_KEY,
            'ttl_seconds' => self::POPULAR_PRODUCTS_CACHE_TTL,
            'duration_ms' => $durationMs,
            'products' => $products,
        ];
    }

    public function clearPopularCache(): array
    {
        Cache::forget(self::POPULAR_PRODUCTS_CACHE_KEY);

        return [
            'message' => 'Popular products cache cleared successfully',
            'cache_key' => self::POPULAR_PRODUCTS_CACHE_KEY,
        ];
    }
}
