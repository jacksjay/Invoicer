<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Invoicer App' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .invoice-status {
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
        }
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-overdue { background-color: #fee2e2; color: #dc2626; }
        
        .search-box {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                Invoicer App
            </a>
        </div>
    </nav>

    <main class="container my-4">
        <?= $this->renderSection('content') ?>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>

<?php
// app/Views/invoices/index.php
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-file-invoice me-2"></i>
        Invoice List
    </h1>
    <button class="btn btn-primary" onclick="refreshInvoices()">
        <i class="fas fa-sync-alt me-2"></i>
        Refresh
    </button>
</div>

<!-- Search Form -->
<div class="search-box">
    <form method="GET" action="<?= base_url('invoices') ?>" id="searchForm">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="invoice_number" class="form-label">Invoice Number</label>
                <input type="text" class="form-control" id="invoice_number" name="invoice_number" 
                       value="<?= esc($searchData['invoice_number']) ?>" placeholder="Enter invoice number">
            </div>
            <div class="col-md-3">
                <label for="client_name" class="form-label">Client Name</label>
                <input type="text" class="form-control" id="client_name" name="client_name" 
                       value="<?= esc($searchData['client_name']) ?>" placeholder="Enter client name">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="draft" <?= $searchData['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="sent" <?= $searchData['status'] === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="paid" <?= $searchData['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="overdue" <?= $searchData['status'] === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search me-1"></i>
                    Search
                </button>
                <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>
                    Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Results Summary -->
<?php if ($success): ?>
    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        Found <?= number_format($foundCount) ?> invoice(s). 
        Showing <?= number_format($returnedCount ?? count($invoices)) ?> results.
    </div>
<?php endif; ?>

<!-- Error Message -->
<?php if (!$success && $error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= esc($error) ?>
    </div>
<?php endif; ?>

<!-- Loading Indicator -->
<div class="loading" id="loadingIndicator">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Fetching invoices from FileMaker...</p>
</div>

<!-- Invoice Table -->
<?php if ($success && !empty($invoices)): ?>
    <div class="table-responsive" id="invoiceTable">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Invoice #</th>
                    <th scope="col">Client</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Status</th>
                    <th scope="col">Due Date</th>
                    <th scope="col">Created</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td>
                            <strong><?= esc($invoice['invoice_number']) ?></strong>
                        </td>
                        <td>
                            <div>
                                <strong><?= esc($invoice['client_name']) ?></strong>
                                <?php if (!empty($invoice['client_email'])): ?>
                                    <br><small class="text-muted"><?= esc($invoice['client_email']) ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong>₹<?= number_format($invoice['amount'], 2) ?></strong>
                        </td>
                        <td>
                            <span class="invoice-status status-<?= esc($invoice['status']) ?>">
                                <?= ucfirst(esc($invoice['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($invoice['due_date'])): ?>
                                <?= date('M j, Y', strtotime($invoice['due_date'])) ?>
                                <?php if (strtotime($invoice['due_date']) < time() && $invoice['status'] !== 'paid'): ?>
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($invoice['created_date'])): ?>
                                <?= date('M j, Y', strtotime($invoice['created_date'])) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary" 
                                        onclick="viewInvoice(<?= $invoice['recordId'] ?>)" 
                                        title="View Invoice">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="editInvoice(<?= $invoice['recordId'] ?>)" 
                                        title="Edit Invoice">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info" 
                                        onclick="downloadPDF(<?= $invoice['recordId'] ?>)" 
                                        title="Download PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($foundCount > $limit): ?>
        <nav aria-label="Invoice pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                $totalPages = ceil($foundCount / $limit);
                $currentPage = $currentPage ?? 1;
                
                // Previous button
                if ($currentPage > 1):
                ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>&limit=<?= $limit ?><?= http_build_query($searchData, '', '&') ? '&' . http_build_query($searchData) : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php
                // Page numbers
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?><?= http_build_query($searchData, '', '&') ? '&' . http_build_query($searchData) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php
                // Next button
                if ($currentPage < $totalPages):
                ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>&limit=<?= $limit ?><?= http_build_query($searchData, '', '&') ? '&' . http_build_query($searchData) : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

<?php elseif ($success && empty($invoices)): ?>
    <div class="text-center py-5">
        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No Invoices Found</h4>
        <p class="text-muted">Try adjusting your search criteria or check your FileMaker database.</p>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function refreshInvoices() {
    $('#loadingIndicator').show();
    $('#invoiceTable').hide();
    
    // Reload the current page
    window.location.reload();
}

function viewInvoice(recordId) {
    // Implement view invoice functionality
    alert('View invoice with Record ID: ' + recordId);
    // window.open('<?= base_url('invoices/view/') ?>' + recordId, '_blank');
}

function editInvoice(recordId) {
    // Implement edit invoice functionality
    alert('Edit invoice with Record ID: ' + recordId);
    // window.location.href = '<?= base_url('invoices/edit/') ?>' + recordId;
}

function downloadPDF(recordId) {
    // Implement PDF download functionality
    alert('Download PDF for Record ID: ' + recordId);
    // window.open('<?= base_url('invoices/pdf/') ?>' + recordId, '_blank');
}

// Auto-submit form when search criteria changes
$(document).ready(function() {
    $('#status').on('change', function() {
        if (this.value !== '') {
            $('#searchForm').submit();
        }
    });
    
    // Add enter key support for search inputs
    $('#invoice_number, #client_name').on('keypress', function(e) {
        if (e.which === 13) {
            $('#searchForm').submit();
        }
    });
});
</script>
<?= $this->endSection() ?>
