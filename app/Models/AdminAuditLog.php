<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class AdminAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'status',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the admin that performed the action.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the model that was affected.
     */
    public function model()
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Scope for filtering by action.
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by admin.
     */
    public function scopeAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope for filtering by model.
     */
    public function scopeModel($query, $modelType, $modelId = null)
    {
        $query = $query->where('model_type', $modelType);
        
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted action description.
     */
    public function getFormattedActionAttribute()
    {
        $actions = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            'password_changed' => 'Changed Password',
            'role_assigned' => 'Assigned Role',
            'role_removed' => 'Removed Role',
            'permission_granted' => 'Granted Permission',
            'permission_revoked' => 'Revoked Permission',
            'settings_updated' => 'Updated Settings',
            'user_impersonated' => 'Impersonated User',
            'bulk_action' => 'Bulk Action',
        ];

        return $actions[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Get the changes summary.
     */
    public function getChangesSummaryAttribute()
    {
        if (!$this->old_values || !$this->new_values) {
            return $this->description;
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[] = "{$key}: {$oldValue} → {$newValue}";
            }
        }

        return implode(', ', $changes);
    }

    /**
     * Log an admin action.
     */
    public static function log($adminId, $action, $description = null, $model = null, $oldValues = null, $newValues = null, $request = null)
    {
        $data = [
            'admin_id' => $adminId,
            'action' => $action,
            'description' => $description,
        ];

        if ($model) {
            $data['model_type'] = get_class($model);
            $data['model_id'] = $model->id;
        }

        if ($oldValues) {
            $data['old_values'] = $oldValues;
        }

        if ($newValues) {
            $data['new_values'] = $newValues;
        }

        if ($request) {
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            $data['url'] = $request->fullUrl();
            $data['method'] = $request->method();
        }

        return static::create($data);
    }

    /**
     * Get recent activity for an admin.
     */
    public static function getRecentActivity($adminId, $limit = 10)
    {
        return static::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity statistics.
     */
    public static function getActivityStats($days = 30)
    {
        $startDate = now()->subDays($days);

        return [
            'total_actions' => static::where('created_at', '>=', $startDate)->count(),
            'actions_by_type' => static::where('created_at', '>=', $startDate)
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'actions_by_admin' => static::where('created_at', '>=', $startDate)
                ->with('admin:id,name')
                ->selectRaw('admin_id, COUNT(*) as count')
                ->groupBy('admin_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->admin->name => $item->count];
                }),
            'daily_activity' => static::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
        ];
    }
}
