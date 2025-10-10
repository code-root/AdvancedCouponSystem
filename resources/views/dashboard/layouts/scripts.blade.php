<!-- jQuery FIRST -->



<!-- DataTables - Latest Version -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>   

<!-- Page Specific JS -->
<script  type="text/javascript" src="https://cdn.jsdelivr.net/npm/apexcharts"></script>



<!-- Vendor js -->
<script src="https://coderthemes.com/greeva/layouts/assets/js/vendor.min.js"></script>

<!-- App js -->
<script src="https://coderthemes.com/greeva/layouts/assets/js/app.js"></script>

<!-- Iconify Icons -->
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- Custom Scripts -->
<script>
    // Performance optimizations
    $(document).ready(function() {
        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Optimized DataTable initialization with better performance
        // Note: Individual pages should initialize their own DataTables
        // This prevents conflicts with custom configurations
      

        // Initialize Select2 with performance optimizations
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    delay: 250, // Debounce search requests
                    cache: true // Cache results
                }
            });
        }

        // Initialize dropdowns (Bootstrap 5)
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            document.querySelectorAll('.dropdown-toggle').forEach(function(el){
                new bootstrap.Dropdown(el);
            });
        }

        // Sidebar toggle
        const sidebarHide = document.getElementById('sidebar-hide');
        if (sidebarHide) {
            sidebarHide.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle('sidebar-collapse');
            });
        }

        // Safe initialization functions
        if (typeof layout_change !== 'undefined') {
            layout_change('light');
        }
        if (typeof layout_sidebar_change !== 'undefined') {
            layout_sidebar_change('light');
        }
        
        if (typeof layout_caption_change !== 'undefined') {
            layout_caption_change('true');
        }
        if (typeof layout_rtl_change !== 'undefined') {
            layout_rtl_change('false');
        }

        // Performance monitoring
        if (window.performance && window.performance.timing) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const timing = window.performance.timing;
                    const loadTime = timing.loadEventEnd - timing.navigationStart;
                    console.log('Page load time:', loadTime + 'ms');
                }, 0);
            });
        }
    });

    // Global AJAX error handler
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('AJAX Error:', error);
        if (xhr.status === 419) { // CSRF token mismatch
            Swal.fire({
                icon: 'error',
                title: 'Session Expired',
                text: 'Please refresh the page and try again.',
                confirmButtonText: 'OK'
            });
        }
    });

    // Debounce function for performance
    function debounce(func, wait, immediate) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
</script>

<!-- Custom Scripts -->
<script src="{{ asset('assets/js/datatables-optimized.js') }}"></script>

@stack('scripts') 