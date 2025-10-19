@extends('dashboard.layouts.vertical', ['title' => 'Sync Settings'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Data Sync', 'title' => 'Sync Settings'])

    <div class="row">
        <div class="col-lg-8">
            <!-- General Settings -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">General Settings</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Note:</strong> These settings are configured in your Laravel environment and queue configuration.
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Queue Driver</label>
                        <p class="mb-0">
                            <code>{{ config('queue.default') }}</code>
                        </p>
                        <small class="text-muted">Current queue connection being used for sync jobs</small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Queue Name</label>
                        <p class="mb-0">
                            <code>default</code>
                        </p>
                        <small class="text-muted">Queue where sync jobs are dispatched</small>
                    </div>

                    <hr>

                    <div class="mb-0">
                        <label class="text-muted small">Job Timeout</label>
                        <p class="mb-0">
                            <code>300 seconds (5 minutes)</code>
                        </p>
                        <small class="text-muted">Maximum time a sync job can run before timing out</small>
                    </div>
                </div>
            </div>

            <!-- Scheduler Status -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Scheduler Status</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        <strong>Important:</strong> Make sure the Laravel Scheduler is running via Cron.
                    </div>

                    <p class="mb-3">Add this Cron entry to your server:</p>
                    <pre class="bg-light p-3 rounded"><code>* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1</code></pre>

                    <p class="mt-3 mb-2">The scheduler will run these commands:</p>
                    <ul>
                        <li><code>sync:process-scheduled</code> - Every minute (checks for due schedules)</li>
                        <li><code>sync:reset-daily-counters</code> - Daily at midnight (resets run counters)</li>
                    </ul>
                </div>
            </div>

            <!-- Queue Worker -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Queue Worker</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Note:</strong> Make sure a queue worker is running to process jobs.
                    </div>

                    <p class="mb-2">Start a queue worker manually:</p>
                    <pre class="bg-light p-3 rounded"><code>php artisan queue:work</code></pre>

                    <p class="mt-3 mb-2">Or use Supervisor for production (recommended):</p>
                    <pre class="bg-light p-3 rounded"><code>[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-your-project/storage/logs/worker.log</code></pre>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('dashboard.sync.schedules.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Create New Schedule
                        </a>
                        <a href="{{ route('dashboard.sync.schedules.index') }}" class="btn btn-light">
                            <i class="ti ti-calendar me-1"></i> View All Schedules
                        </a>
                        <a href="{{ route('dashboard.sync.logs.index') }}" class="btn btn-light">
                            <i class="ti ti-file-text me-1"></i> View Sync Logs
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">System Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Laravel Version</small>
                        <p class="mb-0"><strong>{{ app()->version() }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">PHP Version</small>
                        <p class="mb-0"><strong>{{ PHP_VERSION }}</strong></p>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">Database</small>
                        <p class="mb-0"><strong>{{ config('database.default') }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Documentation -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Documentation</h4>
                </div>
                <div class="card-body">
                    <p class="mb-3">Learn more about the sync system:</p>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="ti ti-book me-2 text-primary"></i>
                            <a href="https://laravel.com/docs/queues" target="_blank">Laravel Queues</a>
                        </li>
                        <li class="mb-2">
                            <i class="ti ti-book me-2 text-primary"></i>
                            <a href="https://laravel.com/docs/scheduling" target="_blank">Laravel Scheduler</a>
                        </li>
                        <li class="mb-0">
                            <i class="ti ti-book me-2 text-primary"></i>
                            <a href="https://laravel.com/docs/supervisor" target="_blank">Supervisor Configuration</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

