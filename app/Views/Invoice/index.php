<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Invoice Management System' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .controls {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .refresh-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .clear-filters-btn {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .clear-filters-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }

        .loading {
            text-align: center;
            padding: 60px;
            font-size: 1.2rem;
            color: #6c757d;
            display: none;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .invoice-list {
            padding: 30px;
        }

        .list-header {
            display: grid;
            grid-template-columns: 1fr 2fr 1.5fr 1.5fr 1.2fr 1fr;
            gap: 20px;
            padding: 20px 25px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            margin-bottom: 15px;
            font-weight: 600;
            color: #495057;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-left: 4px solid #3498db;
        }

        .filter-row {
            display: grid;
            grid-template-columns: 1fr 2fr 1.5fr 1.5fr 1.2fr 1fr;
            gap: 20px;
            padding: 15px 25px;
            background: white;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: 1px solid #e9ecef;
            border-left: 4px solid #28a745;
        }

        .column-filter {
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
            width: 100%;
        }

        .column-filter:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .column-filter::placeholder {
            color: #adb5bd;
            font-style: italic;
        }

        .invoice-item {
            display: grid;
            grid-template-columns: 1fr 2fr 1.5fr 1.5fr 1.2fr 1fr;
            gap: 20px;
            align-items: center;
            padding: 25px;
            background: white;
            border-radius: 12px;
            margin-bottom: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .invoice-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .invoice-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            border-color: #3498db;
        }

        .invoice-item:hover::before {
            transform: scaleY(1);
        }

        .invoice-number {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .customer-name {
            font-size: 1rem;
            color: #495057;
            font-weight: 500;
        }

        .invoice-dates {
            font-size: 0.95rem;
            color: #6c757d;
        }

        .due-date {
            font-size: 0.95rem;
            color: #6c757d;
        }

        .invoice-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #28a745;
            text-align: left;
        }

        .invoice-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .status-paid {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            box-shadow: 0 2px 8px rgba(21, 87, 36, 0.2);
        }

        .status-pending {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            box-shadow: 0 2px 8px rgba(133, 100, 4, 0.2);
        }

        .status-overdue {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            box-shadow: 0 2px 8px rgba(114, 28, 36, 0.2);
        }

        .error-message {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            padding: 30px;
            margin: 20px;
            border-radius: 12px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
        }

        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.4;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #495057;
        }

        .empty-state p {
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .filter-count {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 15px;
            padding-left: 25px;
        }

        /* Server-side data display styles */
        .server-invoices {
            display: block;
        }

        /* Hide server invoices when JavaScript loads */
        .js-loaded .server-invoices {
            display: none;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .list-header,
            .filter-row,
            .invoice-item {
                grid-template-columns: 1fr 1.5fr 1fr 1fr 1fr 0.8fr;
            }
        }

        @media (max-width: 968px) {
            .list-header,
            .filter-row {
                display: none;
            }
            
            .invoice-item {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 20px;
            }
            
            .invoice-item > div {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px solid #f8f9fa;
            }
            
            .invoice-item > div:last-child {
                border-bottom: none;
                justify-content: center;
            }
            
            .invoice-item > div::before {
                content: attr(data-label);
                font-weight: 600;
                color: #6c757d;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .invoice-list {
                padding: 20px;
            }

            /* Mobile filter section */
            .mobile-filters {
                display: block;
                background: white;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            }

            .mobile-filter-group {
                margin-bottom: 15px;
            }

            .mobile-filter-label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
                color: #495057;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
        }

        @media (min-width: 969px) {
            .mobile-filters {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice Management System</h1>
            <p>FileMaker Data API Integration</p>
        </div>
        
        <div class="controls">
            <button class="refresh-btn" onclick="loadInvoices()">
                🔄 Refresh Invoices
            </button>
            <button class="clear-filters-btn" onclick="clearAllFilters()">
                🗑️ Clear All Filters
            </button>
        </div>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            Loading invoices...
        </div>
        
        <div class="invoice-list" id="invoiceList">
            <!-- Show server-side data initially, then replace with AJAX -->
            <div class="server-invoices">
                <?php if (isset($error) && $error): ?>
                    <div class="error-message">
                        <h4>Error Loading Invoices</h4>
                        <p><?= esc($error) ?></p>
                    </div>
                <?php elseif (empty($invoices)): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                        </svg>
                        <h3>No invoices found</h3>
                        <p>There are no invoices to display at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="list-header">
                        <div>Invoice #</div>
                        <div>Customer</div>
                        <div>Invoice Date</div>
                        <div>Due Date</div>
                        <div>Amount</div>
                        <div>Status</div>
                    </div>
                    
                    <?php foreach ($invoices as $invoice): ?>
                        <div class="invoice-item">
                            <div class="invoice-number" data-label="Invoice #">
                                #<?= esc($invoice['invoiceNumber'] ?? 'N/A') ?>
                            </div>
                            <div class="customer-name" data-label="Customer">
                                <?= esc($invoice['customerName'] ?? 'N/A') ?>
                            </div>
                            <div class="invoice-dates" data-label="Invoice Date">
                                <?= isset($invoice['invoiceDate']) ? date('M j, Y', strtotime($invoice['invoiceDate'])) : 'N/A' ?>
                            </div>
                            <div class="due-date" data-label="Due Date">
                                <?= isset($invoice['dueDate']) ? date('M j, Y', strtotime($invoice['dueDate'])) : 'N/A' ?>
                            </div>
                            <div class="invoice-amount" data-label="Amount">
                                $<?= number_format((float) ($invoice['totalAmount'] ?? 0), 2) ?>
                            </div>
                            <div data-label="Status">
                                <?php 
                                $status = $invoice['status'] ?? 'Unknown';
                                $statusClass = 'pending';
                                if (stripos($status, 'paid') !== false) $statusClass = 'paid';
                                elseif (stripos($status, 'overdue') !== false) $statusClass = 'overdue';
                                ?>
                                <span class="invoice-status status-<?= $statusClass ?>">
                                    <?= esc($status) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let allInvoices = [];
        const BASE_URL = '<?= base_url() ?>';

        // Mark that JavaScript has loaded
        document.documentElement.classList.add('js-loaded');

        // Load invoices on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadInvoices();
        });

        // Main function to fetch and load invoices from the backend
        async function loadInvoices() {
            const loading = document.getElementById('loading');
            const list = document.getElementById('invoiceList');
            
            loading.style.display = 'block';
            
            try {
                const response = await fetch(`${BASE_URL}/invoice/getInvoicesAjax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'limit=50&page=1'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    allInvoices = data.data || data.invoices || [];
                    displayInvoices(allInvoices, true); // Pass true for initial load
                } else {
                    showError(data.error || 'Failed to load invoices');
                }
            } catch (error) {
                console.error('Error loading invoices:', error);
                showError('Network error: ' + error.message);
            } finally {
                loading.style.display = 'none';
            }
        }

        // Create the filter row structure (only called once)
        function createFilterRow() {
            const filterRow = `
                <div class="filter-row" id="filterRow">
                    <input type="text" class="column-filter" placeholder="Filter invoice #..." 
                           onkeyup="filterInvoices()" id="filter-invoice">
                    <input type="text" class="column-filter" placeholder="Filter customer..." 
                           onkeyup="filterInvoices()" id="filter-customer">
                    <input type="text" class="column-filter" placeholder="Filter invoice date..." 
                           onkeyup="filterInvoices()" id="filter-invoicedate">
                    <input type="text" class="column-filter" placeholder="Filter due date..." 
                           onkeyup="filterInvoices()" id="filter-duedate">
                    <input type="text" class="column-filter" placeholder="Filter amount..." 
                           onkeyup="filterInvoices()" id="filter-amount">
                    <input type="text" class="column-filter" placeholder="Filter status..." 
                           onkeyup="filterInvoices()" id="filter-status">
                </div>
            `;

            const mobileFilters = `
                <div class="mobile-filters" id="mobileFilters">
                    <div class="mobile-filter-group">
                        <label class="mobile-filter-label">Invoice #</label>
                        <input type="text" class="column-filter" placeholder="Filter invoice #..." 
                               onkeyup="filterInvoices()" id="filter-invoice-mobile">
                    </div>
                    <div class="mobile-filter-group">
                        <label class="mobile-filter-label">Customer</label>
                        <input type="text" class="column-filter" placeholder="Filter customer..." 
                               onkeyup="filterInvoices()" id="filter-customer-mobile">
                    </div>
                    <div class="mobile-filter-group">
                        <label class="mobile-filter-label">Invoice Date</label>
                        <input type="text" class="column-filter" placeholder="Filter invoice date..." 
                               onkeyup="filterInvoices()" id="filter-invoicedate-mobile">
                    </div>
                    <div class="mobile-filter-group">
                        <label class="mobile-filter-label">Due Date</label>
                        <input type="text" class="column-filter" placeholder="Filter due date..." 
                               onkeyup="filterInvoices()" id="filter-duedate-mobile">
                    </div>
                    <div class="mobile-filter-group">
                        <label class="mobile-filter-label">Amount</label>
                        <input type="text" class="column-filter" placeholder="Filter amount..." 
                               onkeyup="filterInvoices()" id="filter-amount-mobile">
                    </div>
                    <div class="mobile-filter-group">
                        <label class="mobile-filter-label">Status</label>
                        <input type="text" class="column-filter" placeholder="Filter status..." 
                               onkeyup="filterInvoices()" id="filter-status-mobile">
                    </div>
                </div>
            `;

            return filterRow + mobileFilters;
        }

        // Renders a list of invoices into the invoice list section
        function displayInvoices(invoices, isInitialLoad = false) {
            const list = document.getElementById('invoiceList');
            
            if (invoices.length === 0 && allInvoices.length === 0) {
                list.innerHTML = `
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                        </svg>
                        <h3>No invoices found</h3>
                        <p>There are no invoices to display at the moment.</p>
                    </div>
                `;
                return;
            }
            
            const listHeader = `
                <div class="list-header">
                    <div>Invoice #</div>
                    <div>Customer</div>
                    <div>Invoice Date</div>
                    <div>Due Date</div>
                    <div>Amount</div>
                    <div>Status</div>
                </div>
            `;

            // Only create filter row on initial load or if it doesn't exist
            let filterSection = '';
            if (isInitialLoad || !document.getElementById('filterRow')) {
                filterSection = createFilterRow();
            }

            const filteredCount = invoices.length < allInvoices.length 
                ? `<div class="filter-count" id="filterCount">Showing ${invoices.length} of ${allInvoices.length} invoices</div>`
                : `<div class="filter-count" id="filterCount"></div>`;
            
            if (invoices.length === 0) {
                // Just update the content area, preserve filters
                const contentArea = document.getElementById('invoiceContent') || createContentArea();
                contentArea.innerHTML = `
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                        </svg>
                        <h3>No invoices found</h3>
                        <p>No invoices match your current filter criteria.</p>
                    </div>
                `;
                updateFilterCount(invoices.length);
                return;
            }
            
            const invoiceItems = invoices.map(invoice => `
                <div class="invoice-item">
                    <div class="invoice-number" data-label="Invoice #">#${invoice.invoiceNumber || 'N/A'}</div>
                    <div class="customer-name" data-label="Customer">${invoice.customerName || 'N/A'}</div>
                    <div class="invoice-dates" data-label="Invoice Date">${formatDate(invoice.invoiceDate)}</div>
                    <div class="due-date" data-label="Due Date">${formatDate(invoice.dueDate)}</div>
                    <div class="invoice-amount" data-label="Amount">${formatCurrency(invoice.totalAmount || 0)}</div>
                    <div data-label="Status">
                        <span class="invoice-status status-${getStatusClass(invoice.status)}">
                            ${invoice.status || 'Unknown'}
                        </span>
                    </div>
                </div>
            `).join('');
            
            if (isInitialLoad) {
                list.innerHTML = listHeader + filterSection + filteredCount + `<div id="invoiceContent">${invoiceItems}</div>`;
            } else {
                // Just update the content area, preserve filters
                const contentArea = document.getElementById('invoiceContent');
                if (contentArea) {
                    contentArea.innerHTML = invoiceItems;
                    updateFilterCount(invoices.length);
                } else {
                    // Fallback if content area doesn't exist
                    list.innerHTML = listHeader + filterSection + filteredCount + `<div id="invoiceContent">${invoiceItems}</div>`;
                }
            }
        }

        // Helper function to create content area if it doesn't exist
        function createContentArea() {
            const list = document.getElementById('invoiceList');
            let contentArea = document.getElementById('invoiceContent');
            if (!contentArea) {
                contentArea = document.createElement('div');
                contentArea.id = 'invoiceContent';
                list.appendChild(contentArea);
            }
            return contentArea;
        }

        // Helper function to update filter count
        function updateFilterCount(count) {
            const filterCount = document.getElementById('filterCount');
            if (filterCount) {
                filterCount.innerHTML = count < allInvoices.length 
                    ? `Showing ${count} of ${allInvoices.length} invoices`
                    : '';
            }
        }

        // Enhanced filter function that works with multiple column filters
        function filterInvoices() {
            // Get filter values from both desktop and mobile inputs
            const filters = {
                invoice: (document.getElementById('filter-invoice')?.value || 
                         document.getElementById('filter-invoice-mobile')?.value || '').toLowerCase(),
                customer: (document.getElementById('filter-customer')?.value || 
                          document.getElementById('filter-customer-mobile')?.value || '').toLowerCase(),
                invoiceDate: (document.getElementById('filter-invoicedate')?.value || 
                             document.getElementById('filter-invoicedate-mobile')?.value || '').toLowerCase(),
                dueDate: (document.getElementById('filter-duedate')?.value || 
                         document.getElementById('filter-duedate-mobile')?.value || '').toLowerCase(),
                amount: (document.getElementById('filter-amount')?.value || 
                        document.getElementById('filter-amount-mobile')?.value || '').toLowerCase(),
                status: (document.getElementById('filter-status')?.value || 
                        document.getElementById('filter-status-mobile')?.value || '').toLowerCase()
            };

            // Sync filter values between desktop and mobile
            syncFilterValues();

            const filtered = allInvoices.filter(invoice => {
                const invoiceNumber = (invoice.invoiceNumber || '').toString().toLowerCase();
                const customerName = (invoice.customerName || '').toString().toLowerCase();
                const invoiceDate = formatDate(invoice.invoiceDate).toLowerCase();
                const dueDate = formatDate(invoice.dueDate).toLowerCase();
                const amount = formatCurrency(invoice.totalAmount || 0).toLowerCase();
                const status = (invoice.status || '').toString().toLowerCase();

                return (
                    (filters.invoice === '' || invoiceNumber.includes(filters.invoice)) &&
                    (filters.customer === '' || customerName.includes(filters.customer)) &&
                    (filters.invoiceDate === '' || invoiceDate.includes(filters.invoiceDate)) &&
                    (filters.dueDate === '' || dueDate.includes(filters.dueDate)) &&
                    (filters.amount === '' || amount.includes(filters.amount)) &&
                    (filters.status === '' || status.includes(filters.status))
                );
            });

            displayInvoices(filtered);
        }

        // Sync filter values between desktop and mobile inputs
        function syncFilterValues() {
            const filterPairs = [
                ['filter-invoice', 'filter-invoice-mobile'],
                ['filter-customer', 'filter-customer-mobile'],
                ['filter-invoicedate', 'filter-invoicedate-mobile'],
                ['filter-duedate', 'filter-duedate-mobile'],
                ['filter-amount', 'filter-amount-mobile'],
                ['filter-status', 'filter-status-mobile']
            ];

            filterPairs.forEach(([desktopId, mobileId]) => {
                const desktop = document.getElementById(desktopId);
                const mobile = document.getElementById(mobileId);
                
                if (desktop && mobile) {
                    if (desktop.value !== mobile.value) {
                        if (document.activeElement === desktop) {
                            mobile.value = desktop.value;
                        } else if (document.activeElement === mobile) {
                            desktop.value = mobile.value;
                        }
                    }
                }
            });
        }

        // Clear all filters
        function clearAllFilters() {
            const filterIds = [
                'filter-invoice', 'filter-customer', 'filter-invoicedate',
                'filter-duedate', 'filter-amount', 'filter-status',
                'filter-invoice-mobile', 'filter-customer-mobile', 'filter-invoicedate-mobile',
                'filter-duedate-mobile', 'filter-amount-mobile', 'filter-status-mobile'
            ];

            filterIds.forEach(id => {
                const element = document.getElementById(id);
                if (element) element.value = '';
            });

            displayInvoices(allInvoices, false); // Don't recreate filters, just update content
        }

        function getStatusClass(status) {
            if (!status) return 'pending';
            const statusLower = status.toLowerCase();
            if (statusLower.includes('paid')) return 'paid';
            if (statusLower.includes('overdue')) return 'overdue';
            return 'pending';
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch {
                return dateString;
            }
        }

        function formatCurrency(amount) {
            if (!amount) return '0.00';
            return parseFloat(amount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function showError(message) {
            const list = document.getElementById('invoiceList');
            list.innerHTML = `
                <div class="error-message">
                    <h4>Error Loading Invoices</h4>
                    <p>${message}</p>
                    <button class="refresh-btn" onclick="loadInvoices()" style="margin-top: 15px;">
                        Try Again
                    </button>
                </div>
            `;
        }
    </script>
</body>
</html>