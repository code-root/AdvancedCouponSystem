<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $startQueries = $this->getQueryCount();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $endQueries = $this->getQueryCount();

        $this->logPerformanceMetrics($request, $response, [
            'execution_time' => $endTime - $startTime,
            'memory_usage' => $endMemory - $startMemory,
            'peak_memory' => memory_get_peak_usage(true),
            'query_count' => $endQueries - $startQueries,
            'response_size' => strlen($response->getContent()),
        ]);

        return $response;
    }

    /**
     * Log performance metrics.
     */
    private function logPerformanceMetrics(Request $request, Response $response, array $metrics): void
    {
        // Only log slow requests or high memory usage
        if ($this->shouldLog($metrics)) {
            $data = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'execution_time' => round($metrics['execution_time'], 4),
                'memory_usage' => $this->formatBytes($metrics['memory_usage']),
                'peak_memory' => $this->formatBytes($metrics['peak_memory']),
                'query_count' => $metrics['query_count'],
                'response_size' => $this->formatBytes($metrics['response_size']),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
                'controller' => $request->route()?->getActionName(),
            ];

            Log::info('Performance Metrics', $data);

            // Store in database for analysis
            $this->storePerformanceMetrics($data);
        }
    }

    /**
     * Check if metrics should be logged.
     */
    private function shouldLog(array $metrics): bool
    {
        // Log if execution time > 2 seconds
        if ($metrics['execution_time'] > 2.0) {
            return true;
        }

        // Log if memory usage > 50MB
        if ($metrics['memory_usage'] > 50 * 1024 * 1024) {
            return true;
        }

        // Log if query count > 20
        if ($metrics['query_count'] > 20) {
            return true;
        }

        // Log if response size > 1MB
        if ($metrics['response_size'] > 1024 * 1024) {
            return true;
        }

        return false;
    }

    /**
     * Store performance metrics in database.
     */
    private function storePerformanceMetrics(array $data): void
    {
        try {
            DB::table('performance_metrics')->insert([
                'url' => $data['url'],
                'method' => $data['method'],
                'status_code' => $data['status_code'],
                'execution_time' => $data['execution_time'],
                'memory_usage' => $data['memory_usage'],
                'peak_memory' => $data['peak_memory'],
                'query_count' => $data['query_count'],
                'response_size' => $data['response_size'],
                'user_id' => $data['user_id'],
                'ip_address' => $data['ip_address'],
                'user_agent' => $data['user_agent'],
                'route' => $data['route'],
                'controller' => $data['controller'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store performance metrics', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Get current query count.
     */
    private function getQueryCount(): int
    {
        return count(DB::getQueryLog());
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}