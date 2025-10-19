@if(!auth()->user()->hasActiveSubscription())
<div class="alert alert-warning mb-3">
    <div class="d-flex align-items-center">
        <i class="ti ti-lock me-3 fs-4"></i>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1">Read-Only Mode</h5>
            <p class="mb-0">You're currently viewing in read-only mode. Subscribe to unlock full features and start managing your data.</p>
        </div>
        <div>
            <a href="{{ route('subscription.plans') }}" class="btn btn-primary">
                <i class="ti ti-crown me-1"></i>View Plans
            </a>
        </div>
    </div>
</div>
@endif

