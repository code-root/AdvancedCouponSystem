<!-- Footer Start -->
<footer class="footer">
    <div class="page-container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <script>document.write(new Date().getFullYear())</script> © Advanced Coupon System · <span class="fw-bold text-uppercase text-reset fs-12">Code Root</span>
            </div>
            <div class="col-md-6">
                <div class="text-md-end footer-links d-none d-md-flex justify-content-md-end gap-3">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                        <i class="ti ti-dashboard me-1"></i> Dashboard
                    </a>
                    @can('view brokers')
                    <a href="{{ route('brokers.index') }}" class="text-decoration-none">
                        <i class="ti ti-building-store me-1"></i> Brokers
                    </a>
                    @endcan
                    @can('view campaigns')
                    <a href="{{ route('campaigns.index') }}" class="text-decoration-none">
                        <i class="ti ti-target me-1"></i> Campaigns
                    </a>
                    @endcan
                    @can('view coupons')
                    <a href="{{ route('coupons.index') }}" class="text-decoration-none">
                        <i class="ti ti-ticket me-1"></i> Coupons
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- end Footer -->
