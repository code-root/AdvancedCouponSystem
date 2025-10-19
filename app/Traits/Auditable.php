<?php

namespace App\Traits;

use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAuditEvent('created', 'Created ' . class_basename($model));
        });

        static::updated(function ($model) {
            $model->logAuditEvent('updated', 'Updated ' . class_basename($model), $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            $model->logAuditEvent('deleted', 'Deleted ' . class_basename($model));
        });
    }

    /**
     * Log an audit event.
     */
    public function logAuditEvent($action, $description = null, $oldValues = null, $newValues = null)
    {
        if (!Auth::guard('admin')->check()) {
            return;
        }

        AdminAuditLog::log(
            Auth::guard('admin')->id(),
            $action,
            $description,
            $this,
            $oldValues,
            $newValues,
            request()
        );
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return $this->morphMany(AdminAuditLog::class, 'model');
    }

    /**
     * Get recent audit logs for this model.
     */
    public function getRecentAuditLogs($limit = 10)
    {
        return $this->auditLogs()
            ->with('admin:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

