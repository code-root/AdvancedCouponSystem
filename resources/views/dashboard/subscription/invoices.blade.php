@extends('dashboard.layouts.vertical', ['title' => 'Subscription Invoices'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Billing', 'title' => 'Subscription Invoices'])

    <!-- Invoices List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-receipt me-2"></i>Payment History
                    </h4>
                </div>
                <div class="card-body">
                    @if($invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Plan</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment Method</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td>
                                                <strong>#{{ $invoice->id }}</strong>
                                            </td>
                                            <td>
                                                {{ $invoice->subscription->plan->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <strong>${{ number_format($invoice->amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->status == 'completed' ? 'success' : ($invoice->status == 'pending' ? 'warning' : ($invoice->status == 'failed' ? 'danger' : 'secondary')) }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($invoice->gateway ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $invoice->created_at->format('M d, Y H:i') }}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#invoiceModal" data-invoice-id="{{ $invoice->id }}">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    @if($invoice->status == 'completed')
                                                        <a href="{{ route('subscription.invoice.download', $invoice->id) }}" class="btn btn-sm btn-outline-success">
                                                            <i class="ti ti-download"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $invoices->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                    <i class="ti ti-receipt fs-24"></i>
                                </div>
                            </div>
                            <h5 class="mb-2">No Invoices Found</h5>
                            <p class="text-muted">You don't have any payment history yet.</p>
                            <a href="{{ route('subscription.plans') }}" class="btn btn-primary">
                                <i class="ti ti-crown me-1"></i>View Plans
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="invoiceDetails">
                <!-- Invoice details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadInvoiceBtn" style="display: none;">
                    <i class="ti ti-download me-1"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentInvoiceId = null;

    // Invoice Modal
    $('#invoiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        currentInvoiceId = button.data('invoice-id');
        
        // Load invoice details
        loadInvoiceDetails(currentInvoiceId);
    });

    // Download Invoice Button
    $('#downloadInvoiceBtn').on('click', function() {
        if (currentInvoiceId) {
            window.open('{{ route("subscription.invoice.download", ":id") }}'.replace(':id', currentInvoiceId), '_blank');
        }
    });

    // Load invoice details
    function loadInvoiceDetails(invoiceId) {
        // Find the invoice data from the table
        const invoiceRow = $(`[data-invoice-id="${invoiceId}"]`).closest('tr');
        const invoiceData = {
            id: invoiceId,
            plan: invoiceRow.find('td:nth-child(2)').text().trim(),
            amount: invoiceRow.find('td:nth-child(3)').text().trim(),
            status: invoiceRow.find('td:nth-child(4) .badge').text().trim(),
            gateway: invoiceRow.find('td:nth-child(5) .badge').text().trim(),
            date: invoiceRow.find('td:nth-child(6)').text().trim()
        };

        // Generate invoice details HTML
        const invoiceDetailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Invoice Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Invoice #:</strong></td>
                            <td>#${invoiceData.id}</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>${invoiceData.date}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td><span class="badge bg-${getStatusColor(invoiceData.status)}">${invoiceData.status}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td><span class="badge bg-info">${invoiceData.gateway}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Plan Details</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Plan:</strong></td>
                            <td>${invoiceData.plan}</td>
                        </tr>
                        <tr>
                            <td><strong>Amount:</strong></td>
                            <td><strong>${invoiceData.amount}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="text-muted">Payment Summary</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>${invoiceData.plan} Subscription</td>
                                    <td class="text-end">${invoiceData.amount}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong>${invoiceData.amount}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        $('#invoiceDetails').html(invoiceDetailsHtml);
        
        // Show/hide download button based on status
        if (invoiceData.status.toLowerCase() === 'completed') {
            $('#downloadInvoiceBtn').show();
        } else {
            $('#downloadInvoiceBtn').hide();
        }
    }

    // Get status color
    function getStatusColor(status) {
        switch (status.toLowerCase()) {
            case 'completed':
                return 'success';
            case 'pending':
                return 'warning';
            case 'failed':
                return 'danger';
            default:
                return 'secondary';
        }
    }
</script>
@endpush

