<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditAdminActions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only audit for admin users
        if (Auth::guard('admin')->check()) {
            $this->auditAction($request, $response);
        }

        return $response;
    }

    /**
     * Audit the admin action.
     */
    private function auditAction(Request $request, $response)
    {
        try {
            $admin = Auth::guard('admin')->user();
            $method = $request->method();
            $url = $request->fullUrl();
            $route = $request->route();

            // Skip certain routes and methods
            if ($this->shouldSkipAudit($request, $response)) {
                return;
            }

            $action = $this->determineAction($method, $route);
            $description = $this->generateDescription($action, $request, $route);

            // Log the action
            AdminAuditLog::log(
                $admin->id,
                $action,
                $description,
                null, // model
                null, // old values
                null, // new values
                $request
            );
        } catch (\Exception $e) {
            // Don't let audit failures break the application
            \Log::error('Audit logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Determine if we should skip auditing this request.
     */
    private function shouldSkipAudit(Request $request, $response)
    {
        $skipRoutes = [
            'admin.audit-logs.index',
            'admin.audit-logs.show',
            'admin.audit-logs.export',
        ];

        $skipMethods = ['GET'];
        $skipPaths = [
            '/admin/notifications',
            '/admin/broadcasting',
        ];

        // Skip GET requests by default (except important ones)
        if (in_array($request->method(), $skipMethods)) {
            $importantRoutes = [
                'admin.users.show',
                'admin.subscriptions.show',
                'admin.networks.show',
            ];
            
            if (!in_array($request->route()?->getName(), $importantRoutes)) {
                return true;
            }
        }

        // Skip certain routes
        if (in_array($request->route()?->getName(), $skipRoutes)) {
            return true;
        }

        // Skip certain paths
        foreach ($skipPaths as $path) {
            if (str_starts_with($request->path(), trim($path, '/'))) {
                return true;
            }
        }

        // Skip AJAX requests for non-critical actions
        if ($request->ajax() && !$this->isImportantAjaxAction($request)) {
            return true;
        }

        return false;
    }

    /**
     * Check if this is an important AJAX action that should be audited.
     */
    private function isImportantAjaxAction(Request $request)
    {
        $importantActions = [
            'admin.subscriptions.cancel',
            'admin.subscriptions.upgrade',
            'admin.subscriptions.manual-activate',
            'admin.subscriptions.extend',
            'admin.users.toggle-status',
            'admin.networks.toggle-status',
        ];

        return in_array($request->route()?->getName(), $importantActions);
    }

    /**
     * Determine the action type based on HTTP method and route.
     */
    private function determineAction(string $method, $route)
    {
        $routeName = $route?->getName() ?? '';

        // Map specific routes to actions
        $actionMap = [
            'admin.login' => 'login',
            'admin.logout' => 'logout',
            'admin.profile.password' => 'password_changed',
            'admin.users.store' => 'created',
            'admin.users.update' => 'updated',
            'admin.users.destroy' => 'deleted',
            'admin.subscriptions.cancel' => 'subscription_cancelled',
            'admin.subscriptions.upgrade' => 'subscription_upgraded',
            'admin.subscriptions.manual-activate' => 'subscription_activated',
            'admin.subscriptions.extend' => 'subscription_extended',
            'admin.settings.general.update' => 'settings_updated',
            'admin.settings.smtp.update' => 'settings_updated',
        ];

        if (isset($actionMap[$routeName])) {
            return $actionMap[$routeName];
        }

        // Default action mapping based on HTTP method
        return match ($method) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'viewed',
        };
    }

    /**
     * Generate a human-readable description of the action.
     */
    private function generateDescription(string $action, Request $request, $route)
    {
        $routeName = $route?->getName() ?? '';
        $routeParams = $route?->parameters() ?? [];

        $descriptions = [
            'login' => 'Logged into admin panel',
            'logout' => 'Logged out of admin panel',
            'password_changed' => 'Changed password',
            'created' => $this->getModelDescription($routeName, $routeParams, 'created'),
            'updated' => $this->getModelDescription($routeName, $routeParams, 'updated'),
            'deleted' => $this->getModelDescription($routeName, $routeParams, 'deleted'),
            'viewed' => $this->getModelDescription($routeName, $routeParams, 'viewed'),
            'subscription_cancelled' => 'Cancelled a subscription',
            'subscription_upgraded' => 'Upgraded a subscription',
            'subscription_activated' => 'Manually activated a subscription',
            'subscription_extended' => 'Extended a subscription',
            'settings_updated' => 'Updated system settings',
        ];

        return $descriptions[$action] ?? "Performed action: {$action}";
    }

    /**
     * Get model-specific description.
     */
    private function getModelDescription(string $routeName, array $params, string $action)
    {
        $modelMap = [
            'admin.users' => 'user',
            'admin.subscriptions' => 'subscription',
            'admin.networks' => 'network',
            'admin.plans' => 'plan',
            'admin.countries' => 'country',
            'admin.campaigns' => 'campaign',
        ];

        foreach ($modelMap as $routePrefix => $modelName) {
            if (str_starts_with($routeName, $routePrefix)) {
                $modelId = $params['id'] ?? $params['user'] ?? $params['subscription'] ?? null;
                $identifier = $modelId ? " (ID: {$modelId})" : '';
                
                return ucfirst($action) . " {$modelName}{$identifier}";
            }
        }

        return ucfirst($action) . " resource";
    }
}

