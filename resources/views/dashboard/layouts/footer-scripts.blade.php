<!-- jquery -->
{{-- <script src="{{ URL::asset('assets/Admin/js/jquery-3.3.1.min.js') }}"></script> --}}
<!-- plugins-jquery -->
<script src="{{ URL::asset('assets/Admin/js/plugins-jquery.js') }}"></script>
<!-- plugin_path -->
<script>
    var plugin_path = 'js/';

    if (typeof $.fn.select2 !== 'undefined' && $('.select2').length) {
        $('.select2').select2({
            placeholder: "Select a patient",
            allowClear: true,
            templateResult: formatPatient,
            templateSelection: formatPatientSelection
        });
    } else {
        console.log("Select2 library not loaded or element not found");
    }

    // دالة تنسيق النتائج
    function formatPatient(patient) {
        if (!patient.id) return patient.text;
        const phone = $(patient.element).data('phone') || 'N/A';
        return $(`<span>${patient.text} <small class="text-muted">(${phone})</small></span>`);
    }

    // دالة تنسيق الShow عند الاختيار
    function formatPatientSelection(patient) {
        return patient.id ? patient.text.split(' (')[0] : patient.text;
    }

</script>

<!-- chart -->
<script src="{{ URL::asset('assets/Admin/js/chart-init.js') }}"></script>
<!-- calendar -->
<script src="{{ URL::asset('assets/Admin/js/calendar.init.js') }}"></script>
<!-- charts sparkline -->
<script src="{{ URL::asset('assets/Admin/js/sparkline.init.js') }}"></script>
<!-- charts morris -->
<script src="{{ URL::asset('assets/Admin/js/morris.init.js') }}"></script>
<!-- datepicker -->
<script src="{{ URL::asset('assets/Admin/js/datepicker.js') }}"></script>
<!-- sweetalert2 -->
<script src="{{ URL::asset('assets/Admin/js/sweetalert2.js') }}"></script>
<!-- toastr -->
@yield('js')
<script src="{{ URL::asset('assets/Admin/js/toastr.js') }}"></script>
<!-- validation -->
<script src="{{ URL::asset('assets/Admin/js/validation.js') }}"></script>
<!-- lobilist -->
<script src="{{ URL::asset('assets/Admin/js/lobilist.js') }}"></script>
<!-- custom -->
<script src="{{ URL::asset('assets/Admin/js/custom.js') }}"></script>
