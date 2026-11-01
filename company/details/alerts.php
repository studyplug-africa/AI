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
    <title>Alert Feed | OpsInsyts</title>
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
            padding: 10px;
        }

        :root {
            --primary: #2373BA;
            --primary-light: #0c96D4;
            --dark: #464646;
            --gray: #989798;
            --border: #e2e8f0;
            --critical: #dc2626;
            --warning: #f59e0b;
            --info: #3b82f6;
            --success: #10b981;
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

        .stat-icon.critical {
            background: #fee2e2;
            color: var(--critical);
        }

        .stat-icon.warning {
            background: #fed7aa;
            color: var(--warning);
        }

        .stat-icon.info {
            background: #dbeafe;
            color: var(--info);
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

        .filter-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            background: white;
            padding: 8px 0;
            position: sticky;
            top: 0;
            z-index: 100;
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

        .alerts-table-container {
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .alerts-table {
            width: 100%;
            border-collapse: collapse;
        }

        .alerts-table th {
            background: #f8fafc;
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 1px solid var(--border);
        }

        .alerts-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .alerts-table tr:last-child td {
            border-bottom: none;
        }

        .alerts-table tr:hover {
            background: #fafcff;
        }

        .severity-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .severity-badge.critical {
            background: #fee2e2;
            color: var(--critical);
        }

        .severity-badge.warning {
            background: #fed7aa;
            color: var(--warning);
        }

        .severity-badge.info {
            background: #dbeafe;
            color: var(--info);
        }

        .module-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 12px;
            background: #f1f5f9;
            color: var(--dark);
        }

        .module-badge i {
            font-size: 11px;
        }

        .resolve-btn {
            background: white;
            border: 1px solid var(--border);
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .resolve-btn:hover {
            background: #f8fafc;
            border-color: var(--success);
            color: var(--success);
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
            border-radius: 8px;
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
            border-radius: 10px;
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

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
            }

            .stat-icon i {
                font-size: 20px;
            }

            .stat-value {
                font-size: 24px;
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

            .alerts-table th,
            .alerts-table td {
                padding: 10px 12px;
            }
        }

        @media (max-width: 640px) {
            .alerts-table-container {
                overflow-x: auto;
            }

            .alerts-table {
                min-width: 600px;
            }
        }
    </style>
</head>

<body>
    <br>
    <div class="page-header">
        <h1>
            <i class="fas fa-bell"></i>
            Alert Feed
        </h1>
        <p>All your unresolved alerts in one place. Review and take action.</p>
    </div>

    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="totalCount">0</div>
                <div class="stat-label">Total Alerts</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon critical">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="criticalCount">0</div>
                <div class="stat-label">Critical</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="warningCount">0</div>
                <div class="stat-label">Warning</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="infoCount">0</div>
                <div class="stat-label">Info</div>
            </div>
        </div>
    </div>



    <div class="filter-nav" id="filterNav">
        <button class="filter-nav-item active" data-filter="all">
            <i class="fas fa-list"></i> All Alerts
            <span class="filter-count" id="allCount">0</span>
        </button>
        <button class="filter-nav-item" data-filter="critical">
            <i class="fas fa-exclamation-circle"></i> Critical
            <span class="filter-count" id="criticalFilterCount">0</span>
        </button>
        <button class="filter-nav-item" data-filter="warning">
            <i class="fas fa-exclamation-triangle"></i> Warning
            <span class="filter-count" id="warningFilterCount">0</span>
        </button>
        <button class="filter-nav-item" data-filter="info">
            <i class="fas fa-info-circle"></i> Info
            <span class="filter-count" id="infoFilterCount">0</span>
        </button>
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Search alerts by message...">
            <button class="clear-search" id="clearSearchBtn" onclick="clearSearch()"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div id="alertsContainer">
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h3>No Alerts</h3>
            <p>You have no unresolved alerts. Good job!</p>
        </div>
    </div>

    <div id="notificationContainer"></div>

    <script>
        let currentFilter = 'all';
        let currentPage = 1;
        let rowsPerPage = 8;
        let allAlerts = [];
        let lastAlertCount = 0;
        let searchTerm = '';

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
            </div>
        `;

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

        function loadAlerts() {
            fetch('../../api/alerts/get_alerts.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const newAlertCount = data.total;
                        if (newAlertCount > lastAlertCount && lastAlertCount !== 0) {
                            const newAlerts = newAlertCount - lastAlertCount;
                            showNotification('New Alerts', `You have ${newAlerts} new alert(s)`, 'warning', 5000);
                        }
                        lastAlertCount = newAlertCount;

                        updateStats(data);
                        updateFilterCounts(data);
                        allAlerts = data.alerts;
                        renderTable();
                    }
                })
                .catch(error => console.error('Error loading alerts:', error));
        }

        function updateStats(data) {
            document.getElementById('totalCount').innerText = data.total || 0;
            document.getElementById('criticalCount').innerText = data.critical_count || 0;
            document.getElementById('warningCount').innerText = data.warning_count || 0;
            document.getElementById('infoCount').innerText = data.info_count || 0;
        }

        function updateFilterCounts(data) {
            document.getElementById('allCount').innerText = data.total || 0;
            document.getElementById('criticalFilterCount').innerText = data.critical_count || 0;
            document.getElementById('warningFilterCount').innerText = data.warning_count || 0;
            document.getElementById('infoFilterCount').innerText = data.info_count || 0;
        }

        function getFilteredAlerts() {
            let filtered = allAlerts;
            if (currentFilter !== 'all') {
                filtered = filtered.filter(alert => alert.severity === currentFilter);
            }
            if (searchTerm) {
                filtered = filtered.filter(alert => alert.message.toLowerCase().includes(searchTerm.toLowerCase()));
            }
            return filtered;
        }

        function renderTable() {
            const container = document.getElementById('alertsContainer');
            const filteredAlerts = getFilteredAlerts();
            const totalPages = Math.ceil(filteredAlerts.length / rowsPerPage);

            if (filteredAlerts.length === 0) {
                container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No ${currentFilter !== 'all' ? currentFilter : ''} Alerts</h3>
                    <p>${searchTerm ? 'No results matching "' + searchTerm + '"' : 'You have no ' + (currentFilter !== 'all' ? currentFilter : '') + ' unresolved alerts.'}</p>
                </div>
            `;
                return;
            }

            if (currentPage > totalPages) currentPage = 1;

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageAlerts = filteredAlerts.slice(start, end);

            let html = `
            <div class="alerts-table-container">
                <table class="alerts-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Severity</th>
                            <th style="width: 100px;">Module</th>
                            <th>Message</th>
                            <th style="width: 120px;">Created</th>
                            <th style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

            for (let i = 0; i < pageAlerts.length; i++) {
                const alert = pageAlerts[i];

                let severityIcon = '';
                if (alert.severity === 'critical') severityIcon = 'fa-exclamation-circle';
                else if (alert.severity === 'warning') severityIcon = 'fa-exclamation-triangle';
                else severityIcon = 'fa-info-circle';

                let moduleIcon = '';
                if (alert.module === 'invoices') moduleIcon = 'fa-file-invoice-dollar';
                else if (alert.module === 'expenses') moduleIcon = 'fa-receipt';
                else if (alert.module === 'compliance') moduleIcon = 'fa-calendar-check';
                else moduleIcon = 'fa-bell';

                html += `
                <tr data-alert-id="${alert.id}">
                    <td><span class="severity-badge ${alert.severity}"><i class="fas ${severityIcon}"></i> ${alert.severity}</span></td>
                    <td><span class="module-badge"><i class="fas ${moduleIcon}"></i> ${alert.module}</span></td>
                    <td>${escapeHtml(alert.message)}</td>
                    <td style="white-space: nowrap;">${formatDate(alert.created_at)}</td>
                    <td><button class="resolve-btn" onclick="resolveAlert(${alert.id})"><i class="fas fa-check"></i> Resolve</button></td>
                </tr>
            `;
            }

            html += `
                    </tbody>
                </table>
                <div class="pagination">
                    <div class="page-info">Showing ${start + 1} to ${Math.min(end, filteredAlerts.length)} of ${filteredAlerts.length} alerts</div>
                    <div class="pagination-controls">
                        <button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i> Previous</button>
        `;

            if (totalPages <= 5) {
                for (let i = 1; i <= totalPages; i++) {
                    html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
                }
            } else {
                if (currentPage > 2) {
                    html += `<button class="page-btn" onclick="changePage(1)">1</button>`;
                    if (currentPage > 3) html += `<span style="padding: 0 4px;">...</span>`;
                }

                for (let i = Math.max(1, currentPage - 1); i <= Math.min(totalPages, currentPage + 1); i++) {
                    html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
                }

                if (currentPage < totalPages - 1) {
                    if (currentPage < totalPages - 2) html += `<span style="padding: 0 4px;">...</span>`;
                    html += `<button class="page-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
                }
            }

            html += `
                        <button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next <i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        `;

            container.innerHTML = html;
        }

        function changePage(page) {
            const filteredAlerts = getFilteredAlerts();
            const totalPages = Math.ceil(filteredAlerts.length / rowsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable();
        }

        function resolveAlert(alertId) {
            fetch('../../api/alerts/resolve_alert.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        alert_id: alertId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Alert Resolved', 'The alert has been marked as resolved', 'success', 3000);
                        loadAlerts();
                    } else {
                        showNotification('Error', data.message || 'Failed to resolve alert', 'error', 3000);
                    }
                })
                .catch(error => {
                    console.error('Error resolving alert:', error);
                    showNotification('Error', 'Network error. Please try again.', 'error', 3000);
                });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);

            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            return date.toLocaleDateString();
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
        notificationStyle.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 3000;
            min-width: 320px;
            max-width: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            transform: translateX(400px);
            transition: transform 0.3s ease-in-out;
            overflow: hidden;
        }
        .notification.show { transform: translateX(0); }
        .notification-content { padding: 16px; display: flex; align-items: flex-start; gap: 12px; }
        .notification-icon { flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .notification-icon i { font-size: 14px; }
        .notification-message { flex: 1; }
        .notification-message strong { display: block; margin-bottom: 4px; }
        .notification-message p { font-size: 13px; color: #666; margin: 0; }
        .notification-close { cursor: pointer; color: #999; font-size: 12px; }
        .notification-close:hover { color: #666; }
        .notification.success { border-left: 4px solid #10b981; }
        .notification.success .notification-icon { background: #d1fae5; color: #10b981; }
        .notification.error { border-left: 4px solid #dc2626; }
        .notification.error .notification-icon { background: #fee2e2; color: #dc2626; }
        .notification.info { border-left: 4px solid #3b82f6; }
        .notification.info .notification-icon { background: #dbeafe; color: #3b82f6; }
        .notification.warning { border-left: 4px solid #f59e0b; }
        .notification.warning .notification-icon { background: #fed7aa; color: #f59e0b; }
    `;
        document.head.appendChild(notificationStyle);

        loadAlerts();
        setInterval(loadAlerts, 10000);
    </script>

</body>

</html>