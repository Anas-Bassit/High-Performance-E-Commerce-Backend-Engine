<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoringMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $queries = [];

        DB::listen(function ($query) use (&$queries) {
            $queries[] = [
                'sql' => $query->sql,
                'time_ms' => $query->time,
            ];
        });

        /** @var Response $response */
        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $memoryUsageKb = round((memory_get_usage() - $startMemory) / 1024, 2);
        $dbTotalTimeMs = round(array_sum(array_column($queries, 'time_ms')), 2);

        Log::channel('performance')->info('HTTP request performance', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'memory_usage_kb' => $memoryUsageKb,
            'db_queries_count' => count($queries),
            'db_queries_total_time_ms' => $dbTotalTimeMs,
            'server_instance' => env('APP_INSTANCE', 'single-instance'),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return $response;
    }
}