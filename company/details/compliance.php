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
    <title>Compliance Calendar | OpsInsyts</title>
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

        .stat-icon.upcoming {
            background: #fed7aa;
            color: var(--warning);
        }

        .stat-icon.completed {
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

        .add-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .add-btn:hover {
            background: #1b5a96;
        }

        .filter-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            background: white;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
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

        .compliance-table-container {
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .compliance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .compliance-table th {
            background: #f8fafc;
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 1px solid var(--border);
        }

        .compliance-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .compliance-table tr:last-child td {
            border-bottom: none;
        }

        .compliance-table tr:hover {
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

        .status-badge.upcoming {
            background: #fed7aa;
            color: var(--warning);
        }

        .status-badge.completed {
            background: #d1fae5;
            color: var(--success);
        }

        .status-badge.pending {
            background: #e2e8f0;
            color: var(--dark);
        }

        .days-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .days-badge.critical {
            background: #fee2e2;
            color: var(--critical);
        }

        .days-badge.warning {
            background: #fed7aa;
            color: var(--warning);
        }

        .days-badge.success {
            background: #d1fae5;
            color: var(--success);
        }

        .days-badge.info {
            background: #eef4fc;
            color: var(--primary);
        }

        .reminder-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 11px;
            background: #f1f5f9;
            color: var(--dark);
        }

        .reminder-badge.active {
            background: #dc2626;
            color: white;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }

        .icon-btn {
            background: #eef4fc;
            color: var(--primary);
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .icon-btn:hover {
            background: var(--primary);
            color: white;
        }

        .action-btn {
            background: white;
            border: 1px solid var(--border);
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .delete-btn {
            background: #fee2e2;
            color: var(--critical);
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .delete-btn:hover {
            background: var(--critical);
            color: white;
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

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 5px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
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
        }

        .reminder-days-group {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: normal !important;
            cursor: pointer;
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

            .filter-nav-item {
                padding: 6px 14px;
                font-size: 13px;
            }

            .compliance-table th,
            .compliance-table td {
                padding: 10px 12px;
                font-size: 12px;
            }
        }

        @media (max-width: 640px) {
            .compliance-table-container {
                overflow-x: auto;
            }

            .compliance-table {
                min-width: 750px;
            }
        }
    </style>
</head>

<body>

    <div class="page-header">
        <h1>
            <i class="fas fa-calendar-check"></i>
            Compliance Calendar
        </h1>
        <p>Track all your statutory deadlines and compliance obligations.</p>
    </div>

    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-list"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="totalCount">0</div>
                <div class="stat-label">Total Items</div>
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
            <div class="stat-icon upcoming"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="upcomingCount">0</div>
                <div class="stat-label">Due Within 7 Days</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon completed"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="completedCount">0</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>

    <div class="filter-nav" id="filterNav">
        <button class="filter-nav-item active" data-filter="all"><i class="fas fa-list"></i> All <span class="filter-count" id="allFilterCount">0</span></button>
        <button class="filter-nav-item" data-filter="overdue"><i class="fas fa-exclamation-circle"></i> Overdue <span class="filter-count" id="overdueFilterCount">0</span></button>
        <button class="filter-nav-item" data-filter="upcoming"><i class="fas fa-clock"></i> Upcoming (7 days) <span class="filter-count" id="upcomingFilterCount">0</span></button>
        <button class="filter-nav-item" data-filter="completed"><i class="fas fa-check-circle"></i> Completed <span class="filter-count" id="completedFilterCount">0</span></button>
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Search by title...">
            <button class="clear-search" id="clearSearchBtn" onclick="clearSearch()"><i class="fas fa-times"></i></button>
        </div>
        <button class="add-btn" onclick="openAddModal()"><i class="fas fa-plus"></i> Add Obligation</button>
    </div>

    <div id="complianceContainer">
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No Compliance Items</h3>
            <p>Upload your compliance data or add a new obligation to get started.</p>
        </div>
    </div>

    <div id="notificationContainer"></div>

    <!-- Add Compliance Modal -->
    <div id="addComplianceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Add Compliance Obligation</h3>
                <button class="icon-btn" onclick="closeAddModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Title</label>
                    <input type="text" id="complianceTitle" class="form-control" placeholder="e.g., Lease Renewal, Insurance Review">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Due Date</label>
                    <input type="date" id="complianceDueDate" class="form-control">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-bell"></i> Reminder Days</label>
                    <div class="reminder-days-group">
                        <label class="checkbox-label"><input type="checkbox" value="30"> 30 days before</label>
                        <label class="checkbox-label"><input type="checkbox" value="14"> 14 days before</label>
                        <label class="checkbox-label"><input type="checkbox" value="7"> 7 days before</label>
                        <label class="checkbox-label"><input type="checkbox" value="1"> 1 day before</label>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sync-alt"></i> Recurrence</label>
                    <select id="complianceRecurrence" class="form-control">
                        <option value="none">One-time only</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="action-btn" onclick="closeAddModal()">Cancel</button>
                <button class="add-btn" onclick="addCompliance()">Add Obligation</button>
            </div>
        </div>
    </div>

    <!-- Edit Compliance Modal -->
    <div id="editComplianceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Compliance Obligation</h3>
                <button class="icon-btn" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editComplianceId">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Title</label>
                    <input type="text" id="editComplianceTitle" class="form-control">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Due Date</label>
                    <input type="date" id="editComplianceDueDate" class="form-control">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-bell"></i> Reminder Days</label>
                    <div class="reminder-days-group" id="editReminderDays">
                        <label class="checkbox-label"><input type="checkbox" value="30"> 30 days before</label>
                        <label class="checkbox-label"><input type="checkbox" value="14"> 14 days before</label>
                        <label class="checkbox-label"><input type="checkbox" value="7"> 7 days before</label>
                        <label class="checkbox-label"><input type="checkbox" value="1"> 1 day before</label>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sync-alt"></i> Recurrence</label>
                    <select id="editComplianceRecurrence" class="form-control">
                        <option value="none">One-time only</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="action-btn" onclick="closeEditModal()">Cancel</button>
                <button class="add-btn" onclick="updateCompliance()"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = 'all';
        let currentPage = 1;
        let rowsPerPage = 8;
        let allItems = [];
        let searchTerm = '';
        let shownReminders = [];
        let shownWarningAlerts = [];

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

            notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">${icon}</div>
                <div class="notification-message">
                    <strong>${title}</strong>
                    <p>${message}</p>
                </div>
                <div class="notification-close" onclick="this.closest('.notification').remove()">
                    <i class="fas fa-times"></i>
                </div>
            </div>`;
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
            renderTable();
        }

        function openAddModal() {
            document.getElementById('addComplianceModal').classList.add('show');
        }

        function closeAddModal() {
            document.getElementById('addComplianceModal').classList.remove('show');
            document.getElementById('complianceTitle').value = '';
            document.getElementById('complianceDueDate').value = '';
            document.querySelectorAll('#addComplianceModal input[type="checkbox"]').forEach(cb => cb.checked = false);
        }

        function openEditModal(item) {
            document.getElementById('editComplianceId').value = item.id;
            document.getElementById('editComplianceTitle').value = item.title;
            document.getElementById('editComplianceDueDate').value = item.due_date;


            let recurrence = 'none';
            let cleanTitle = item.title;

            if (item.title) {
                const lowerTitle = item.title.toLowerCase();
                if (lowerTitle.endsWith(' monthly')) {
                    recurrence = 'monthly';
                    cleanTitle = item.title.slice(0, -8).trim();
                } else if (lowerTitle.endsWith(' quarterly')) {
                    recurrence = 'quarterly';
                    cleanTitle = item.title.slice(0, -10).trim();
                } else if (lowerTitle.endsWith(' yearly')) {
                    recurrence = 'yearly';
                    cleanTitle = item.title.slice(0, -7).trim();
                }
            }

            document.getElementById('editComplianceTitle').value = cleanTitle;
            document.getElementById('editComplianceRecurrence').value = recurrence;
            let reminderDays = item.reminder_days;
            if (typeof reminderDays === 'string') {
                try {
                    reminderDays = JSON.parse(reminderDays);
                } catch (e) {
                    reminderDays = [];
                }
            }
            if (!Array.isArray(reminderDays)) reminderDays = [];

            document.querySelectorAll('#editReminderDays input[type="checkbox"]').forEach(cb => {
                cb.checked = reminderDays.includes(parseInt(cb.value));
            });
            document.getElementById('editComplianceModal').classList.add('show');
        }

        function closeEditModal() {
            document.getElementById('editComplianceModal').classList.remove('show');
        }

        function addCompliance() {
            const title = document.getElementById('complianceTitle').value.trim();
            const dueDate = document.getElementById('complianceDueDate').value;
            const reminderDays = Array.from(document.querySelectorAll('#addComplianceModal input[type="checkbox"]:checked')).map(cb => parseInt(cb.value));
            const recurrence = document.getElementById('complianceRecurrence').value;

            if (!title || !dueDate) {
                showNotification('Error', 'Please fill in title and due date', 'error');
                return;
            }

            fetch('../../api/api_csv/add_compliance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        due_date: dueDate,
                        reminder_days: reminderDays,
                        recurrence: recurrence
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Success', 'Compliance obligation added', 'success');
                        closeAddModal();
                        loadCompliance();
                    } else {
                        showNotification('Error', data.message || 'Failed to add', 'error');
                    }
                });
        }

        function updateCompliance() {
            const id = document.getElementById('editComplianceId').value;
            const title = document.getElementById('editComplianceTitle').value.trim();
            const dueDate = document.getElementById('editComplianceDueDate').value;
            const reminderDays = Array.from(document.querySelectorAll('#editReminderDays input[type="checkbox"]:checked')).map(cb => parseInt(cb.value));
            const recurrence = document.getElementById('editComplianceRecurrence').value;

            if (!title || !dueDate) {
                showNotification('Error', 'Please fill in title and due date', 'error');
                return;
            }

            fetch('../../api/api_csv/update_compliance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        title: title,
                        due_date: dueDate,
                        reminder_days: reminderDays,
                        recurrence: recurrence
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Success', 'Compliance obligation updated', 'success');
                        closeEditModal();
                        loadCompliance();
                    } else {
                        showNotification('Error', data.message || 'Failed to update', 'error');
                    }
                });
        }

        function deleteComplianceItem(id) {

            // Create popup overlay
            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.background = 'rgba(0,0,0,0.6)';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.zIndex = '99999';
            overlay.style.backdropFilter = 'blur(4px)';

            // Popup box - matching OpsInsyts theme
            const popup = document.createElement('div');
            popup.style.background = 'white';
            popup.style.padding = '28px';
            popup.style.borderRadius = '5px';
            popup.style.width = '400px';
            popup.style.maxWidth = '90%';
            popup.style.boxShadow = '0 20px 35px -10px rgba(0,0,0,0.2)';
            popup.style.fontFamily = "'Inter', sans-serif";
            popup.style.border = '1px solid var(--border, #e2e8f0)';

            popup.innerHTML = `
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
            <div style="width: 48px; height: 48px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-trash-alt" style="color: #dc2626; font-size: 22px;"></i>
            </div>
            <h3 style="margin:0; font-size: 20px; font-weight: 700; color: var(--dark, #464646);">Delete Compliance Item</h3>
        </div>
        
        <p style="color: var(--gray, #989798); line-height: 1.6; margin-bottom: 25px; font-size: 14px;">
            Are you sure you want to delete this compliance obligation?<br>
            This action cannot be undone.
        </p>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;">
            <button id="cancelDeleteBtn"
                style="
                    padding: 10px 20px;
                    border: 1px solid var(--border, #e2e8f0);
                    border-radius: 5px;
                    background: white;
                    cursor: pointer;
                    font-size: 13px;
                    font-weight: 500;
                    color: var(--dark, #464646);
                    transition: all 0.2s;
                    font-family: 'Inter', sans-serif;
                "
                onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#2373BA'; this.style.color='#2373BA'"
                onmouseout="this.style.background='white'; this.style.borderColor='var(--border, #e2e8f0)'; this.style.color='var(--dark, #464646)'">
                Cancel
            </button>

            <button id="confirmDeleteBtn"
                style="
                    padding: 10px 24px;
                    border: none;
                    border-radius: 5px;
                    background: #dc2626;
                    color: white;
                    cursor: pointer;
                    font-size: 13px;
                    font-weight: 600;
                    transition: all 0.2s;
                    font-family: 'Inter', sans-serif;
                "
                onmouseover="this.style.background='#b91c1c'"
                onmouseout="this.style.background='#dc2626'">
                <i class="fas fa-trash" style="margin-right: 6px;"></i> Delete
            </button>
        </div>
    `;

            overlay.appendChild(popup);
            document.body.appendChild(overlay);

            // Cancel button
            document.getElementById('cancelDeleteBtn').onclick = function() {
                document.body.removeChild(overlay);
            };

            // Confirm delete
            document.getElementById('confirmDeleteBtn').onclick = function() {
                // Disable button to prevent double click
                const confirmBtn = document.getElementById('confirmDeleteBtn');
                const originalText = confirmBtn.innerHTML;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Deleting...';
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.7';
                confirmBtn.style.cursor = 'not-allowed';

                fetch('../../api/api_csv/delete_compliance.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.body.removeChild(overlay);

                        if (data.success) {
                            showNotification(
                                'Success',
                                'Compliance obligation deleted successfully',
                                'success'
                            );
                            loadCompliance();
                        } else {
                            showNotification(
                                'Error',
                                data.message || 'Failed to delete compliance obligation',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        document.body.removeChild(overlay);
                        showNotification(
                            'Server Error',
                            'Could not connect to server',
                            'error'
                        );
                        console.error(error);
                    });
            };
        }

        function loadCompliance() {
            fetch('../../api/api_csv/get_compliance.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStats(data);
                        updateFilterCounts(data);
                        allItems = data.items;
                        checkWarnings(allItems);
                        checkReminders(allItems);
                        renderTable();
                    }
                })
                .catch(error => console.error('Error loading compliance:', error));
        }

        function checkWarnings(items) {
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                if (item.status === 'completed') continue;
                if (item.status === 'overdue') {
                    if (!shownWarningAlerts.includes(item.id + '_overdue')) {
                        shownWarningAlerts.push(item.id + '_overdue');
                        showNotification('Overdue Compliance', item.title + ' was due on ' + item.due_date + '. Please take action immediately.', 'error', 8000);
                    }
                } else if (item.status === 'upcoming') {
                    const daysLeft = getDaysRemaining(item.due_date, item.status);
                    if (!shownWarningAlerts.includes(item.id + '_' + daysLeft)) {
                        shownWarningAlerts.push(item.id + '_' + daysLeft);
                        if (daysLeft <= 3) {
                            showNotification('Urgent: ' + item.title, 'This compliance item is due in ' + daysLeft + ' days on ' + item.due_date, 'warning', 8000);
                        } else if (daysLeft <= 7) {
                            showNotification('Reminder: ' + item.title, 'This compliance item is due in ' + daysLeft + ' days on ' + item.due_date, 'info', 5000);
                        }
                    }
                }
            }
        }

        function checkReminders(items) {
            const today = new Date().toISOString().split('T')[0];
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                if (item.status === 'completed') continue;
                let reminderDays = item.reminder_days;
                if (typeof reminderDays === 'string') {
                    try {
                        reminderDays = JSON.parse(reminderDays);
                    } catch (e) {
                        reminderDays = [reminderDays];
                    }
                }
                if (reminderDays && Array.isArray(reminderDays)) {
                    for (let j = 0; j < reminderDays.length; j++) {
                        const reminderDay = reminderDays[j];
                        const reminderDate = new Date(item.due_date);
                        reminderDate.setDate(reminderDate.getDate() - reminderDay);
                        const reminderDateStr = reminderDate.toISOString().split('T')[0];
                        const reminderKey = item.id + '_' + reminderDay;
                        if (reminderDateStr === today && !shownReminders.includes(reminderKey)) {
                            shownReminders.push(reminderKey);
                            showNotification('Reminder: ' + item.title, 'This compliance item is due in ' + reminderDay + ' days on ' + item.due_date, 'warning', 10000);
                        }
                    }
                }
            }
        }

        function updateStats(data) {
            document.getElementById('totalCount').innerText = data.total || 0;
            document.getElementById('overdueCount').innerText = data.overdue_count || 0;
            document.getElementById('upcomingCount').innerText = data.upcoming_count || 0;
            document.getElementById('completedCount').innerText = data.completed_count || 0;
        }

        function updateFilterCounts(data) {
            document.getElementById('allFilterCount').innerText = data.total || 0;
            document.getElementById('overdueFilterCount').innerText = data.overdue_count || 0;
            document.getElementById('upcomingFilterCount').innerText = data.upcoming_count || 0;
            document.getElementById('completedFilterCount').innerText = data.completed_count || 0;
        }

        function getFilteredItems() {
            let filtered = allItems;
            if (currentFilter !== 'all') {
                filtered = filtered.filter(item => item.status === currentFilter);
            }
            if (searchTerm) {
                filtered = filtered.filter(item => item.title.toLowerCase().includes(searchTerm.toLowerCase()));
            }
            return filtered;
        }

        function getDaysRemaining(dueDate, status) {
            if (status === 'completed') return null;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const due = new Date(dueDate);
            due.setHours(0, 0, 0, 0);
            const diffTime = due - today;
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        }

        function renderTable() {
            const container = document.getElementById('complianceContainer');
            const filteredItems = getFilteredItems();
            const totalPages = Math.ceil(filteredItems.length / rowsPerPage);

            if (filteredItems.length === 0) {
                container.innerHTML = `<div class="empty-state"><i class="fas fa-calendar-times"></i><h3>No ${currentFilter !== 'all' ? currentFilter : ''} Compliance Items</h3><p>${searchTerm ? 'No results matching "' + searchTerm + '"' : 'You have no ' + (currentFilter !== 'all' ? currentFilter : '') + ' compliance items.'}</p></div>`;
                return;
            }

            if (currentPage > totalPages) currentPage = 1;
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageItems = filteredItems.slice(start, end);

            let html = `<div class="compliance-table-container"><table class="compliance-table"><thead><tr><th style="width: 25%;">Title</th><th style="width: 12%;">Due Date</th><th style="width: 12%;">Status</th><th style="width: 15%;">Days Remaining</th><th style="width: 24%;">Reminder Schedule</th><th style="width: 12%;">Action</th></tr></thead><tbody>`;

            for (let i = 0; i < pageItems.length; i++) {
                const item = pageItems[i];

                let statusIcon = '',
                    statusClass = '';
                if (item.status === 'overdue') {
                    statusIcon = 'fa-exclamation-circle';
                    statusClass = 'overdue';
                } else if (item.status === 'upcoming') {
                    statusIcon = 'fa-clock';
                    statusClass = 'upcoming';
                } else if (item.status === 'completed') {
                    statusIcon = 'fa-check-circle';
                    statusClass = 'completed';
                } else {
                    statusIcon = 'fa-hourglass-half';
                    statusClass = 'pending';
                }

                let daysRemaining = '',
                    daysClass = '',
                    daysIcon = '';
                if (item.status === 'completed') {
                    daysRemaining = 'Completed';
                    daysClass = 'success';
                    daysIcon = 'fa-check-circle';
                } else if (item.status === 'overdue') {
                    const overdueDays = Math.abs(getDaysRemaining(item.due_date, item.status));
                    daysRemaining = 'OVERDUE by ' + overdueDays + ' days';
                    daysClass = 'critical';
                    daysIcon = 'fa-exclamation-triangle';
                } else {
                    const daysLeft = getDaysRemaining(item.due_date, item.status);
                    daysClass = daysLeft <= 3 ? 'critical' : (daysLeft <= 7 ? 'warning' : 'info');
                    daysIcon = daysLeft <= 3 ? 'fa-exclamation-circle' : (daysLeft <= 7 ? 'fa-clock' : 'fa-calendar-day');
                    daysRemaining = daysLeft + ' days';
                }

                let reminderHtml = '';
                if (item.reminder_days) {
                    let days = item.reminder_days;
                    if (typeof days === 'string') {
                        try {
                            days = JSON.parse(days);
                        } catch (e) {
                            days = [days];
                        }
                    }
                    if (Array.isArray(days)) {
                        const today = new Date().toISOString().split('T')[0];
                        reminderHtml = '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
                        for (let j = 0; j < days.length; j++) {
                            const d = days[j];
                            const reminderDate = new Date(item.due_date);
                            reminderDate.setDate(reminderDate.getDate() - d);
                            const reminderDateStr = reminderDate.toISOString().split('T')[0];
                            const isToday = (reminderDateStr === today);
                            reminderHtml += `<span class="reminder-badge ${isToday ? 'active' : ''}"><i class="fas fa-bell"></i> ${d} days</span>`;
                        }
                        reminderHtml += '</div>';
                    } else {
                        reminderHtml = `<span class="reminder-badge"><i class="fas fa-bell"></i> ${days} days</span>`;
                    }
                } else {
                    reminderHtml = '<span class="reminder-badge">No reminders set</span>';
                }

                html += `<tr data-item-id="${item.id}">
                    <td><strong>${escapeHtml(item.title)}</strong></td>
                    <td>${item.due_date}</td>
                    <td><span class="status-badge ${statusClass}"><i class="fas ${statusIcon}"></i> ${item.status}</span></td>
                    <td><span class="days-badge ${daysClass}"><i class="fas ${daysIcon}"></i> ${daysRemaining}</span></td>
                    <td>${reminderHtml}</td>
                    <td class="action-buttons-group">
                        <button class="icon-btn" onclick='openEditModal(${JSON.stringify(item)})' title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="action-btn" onclick="markCompleted(${item.id})" title="Mark Complete"><i class="fas fa-check"></i></button>
                        <button class="delete-btn" onclick="deleteComplianceItem(${item.id})" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            }

            html += `</tbody></table><div class="pagination"><div class="page-info">Showing ${start + 1} to ${Math.min(end, filteredItems.length)} of ${filteredItems.length} items</div><div class="pagination-controls"><button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i> Previous</button>`;

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
            const filteredItems = getFilteredItems();
            const totalPages = Math.ceil(filteredItems.length / rowsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable();
        }

        function markCompleted(id) {
            fetch('../../api/api_csv/mark_completed.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Success', 'Compliance item marked as completed', 'success', 3000);
                        loadCompliance();
                    } else {
                        showNotification('Error', data.message || 'Failed to update', 'error', 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error', 'Network error. Please try again.', 'error', 3000);
                });
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

        const notificationStyle = document.createElement('style');
        notificationStyle.textContent = `.notification { position: fixed; top: 20px; right: 20px; z-index: 3000; min-width: 320px; max-width: 400px; background: white; border-radius: 5px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); transform: translateX(400px); transition: transform 0.3s ease-in-out; overflow: hidden; } .notification.show { transform: translateX(0); } .notification-content { padding: 16px; display: flex; align-items: flex-start; gap: 12px; } .notification-icon { flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; } .notification-icon i { font-size: 14px; } .notification-message { flex: 1; } .notification-message strong { display: block; margin-bottom: 4px; } .notification-message p { font-size: 13px; color: #666; margin: 0; } .notification-close { cursor: pointer; color: #999; font-size: 12px; } .notification-close:hover { color: #666; } .notification.success { border-left: 4px solid #10b981; } .notification.success .notification-icon { background: #d1fae5; color: #10b981; } .notification.error { border-left: 4px solid #dc2626; } .notification.error .notification-icon { background: #fee2e2; color: #dc2626; } .notification.info { border-left: 4px solid #3b82f6; } .notification.info .notification-icon { background: #dbeafe; color: #3b82f6; } .notification.warning { border-left: 4px solid #f59e0b; } .notification.warning .notification-icon { background: #fed7aa; color: #f59e0b; }`;
        document.head.appendChild(notificationStyle);

        loadCompliance();
        setInterval(loadCompliance, 30000);
    </script>

</body>

</html>