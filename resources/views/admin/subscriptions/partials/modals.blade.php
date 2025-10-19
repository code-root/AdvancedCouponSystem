<!-- Cancel Subscription Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Reason for cancellation (optional)</label>
                        <textarea class="form-control" id="cancelReason" name="reason" rows="3" 
                                  placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Cancel Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upgrade Subscription Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upgrade Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="upgradeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="upgradePlan" class="form-label">Select New Plan</label>
                        <select class="form-select" id="upgradePlan" name="plan_id" required>
                            <option value="">Choose a plan...</option>
                            @foreach($plans ?? [] as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} - ${{ number_format($plan->price, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Upgrade Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extend Subscription Modal -->
<div class="modal fade" id="extendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Extend Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="extendForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="extendDays" class="form-label">Number of days to extend</label>
                        <input type="number" class="form-control" id="extendDays" name="days" 
                               min="1" max="365" required placeholder="Enter number of days">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Extend Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

