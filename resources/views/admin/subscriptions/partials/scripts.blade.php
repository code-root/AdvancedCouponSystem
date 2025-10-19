let currentSubscriptionId = null;

function cancelSubscription(id) {
    currentSubscriptionId = id;
    $('#cancelModal').modal('show');
}

function upgradeSubscription(id) {
    currentSubscriptionId = id;
    $('#upgradeModal').modal('show');
}

function extendSubscription(id) {
    currentSubscriptionId = id;
    $('#extendModal').modal('show');
}

function manualActivate(id) {
    if (confirm('Are you sure you want to manually activate this subscription?')) {
        fetch(`/admin/subscriptions/${id}/manual-activate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showAlert('error', 'An error occurred while activating the subscription.');
        });
    }
}

function exportSubscriptions() {
    const form = document.querySelector('form[method="GET"]');
    if (form) {
        const formData = new FormData(form);
        formData.append('export', '1');
        
        const params = new URLSearchParams(formData);
        window.open(`/admin/subscriptions/export?${params.toString()}`, '_blank');
    } else {
        window.open('/admin/subscriptions/export', '_blank');
    }
}

// Form submissions
$('#cancelForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/admin/subscriptions/${currentSubscriptionId}/cancel`, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            $('#cancelModal').modal('hide');
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'An error occurred while cancelling the subscription.');
    });
});

$('#upgradeForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/admin/subscriptions/${currentSubscriptionId}/upgrade`, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            $('#upgradeModal').modal('hide');
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'An error occurred while upgrading the subscription.');
    });
});

$('#extendForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/admin/subscriptions/${currentSubscriptionId}/extend`, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            $('#extendModal').modal('hide');
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'An error occurred while extending the subscription.');
    });
});

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at the top of the content
    const content = document.querySelector('.admin-content');
    if (content) {
        content.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            const alert = content.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

