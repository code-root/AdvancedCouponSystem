<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ErrorLog;
use Symfony\Component\HttpFoundation\Response;

class ErrorMonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log errors for 4xx and 5xx responses
        if ($response->getStatusCode() >= 400) {
            $this->logError($request, $response);
        }

        return $response;
    }

    /**
     * Log error details.
     */
    private function logError(Request $request, Response $response): void
    {
        try {
            $errorData = [
                'level' => $this->getErrorLevel($response->getStatusCode()),
                'message' => $this->getErrorMessage($response),
                'context' => $this->getContext($request),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'request_id' => $request->header('X-Request-ID', uniqid()),
                'extra_data' => $this->getExtraData($request, $response),
                'occurrence_count' => 1,
                'last_occurred_at' => now(),
            ];

            // Check if similar error exists
            $existingError = ErrorLog::where('message', $errorData['message'])
                ->where('url', $errorData['url'])
                ->where('method', $errorData['method'])
                ->where('is_resolved', false)
                ->first();

            if ($existingError) {
                $existingError->incrementOccurrence();
            } else {
                ErrorLog::create($errorData);
            }

            // Also log to Laravel's log system
            Log::error('HTTP Error', $errorData);

        } catch (\Exception $e) {
            // Fallback to Laravel's log system if our logging fails
            Log::error('Error monitoring failed', [
                'original_error' => $response->getStatusCode(),
                'monitoring_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get error level based on status code.
     */
    private function getErrorLevel(int $statusCode): string
    {
        return match(true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            default => 'info',
        };
    }

    /**
     * Get error message.
     */
    private function getErrorMessage(Response $response): string
    {
        $statusCode = $response->getStatusCode();
        $statusText = $response->getStatusCode() . ' ' . Response::$statusTexts[$statusCode] ?? 'Unknown Error';
        
        return "HTTP {$statusText}";
    }

    /**
     * Get request context.
     */
    private function getContext(Request $request): array
    {
        return [
            'route' => $request->route()?->getName(),
            'controller' => $request->route()?->getActionName(),
            'middleware' => $request->route()?->gatherMiddleware(),
            'parameters' => $request->route()?->parameters(),
            'query' => $request->query(),
            'headers' => $this->getFilteredHeaders($request),
            'input' => $this->getFilteredInput($request),
        ];
    }

    /**
     * Get filtered headers (remove sensitive data).
     */
    private function getFilteredHeaders(Request $request): array
    {
        $headers = $request->headers->all();
        $sensitiveHeaders = ['authorization', 'cookie', 'x-csrf-token', 'x-api-key'];
        
        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***'];
            }
        }
        
        return $headers;
    }

    /**
     * Get filtered input (remove sensitive data).
     */
    private function getFilteredInput(Request $request): array
    {
        $input = $request->all();
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($input[$field])) {
                $input[$field] = '***';
            }
        }
        
        return $input;
    }

    /**
     * Get extra data.
     */
    private function getExtraData(Request $request, Response $response): array
    {
        return [
            'response_size' => strlen($response->getContent()),
            'execution_time' => microtime(true) - LARAVEL_START,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
        ];
    }
}