<?php
session_start();

if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
    header("Location: ../login.php");
    exit();
}

$company_id = $_SESSION["company_id"];
$company_name = $_SESSION["company_name"];
$admin_name = $_SESSION["admin_name"];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: white;
            padding: 32px;
        }

        :root {
            --primary: #2373BA;
            --primary-light: #0c96D4;
            --dark: #464646;
            --gray: #989798;
            --border: #e2e8f0;
            --critical: #dc2626;
            --warning: #f59e0b;
            --success: #10b981;
            --info: #3b82f6;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            color: var(--primary);
        }

        .page-header p {
            color: var(--gray);
            margin-top: 8px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 5px;
            padding: 20px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.total {
            background: #eef4fc;
            color: var(--primary);
        }

        .stat-icon.overdue {
            background: #fee2e2;
            color: var(--critical);
        }

        .stat-icon.pending {
            background: #fed7aa;
            color: var(--warning);
        }

        .stat-icon.paid {
            background: #d1fae5;
            color: var(--success);
        }

        .stat-icon i {
            font-size: 24px;
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
        }

        .stat-label {
            font-size: 13px;
            color: var(--gray);
            margin-top: 4px;
        }

        /* Search and Filter */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .search-container {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 8px 16px;
            gap: 8px;
            min-width: 280px;
        }

        .search-container i {
            color: var(--gray);
        }

        .search-input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }

        .clear-search {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray);
            display: none;
        }

        .filter-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .filter-nav-item {
            background: none;
            border: none;
            padding: 8px 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: var(--gray);
            transition: all 0.2s;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .filter-nav-item:hover {
            color: var(--primary);
            background: #eef4fc;
        }

        .filter-nav-item.active {
            background: var(--primary);
            color: white;
        }

        .filter-count {
            background: rgba(0, 0, 0, 0.08);
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .filter-nav-item.active .filter-count {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Invoices Table */
        .invoices-table-container {
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .invoices-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoices-table th {
            background: #f8fafc;
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 1px solid var(--border);
        }

        .invoices-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .invoices-table tr:last-child td {
            border-bottom: none;
        }

        .invoices-table tr:hover {
            background: #fafcff;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.overdue {
            background: #fee2e2;
            color: var(--critical);
        }

        .status-badge.pending {
            background: #fed7aa;
            color: var(--warning);
        }

        .status-badge.paid {
            background: #d1fae5;
            color: var(--success);
        }

        .days-overdue {
            font-weight: 700;
        }

        .days-overdue.critical {
            color: var(--critical);
        }

        .days-overdue.warning {
            color: var(--warning);
        }

        .amount-at-risk {
            font-weight: 700;
            color: var(--critical);
        }

        .reminder-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .reminder-btn:hover {
            background: #1b5a96;
        }

        .reminder-btn:disabled {
            background: var(--gray);
            cursor: not-allowed;
        }

        .action-paid-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .action-paid-btn:hover {
            background: #0d9488;
        }

        .action-buttons-group {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: white;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .pagination-controls {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .page-btn {
            background: white;
            border: 1px solid var(--border);
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            color: var(--dark);
        }

        .page-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-info {
            font-size: 13px;
            color: var(--gray);
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
        }

        .empty-state i {
            font-size: 64px;
            color: var(--gray);
            margin-bottom: 16px;
        }

        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--gray);
        }

        /* Send Reminder Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            animation: modalFadeIn 0.2s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }

        .modal-header h3 {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-header h3 i {
            color: var(--primary);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            resize: vertical;
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .top-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                width: 100%;
            }

            .filter-nav {
                flex-direction: column;
                align-items: stretch;
            }

            .invoices-table th,
            .invoices-table td {
                padding: 10px;
                font-size: 12px;
            }
        }

        @media (max-width: 640px) {
            .invoices-table-container {
                overflow-x: auto;
            }

            .invoices-table {
                min-width: 700px;
            }
        }
    </style>
</head>

<body>

    <div class="page-header">
        <h1>
            <i class="fas fa-file-invoice-dollar"></i>
            Cash Intelligence
        </h1>
        <p>Track outstanding invoices, overdue accounts, and receivables aging. Send payment reminders directly.</p>
    </div>

    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-receipt"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="totalCount">0</div>
                <div class="stat-label">Total Invoices</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon overdue"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="overdueCount">0</div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="pendingCount">0</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon paid"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="paidCount">0</div>
                <div class="stat-label">Paid</div>
            </div>
        </div>
    </div>

    <div class="filter-nav" id="filterNav">
        <button class="filter-nav-item active" data-filter="all"><i class="fas fa-list"></i> All Invoices <span class="filter-count" id="allCount">0</span></button>
        <button class="filter-nav-item" data-filter="overdue"><i class="fas fa-exclamation-circle"></i> Overdue <span class="filter-count" id="overdueFilterCount">0</span></button>
        <button class="filter-nav-item" data-filter="pending"><i class="fas fa-clock"></i> Pending <span class="filter-count" id="pendingFilterCount">0</span></button>
        <button class="filter-nav-item" data-filter="paid"><i class="fas fa-check-circle"></i> Paid <span class="filter-count" id="paidFilterCount">0</span></button>
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Search by client name...">
            <button class="clear-search" id="clearSearchBtn" onclick="clearSearch()"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div id="invoicesContainer">
        <div class="empty-state">
            <i class="fas fa-file-invoice"></i>
            <h3>No Invoices</h3>
            <p>Upload your invoice data to get started.</p>
        </div>
    </div>

    <div id="notificationContainer"></div>

    <!-- Send Reminder Modal -->
    <div id="reminderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-paper-plane"></i> Send Payment Reminder</h3>
                <button class="close-modal" onclick="closeReminderModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Client</label>
                    <input type="text" id="reminderClient" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="reminderEmail" class="form-control" placeholder="client@example.com">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-coins"></i> Amount Due</label>
                    <input type="text" id="reminderAmount" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Due Date</label>
                    <input type="text" id="reminderDueDate" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-edit"></i> Message</label>
                    <textarea id="reminderMessage" class="form-control" rows="10"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="reminder-btn" id="sendReminderBtn" style="padding: 8px 20px;">Send Reminder</button>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = 'all';
        let currentPage = 1;
        let rowsPerPage = 8;
        let allInvoices = [];
        let searchTerm = '';
        let currentReminderId = null;

        function showNotification(title, message, type = 'info', duration = 4000) {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            let icon = '';
            switch (type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'error':
                    icon = '<i class="fas fa-exclamation-circle"></i>';
                    break;
                case 'warning':
                    icon = '<i class="fas fa-exclamation-triangle"></i>';
                    break;
                default:
                    icon = '<i class="fas fa-info-circle"></i>';
            }
            notification.innerHTML = `<div class="notification-content"><div class="notification-icon">${icon}</div><div class="notification-message"><strong>${title}</strong><p>${message}</p></div><div class="notification-close" onclick="this.closest('.notification').remove()"><i class="fas fa-times"></i></div></div>`;
            container.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 10);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchTerm = '';
            document.getElementById('clearSearchBtn').style.display = 'none';
            currentPage = 1;
            loadInvoices();
        }

        function loadInvoices() {
            fetch('../../api/api_csv/get_invoices.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStats(data);
                        updateFilterCounts(data);
                        allInvoices = data.invoices;
                        renderTable();
                    }
                })
                .catch(error => console.error('Error loading invoices:', error));
        }

        function updateStats(data) {
            document.getElementById('totalCount').innerText = data.total || 0;
            document.getElementById('overdueCount').innerText = data.overdue_count || 0;
            document.getElementById('pendingCount').innerText = data.pending_count || 0;
            document.getElementById('paidCount').innerText = data.paid_count || 0;
        }

        function updateFilterCounts(data) {
            document.getElementById('allCount').innerText = data.total || 0;
            document.getElementById('overdueFilterCount').innerText = data.overdue_count || 0;
            document.getElementById('pendingFilterCount').innerText = data.pending_count || 0;
            document.getElementById('paidFilterCount').innerText = data.paid_count || 0;
        }

        function getFilteredInvoices() {
            let filtered = allInvoices;
            if (currentFilter !== 'all') {
                filtered = filtered.filter(inv => inv.status === currentFilter);
            }
            if (searchTerm) {
                filtered = filtered.filter(inv => inv.client_name.toLowerCase().includes(searchTerm.toLowerCase()));
            }
            // Sort by risk severity: overdue first (most days first), then pending
            filtered.sort((a, b) => {
                if (a.status === 'overdue' && b.status !== 'overdue') return -1;
                if (a.status !== 'overdue' && b.status === 'overdue') return 1;
                if (a.status === 'overdue' && b.status === 'overdue') {
                    return (b.days_overdue || 0) - (a.days_overdue || 0);
                }
                return 0;
            });
            return filtered;
        }

        function openReminderModal(invoice) {
            currentReminderId = invoice.id;
            document.getElementById('reminderClient').value = invoice.client_name;
            document.getElementById('reminderAmount').value = 'R' + formatNumber(invoice.amount);
            document.getElementById('reminderDueDate').value = invoice.due_date;

            let message = `Dear ${invoice.client_name},\n\nThis is a friendly reminder that your invoice of R${formatNumber(invoice.amount)} was due on ${invoice.due_date}. The payment is now ${invoice.days_overdue} days overdue.\n\nPlease arrange payment at your earliest convenience.\n\nThank you for your prompt attention to this matter.\n\nRegards,\n${getCompanyName()}`;

            document.getElementById('reminderMessage').value = message;
            document.getElementById('reminderModal').classList.add('show');
        }

        function closeReminderModal() {
            document.getElementById('reminderModal').classList.remove('show');
            currentReminderId = null;
        }

        function getCompanyName() {
            return '<?php echo htmlspecialchars($company_name); ?>';
        }

        function sendReminder() {
            const email = document.getElementById('reminderEmail').value;
            if (!email) {
                showNotification('Email Required', 'Please enter client email address', 'error');
                return;
            }

            const message = document.getElementById('reminderMessage').value;

            fetch('../../api/api_csv/send_reminder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        invoice_id: currentReminderId,
                        email: email,
                        message: message
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Reminder Sent', `Payment reminder sent to ${email}`, 'success', 4000);
                        closeReminderModal();
                    } else {
                        showNotification('Failed', data.message || 'Could not send reminder', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error', 'Network error. Please try again.', 'error');
                });
        }

     function markAsPaid(invoiceId) {

    const popup = document.createElement('div');

    popup.innerHTML = `
        <div id="paidPopupOverlay" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            backdrop-filter: blur(4px);
        ">
            <div style="
                background: white;
                width: 420px;
                max-width: 90%;
                border-radius: 5px;
                padding: 28px;
                box-shadow: 0 20px 35px -10px rgba(0,0,0,0.2);
                font-family: 'Inter', sans-serif;
                animation: modalFadeIn 0.2s ease;
                border: 1px solid var(--border, #e2e8f0);
            ">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                    <div style="
                        width: 48px;
                        height: 48px;
                        background: #d1fae5;
                        border-radius: 5px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 24px;"></i>
                    </div>
                    <h3 style="
                        margin: 0;
                        font-size: 20px;
                        font-weight: 700;
                        color: var(--dark, #464646);
                    ">
                        Mark Invoice as Paid
                    </h3>
                </div>

                <p style="
                    margin: 0 0 25px 0;
                    color: var(--gray, #989798);
                    line-height: 1.6;
                    font-size: 14px;
                ">
                    Mark this invoice as paid? This will remove it from overdue alerts and update your dashboard.
                </p>

                <div style="
                    display: flex;
                    justify-content: flex-end;
                    gap: 12px;
                ">
                    <button id="cancelPaidBtn" style="
                        padding: 10px 20px;
                        border: 1px solid var(--border, #e2e8f0);
                        border-radius: 5px;
                        cursor: pointer;
                        background: white;
                        color: var(--dark, #464646);
                        font-weight: 500;
                        font-size: 13px;
                        transition: all 0.2s;
                        font-family: 'Inter', sans-serif;
                    ">
                        Cancel
                    </button>

                    <button id="confirmPaidBtn" style="
                        padding: 10px 24px;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        background: var(--success, #10b981);
                        color: white;
                        font-weight: 600;
                        font-size: 13px;
                        transition: all 0.2s;
                        font-family: 'Inter', sans-serif;
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                    ">
                        <i class="fas fa-check-circle"></i> Yes, Mark Paid
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(popup);

    // Add hover effects
    const cancelBtn = document.getElementById('cancelPaidBtn');
    const confirmBtn = document.getElementById('confirmPaidBtn');

    cancelBtn.addEventListener('mouseenter', () => {
        cancelBtn.style.background = '#eef4fc';
        cancelBtn.style.borderColor = 'var(--primary, #2373BA)';
        cancelBtn.style.color = 'var(--primary, #2373BA)';
    });
    cancelBtn.addEventListener('mouseleave', () => {
        cancelBtn.style.background = 'white';
        cancelBtn.style.borderColor = 'var(--border, #e2e8f0)';
        cancelBtn.style.color = 'var(--dark, #464646)';
    });

    confirmBtn.addEventListener('mouseenter', () => {
        confirmBtn.style.background = '#0d9488';
    });
    confirmBtn.addEventListener('mouseleave', () => {
        confirmBtn.style.background = '#10b981';
    });

    cancelBtn.onclick = function () {
        popup.remove();
    };

    confirmBtn.onclick = function () {
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Processing...';
        confirmBtn.disabled = true;
        confirmBtn.style.opacity = '0.7';
        confirmBtn.style.cursor = 'not-allowed';

        fetch('../../api/api_csv/mark_paid.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: invoiceId
            })
        })
        .then(response => response.json())
        .then(data => {
            popup.remove();
            if (data.success) {
                showNotification('Success', 'Invoice marked as paid', 'success', 3000);
                loadInvoices();
            } else {
                showNotification('Error', data.message || 'Failed to update', 'error');
            }
        })
        .catch(error => {
            popup.remove();
            console.error('Error:', error);
            showNotification('Error', 'Network error. Please try again.', 'error');
        });
    };
}

        function formatNumber(amount) {
            return Number(amount).toLocaleString('en-ZA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-ZA');
        }

        function renderTable() {
            const container = document.getElementById('invoicesContainer');
            const filteredInvoices = getFilteredInvoices();
            const totalPages = Math.ceil(filteredInvoices.length / rowsPerPage);

            if (filteredInvoices.length === 0) {
                container.innerHTML = `<div class="empty-state"><i class="fas fa-file-invoice"></i><h3>No Invoices Found</h3><p>${searchTerm ? 'No results matching "' + searchTerm + '"' : 'No invoices match the selected filter.'}</p></div>`;
                return;
            }

            if (currentPage > totalPages) currentPage = 1;
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageInvoices = filteredInvoices.slice(start, end);

            let html = `<div class="invoices-table-container"><table class="invoices-table"><thead><tr>
            <th>Client</th><th>Amount</th><th>Due Date</th><th>Days Overdue</th><th>Status</th><th>Action</th>
        </tr></thead><tbody>`;

            for (let i = 0; i < pageInvoices.length; i++) {
                const inv = pageInvoices[i];

                let statusBadge = '';
                let daysClass = '';
                let amountAtRisk = '';

                if (inv.status === 'overdue') {
                    statusBadge = '<span class="status-badge overdue"><i class="fas fa-exclamation-circle"></i> Overdue</span>';
                    daysClass = 'critical';
                    amountAtRisk = '<span class="amount-at-risk">R' + formatNumber(inv.amount) + '</span>';
                } else if (inv.status === 'pending') {
                    statusBadge = '<span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>';
                    daysClass = 'warning';
                    amountAtRisk = 'R' + formatNumber(inv.amount);
                } else {
                    statusBadge = '<span class="status-badge paid"><i class="fas fa-check-circle"></i> Paid</span>';
                    amountAtRisk = 'R' + formatNumber(inv.amount);
                }

                let daysOverdueText = '-';
                if (inv.days_overdue > 0) {
                    daysOverdueText = `<span class="days-overdue ${daysClass}">${inv.days_overdue} days</span>`;
                }

                let actionButton = '';
                if (inv.status === 'overdue' || inv.status === 'pending') {
                    actionButton = `<div class="action-buttons-group">
                        <button class="reminder-btn" onclick='openReminderModal(${JSON.stringify(inv).replace(/'/g, "&apos;")})'><i class="fas fa-paper-plane"></i> Remind</button>
                        <button class="action-paid-btn" onclick="markAsPaid(${inv.id})"><i class="fas fa-check-circle"></i> Mark Paid</button>
                    </div>`;
                } else {
                    actionButton = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> Paid</span>';
                }

                html += `<tr>
                <td><strong>${escapeHtml(inv.client_name)}</strong></td>
                <td>${amountAtRisk}</td>
                <td>${inv.due_date}</td>
                <td>${daysOverdueText}</td>
                <td>${statusBadge}</td>
                <td>${actionButton}</td>
            </tr>`;
            }

            html += `</tbody></table><div class="pagination"><div class="page-info">Showing ${start + 1} to ${Math.min(end, filteredInvoices.length)} of ${filteredInvoices.length} invoices</div><div class="pagination-controls"><button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i> Previous</button>`;

            if (totalPages <= 5) {
                for (let i = 1; i <= totalPages; i++) html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
            } else {
                if (currentPage > 2) {
                    html += `<button class="page-btn" onclick="changePage(1)">1</button>`;
                    if (currentPage > 3) html += `<span style="padding: 0 4px;">...</span>`;
                }
                for (let i = Math.max(1, currentPage - 1); i <= Math.min(totalPages, currentPage + 1); i++) html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
                if (currentPage < totalPages - 1) {
                    if (currentPage < totalPages - 2) html += `<span style="padding: 0 4px;">...</span>`;
                    html += `<button class="page-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
                }
            }

            html += `<button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next <i class="fas fa-chevron-right"></i></button></div></div></div>`;
            container.innerHTML = html;
        }

        function changePage(page) {
            const filteredInvoices = getFilteredInvoices();
            const totalPages = Math.ceil(filteredInvoices.length / rowsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable();
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.querySelectorAll('.filter-nav-item').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-nav-item').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.getAttribute('data-filter');
                currentPage = 1;
                renderTable();
            });
        });

        document.getElementById('searchInput').addEventListener('input', function(e) {
            searchTerm = e.target.value;
            document.getElementById('clearSearchBtn').style.display = searchTerm ? 'block' : 'none';
            currentPage = 1;
            renderTable();
        });

        document.getElementById('sendReminderBtn').addEventListener('click', sendReminder);

        document.getElementById('reminderModal').addEventListener('click', function(e) {
            if (e.target === this) closeReminderModal();
        });

        const notificationStyle = document.createElement('style');
        notificationStyle.textContent = `.notification { position: fixed; top: 20px; right: 20px; z-index: 3000; min-width: 320px; max-width: 400px; background: white; border-radius: 5px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); transform: translateX(400px); transition: transform 0.3s ease-in-out; overflow: hidden; } .notification.show { transform: translateX(0); } .notification-content { padding: 16px; display: flex; align-items: flex-start; gap: 12px; } .notification-icon { flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; } .notification-icon i { font-size: 14px; } .notification-message { flex: 1; } .notification-message strong { display: block; margin-bottom: 4px; } .notification-message p { font-size: 13px; color: #666; margin: 0; } .notification-close { cursor: pointer; color: #999; font-size: 12px; } .notification-close:hover { color: #666; } .notification.success { border-left: 4px solid #10b981; } .notification.success .notification-icon { background: #d1fae5; color: #10b981; } .notification.error { border-left: 4px solid #dc2626; } .notification.error .notification-icon { background: #fee2e2; color: #dc2626; } .notification.info { border-left: 4px solid #3b82f6; } .notification.info .notification-icon { background: #dbeafe; color: #3b82f6; } .notification.warning { border-left: 4px solid #f59e0b; } .notification.warning .notification-icon { background: #fed7aa; color: #f59e0b; }`;
        document.head.appendChild(notificationStyle);

        loadInvoices();
        setInterval(loadInvoices, 30000);
    </script>

</body>

</html>