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
    <title>Expense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

        .stat-icon.flagged {
            background: #fee2e2;
            color: var(--critical);
        }

        .stat-icon.anomaly {
            background: #fed7aa;
            color: var(--warning);
        }

        .stat-icon.categories {
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

        .filter-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            align-items: center;
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

        .show-analysis-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .show-analysis-btn:hover {
            background: #1b5a96;
        }

        .hide-analysis-btn {
            background: var(--gray);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .search-input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 14px;
        }

        .clear-search {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray);
            display: none;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .category-card {
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
            padding: 20px;
            transition: all 0.2s;
        }

        .category-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .category-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
        }

        .category-trend {
            font-size: 13px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .category-trend.up {
            background: #fee2e2;
            color: var(--critical);
        }

        .category-trend.down {
            background: #d1fae5;
            color: var(--success);
        }

        .category-trend.stable {
            background: #eef4fc;
            color: var(--primary);
        }

        .category-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .category-current {
            font-size: 20px;
            font-weight: 800;
            color: var(--dark);
        }

        .category-average {
            font-size: 13px;
            color: var(--gray);
        }

        .progress-bar {
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
            margin: 12px 0;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s;
        }

        .progress-fill.critical {
            background: var(--critical);
        }

        .progress-fill.warning {
            background: var(--warning);
        }

        .progress-fill.normal {
            background: var(--primary);
        }

        .warning-alert {
            margin-top: 12px;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .warning-alert.critical {
            background: #fee2e2;
            color: var(--critical);
        }

        .warning-alert.warning {
            background: #fed7aa;
            color: var(--warning);
        }

        .expenses-table-container {
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
            overflow: hidden;
            margin-top: 24px;
            transition: all 0.3s ease;
        }

        .expenses-table-container.hide {
            display: none;
        }

        .expenses-table {
            width: 100%;
            border-collapse: collapse;
        }

        .expenses-table th {
            background: #f8fafc;
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 1px solid var(--border);
        }

        .expenses-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .expenses-table tr:hover {
            background: #fafcff;
        }

        .flagged-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            background: #fee2e2;
            color: var(--critical);
        }

        .normal-badge {
            background: #d1fae5;
            color: var(--success);
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .analysis-btn {
            background: #eef4fc;
            color: var(--primary);
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

        .analysis-btn:hover {
            background: var(--primary);
            color: white;
        }

        .review-btn {
            background: #fee2e2;
            color: var(--critical);
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

        .review-btn:hover {
            background: var(--critical);
            color: white;
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

        /*  Modal Styles */
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
            border-radius: 16px;
            width: 90%;
            max-width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2);
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
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border-radius: 16px 16px 0 0;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark);
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
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: var(--critical);
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
            border-radius: 0 0 16px 16px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .detail-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px;
        }

        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--gray);
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
        }

        .detail-value.amount {
            color: var(--critical);
            font-size: 22px;
        }

        .flag-reason-box {
            background: #fee2e2;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            border-left: 4px solid var(--critical);
        }

        .flag-reason-box i {
            color: var(--critical);
            margin-right: 8px;
        }

        .flag-reason-box p {
            margin-top: 8px;
            font-size: 14px;
            color: var(--critical);
            line-height: 1.5;
        }

        .duplicate-warning {
            background: #fed7aa;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            border-left: 4px solid var(--warning);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .duplicate-warning i {
            font-size: 20px;
            color: var(--warning);
        }

        .duplicate-warning strong {
            color: var(--warning);
        }

        .duplicate-warning p {
            font-size: 13px;
            color: var(--dark);
            margin-top: 4px;
        }

        .charts-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }

        .chart-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
        }

        .chart-card h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-container {
            height: 200px;
            position: relative;
        }

        .info-box {
            background: #eef4fc;
            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
        }

        .info-box h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box p {
            font-size: 13px;
            color: var(--gray);
            line-height: 1.5;
        }

        .similar-expenses {
            margin-top: 20px;
        }

        .similar-expenses h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .similar-list {
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px;
            max-height: 150px;
            overflow-y: auto;
        }

        .similar-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        .similar-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .charts-row {
                grid-template-columns: 1fr;
            }

            .filter-nav {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            .expenses-table-container {
                overflow-x: auto;
            }

            .expenses-table {
                min-width: 600px;
            }
        }
    </style>
</head>

<body>

    <div class="page-header">
        <h1><i class="fas fa-shield-alt"></i> Expense Watchdog</h1>
        <p>Compares monthly spend per category against 3-month average. Alerts when deviation exceeds 20%.</p>
    </div>

    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-receipt"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="totalCount">0</div>
                <div class="stat-label">Total Expenses</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon flagged"><i class="fas fa-flag"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="flaggedCount">0</div>
                <div class="stat-label">Flagged Expenses</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon anomaly"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="anomalyCount">0</div>
                <div class="stat-label">Active Alerts</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon categories"><i class="fas fa-chart-pie"></i></div>
            <div class="stat-info">
                <div class="stat-value" id="categoryCount">0</div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
    </div>

    <div class="filter-nav" id="filterNav">
        <button class="filter-nav-item active" data-filter="all"><i class="fas fa-list"></i> All Expenses <span class="filter-count" id="allCount">0</span></button>
        <button class="filter-nav-item" data-filter="flagged"><i class="fas fa-flag"></i> Flagged <span class="filter-count" id="flaggedFilterCount">0</span></button>
        <button class="filter-nav-item" data-filter="normal"><i class="fas fa-check-circle"></i> Normal <span class="filter-count" id="normalFilterCount">0</span></button>
        <button class="show-analysis-btn" id="showAnalysisBtn"><i class="fas fa-chart-line"></i> Show Category Analysis</button>
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Search by supplier or category...">
            <button class="clear-search" id="clearSearchBtn" onclick="clearSearch()"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div id="categoryContainer" style="display: none;">
        <div class="empty-state">
            <i class="fas fa-chart-line"></i>
            <h3>No Category Data</h3>
            <p>Upload expense data to see category analysis.</p>
        </div>
    </div>

    <div id="expensesContainer" class="expenses-table-container">
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h3>No Expenses</h3>
            <p>Upload your expense data to get started.</p>
        </div>
    </div>

    <div id="notificationContainer"></div>

    <!-- Enhanced Analysis Modal -->
    <div id="analysisModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-chart-line"></i> Expense Analysis & Category Insights</h3>
                <button class="close-modal" onclick="closeAnalysisModal()">&times;</button>
            </div>
            <div class="modal-body" id="analysisModalBody">
                <div style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-pulse"></i> Loading analysis...</div>
            </div>
            <div class="modal-footer">
                <button class="analysis-btn" onclick="closeAnalysisModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = 'all';
        let currentPage = 1;
        let rowsPerPage = 8;
        let allExpenses = [];
        let categoryData = [];
        let searchTerm = '';
        let duplicateAlerts = [];

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
            loadExpenses();
        }

        document.getElementById('showAnalysisBtn').addEventListener('click', function() {
            const container = document.getElementById('categoryContainer');
            const expensesContainer = document.getElementById('expensesContainer');
            const btn = this;
            if (container.style.display === 'none') {
                container.style.display = 'block';
                expensesContainer.classList.add('hide');
                btn.innerHTML = '<i class="fas fa-chart-line"></i> Hide Category Analysis';
                btn.classList.remove('show-analysis-btn');
                btn.classList.add('hide-analysis-btn');
                loadExpenses();
            } else {
                container.style.display = 'none';
                expensesContainer.classList.remove('hide');
                btn.innerHTML = '<i class="fas fa-chart-line"></i> Show Category Analysis';
                btn.classList.remove('hide-analysis-btn');
                btn.classList.add('show-analysis-btn');
            }
        });

        function loadExpenses() {
            fetch('../../api/api_csv/get_expenses.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStats(data);
                        updateFilterCounts(data);
                        allExpenses = data.expenses;
                        categoryData = data.category_analysis || [];
                        duplicateAlerts = data.duplicate_alerts || [];
                        if (document.getElementById('categoryContainer').style.display !== 'none') {
                            displayCategoryAnalysis();
                        }
                        renderTable();
                    }
                })
                .catch(error => console.error('Error loading expenses:', error));
        }

        function updateStats(data) {
            document.getElementById('totalCount').innerText = data.total || 0;
            document.getElementById('flaggedCount').innerText = data.flagged_count || 0;
            document.getElementById('anomalyCount').innerText = data.anomaly_count || 0;
            document.getElementById('categoryCount').innerText = data.category_count || 0;
        }

        function updateFilterCounts(data) {
            document.getElementById('allCount').innerText = data.total || 0;
            document.getElementById('flaggedFilterCount').innerText = data.flagged_count || 0;
            document.getElementById('normalFilterCount').innerText = (data.total - data.flagged_count) || 0;
        }

        function getFilteredExpenses() {
            let filtered = allExpenses;
            if (currentFilter === 'flagged') filtered = filtered.filter(exp => exp.flagged == 1);
            else if (currentFilter === 'normal') filtered = filtered.filter(exp => exp.flagged == 0);
            if (searchTerm) filtered = filtered.filter(exp => exp.supplier_name.toLowerCase().includes(searchTerm.toLowerCase()) || exp.category.toLowerCase().includes(searchTerm.toLowerCase()));
            return filtered;
        }

        function displayCategoryAnalysis() {
            const container = document.getElementById('categoryContainer');
            if (!categoryData || categoryData.length === 0) {
                container.innerHTML = `<div class="empty-state"><i class="fas fa-chart-line"></i><h3>No Category Data</h3><p>Upload expense data to see category analysis.</p></div>`;
                return;
            }
            let html = `<div class="category-grid">`;
            for (let i = 0; i < categoryData.length; i++) {
                const cat = categoryData[i];
                const percentChange = cat.variance_percent;
                const trendClass = cat.trend === 'up' ? 'up' : (cat.trend === 'down' ? 'down' : 'stable');
                const trendIcon = cat.trend === 'up' ? '↑' : (cat.trend === 'down' ? '↓' : '→');
                const fillClass = cat.variance_percent > 20 ? 'critical' : (cat.variance_percent > 0 ? 'warning' : 'normal');
                const fillWidth = Math.min(Math.abs(cat.variance_percent) * 2, 100);
                html += `<div class="category-card"><div class="category-header"><span class="category-name">${escapeHtml(cat.category)}</span><span class="category-trend ${trendClass}">${trendIcon} ${Math.abs(percentChange)}%</span></div>
                <div class="category-stats"><div><div class="category-current">R${formatNumber(cat.current_month)}</div><div class="category-average">This month</div></div>
                <div style="text-align:right;"><div class="category-average">R${formatNumber(cat.three_month_avg)}</div><div class="category-average">3-month avg</div></div></div>
                <div class="progress-bar"><div class="progress-fill ${fillClass}" style="width: ${fillWidth}%;"></div></div>`;
                if (cat.variance_percent > 20) {
                    html += `<div class="warning-alert critical"><i class="fas fa-exclamation-triangle"></i> ${cat.variance_percent}% above threshold - Review this category immediately</div>`;
                } else if (cat.variance_percent > 0) {
                    html += `<div class="warning-alert warning"><i class="fas fa-clock"></i> ${cat.variance_percent}% above average - Monitor this category</div>`;
                }
                html += `</div>`;
            }
            html += `</div>`;
            container.innerHTML = html;
        }

        function reviewExpense(id) {
            showNotification('Review Started', 'Processing expense review...', 'info', 2000);
            fetch('../../api/api_csv/review_expense.php', {
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
                        showNotification('Review Complete', 'Expense has been reviewed and cleared', 'success', 3000);
                        loadExpenses();
                    } else {
                        showNotification('Review Failed', data.message || 'Something went wrong', 'error', 3000);
                    }
                })
                .catch(error => {
                    showNotification('Error', 'Network error. Please try again.', 'error', 3000);
                });
        }

        function openAnalysisModal(expense) {
            const modal = document.getElementById('analysisModal');
            const modalBody = document.getElementById('analysisModalBody');

            // Find category data for this expense
            const categoryInfo = categoryData.find(c => c.category === expense.category) || null;

            // Find if this expense has duplicates
            const hasDuplicate = duplicateAlerts.some(d => d.id === expense.id);

            // Find similar expenses from same supplier
            const similarExpenses = allExpenses.filter(e => e.supplier_name === expense.supplier_name && e.id !== expense.id).slice(0, 5);

            let flagReasonHtml = '';
            if (expense.flagged == 1 && expense.flagged_reason) {
                flagReasonHtml = `<div class="flag-reason-box"><i class="fas fa-exclamation-triangle"></i> <strong>Flagged Reason</strong><p>${escapeHtml(expense.flagged_reason)}</p></div>`;
            } else if (expense.flagged == 1) {
                flagReasonHtml = `<div class="flag-reason-box"><i class="fas fa-exclamation-triangle"></i> <strong>Flagged Reason</strong><p>This expense was flagged for review. Please investigate the transaction.</p></div>`;
            }

            let duplicateWarningHtml = '';
            if (hasDuplicate) {
                duplicateWarningHtml = `<div class="duplicate-warning"><i class="fas fa-copy"></i><div><strong>⚠ Potential Duplicate Detected</strong><p>This expense may be a duplicate. Check if this supplier has billed you multiple times for the same amount in this month.</p></div></div>`;
            }

            let categoryAnalysisHtml = '';
            if (categoryInfo) {
                const varianceClass = categoryInfo.variance_percent > 20 ? 'critical' : (categoryInfo.variance_percent > 0 ? 'warning' : 'normal');
                categoryAnalysisHtml = `
                <div class="detail-card">
                    <div class="detail-label">Category Trend</div>
                    <div class="detail-value" style="color: ${categoryInfo.variance_percent > 20 ? 'var(--critical)' : (categoryInfo.variance_percent > 0 ? 'var(--warning)' : 'var(--success)')}">
                        ${categoryInfo.variance_percent > 0 ? '↑' : (categoryInfo.variance_percent < 0 ? '↓' : '→')} ${Math.abs(categoryInfo.variance_percent)}% vs avg
                    </div>
                </div>
            `;
            }

            let similarExpensesHtml = '';
            if (similarExpenses.length > 0) {
                similarExpensesHtml = `<div class="similar-expenses"><h4><i class="fas fa-history"></i> Previous Expenses from ${escapeHtml(expense.supplier_name)}</h4><div class="similar-list">`;
                for (let i = 0; i < similarExpenses.length; i++) {
                    const se = similarExpenses[i];
                    similarExpensesHtml += `<div class="similar-item"><span>${se.invoice_date}</span><span><strong>R${formatNumber(se.amount)}</strong></span><span>${se.category}</span><span>${se.flagged == 1 ? '<span class="flagged-badge" style="padding:2px 6px; font-size:10px;">Flagged</span>' : ''}</span></div>`;
                }
                similarExpensesHtml += `</div></div>`;
            }

            modalBody.innerHTML = `
            <div class="detail-grid">
                <div class="detail-card"><div class="detail-label">Supplier Name</div><div class="detail-value">${escapeHtml(expense.supplier_name)}</div></div>
                <div class="detail-card"><div class="detail-label">Category</div><div class="detail-value">${escapeHtml(expense.category)}</div></div>
                <div class="detail-card"><div class="detail-label">Amount</div><div class="detail-value amount">R${formatNumber(expense.amount)}</div></div>
                <div class="detail-card"><div class="detail-label">Invoice Date</div><div class="detail-value">${expense.invoice_date}</div></div>
                <div class="detail-card"><div class="detail-label">Status</div><div class="detail-value">${expense.flagged == 1 ? '<span class="flagged-badge"><i class="fas fa-flag"></i> Flagged for Review</span>' : '<span class="normal-badge"><i class="fas fa-check-circle"></i> Normal</span>'}</div></div>
                ${categoryAnalysisHtml}
                <div class="detail-card"><div class="detail-label">Created At</div><div class="detail-value">${expense.created_at || expense.invoice_date}</div></div>
            </div>
            ${flagReasonHtml}
            ${duplicateWarningHtml}
            <div class="charts-row">
                <div class="chart-card"><h4><i class="fas fa-chart-bar"></i> Category: ${escapeHtml(expense.category)}</h4><div class="chart-container"><canvas id="categoryTrendChart"></canvas></div></div>
                <div class="chart-card"><h4><i class="fas fa-chart-line"></i> Monthly Trend</h4><div class="chart-container"><canvas id="monthlyTrendChart"></canvas></div></div>
            </div>
            ${similarExpensesHtml}
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> About This Analysis</h4>
                <p>The Expense Watchdog compares monthly spend per category against the trailing 3-month average. Any category that deviates by more than 20% generates an alert. Duplicate invoices from the same supplier in the same month are flagged automatically.</p>
                ${expense.flagged == 1 ? `<p style="margin-top: 12px;"><strong>Recommended Action:</strong> Review this transaction with your finance team. If legitimate, mark as reviewed to clear the flag.</p>` : ''}
            </div>
        `;

            modal.classList.add('show');

            setTimeout(() => {
                // Category comparison chart
                if (categoryInfo) {
                    const ctx1 = document.getElementById('categoryTrendChart').getContext('2d');
                    if (window.categoryTrendChartInstance) window.categoryTrendChartInstance.destroy();
                    window.categoryTrendChartInstance = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: ['3-Month Average', 'Current Month'],
                            datasets: [{
                                label: expense.category,
                                data: [categoryInfo.three_month_avg, categoryInfo.current_month],
                                backgroundColor: ['#0c96D4', categoryInfo.current_month > categoryInfo.three_month_avg * 1.2 ? '#dc2626' : '#2373BA'],
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(v) {
                                            return 'R' + v.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Monthly trend chart for this category (last 3 months)
                const monthlyData = getMonthlyTrendForCategory(expense.category);
                const ctx2 = document.getElementById('monthlyTrendChart').getContext('2d');
                if (window.monthlyTrendChartInstance) window.monthlyTrendChartInstance.destroy();
                window.monthlyTrendChartInstance = new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: monthlyData.labels,
                        datasets: [{
                            label: expense.category + ' Spend',
                            data: monthlyData.values,
                            borderColor: '#2373BA',
                            backgroundColor: 'rgba(35,115,186,0.1)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: monthlyData.values.map((v, i) => i === monthlyData.values.length - 1 ? '#dc2626' : '#2373BA'),
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(v) {
                                        return 'R' + v.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }, 150);
        }

        function getMonthlyTrendForCategory(category) {
            const months = ['3 months ago', '2 months ago', 'Last month', 'This month'];
            const values = [0, 0, 0, 0];
            const today = new Date();
            for (let i = 0; i < allExpenses.length; i++) {
                const exp = allExpenses[i];
                if (exp.category === category) {
                    const expDate = new Date(exp.invoice_date);
                    const monthDiff = (today.getFullYear() - expDate.getFullYear()) * 12 + (today.getMonth() - expDate.getMonth());
                    if (monthDiff === 0) values[3] += exp.amount;
                    else if (monthDiff === 1) values[2] += exp.amount;
                    else if (monthDiff === 2) values[1] += exp.amount;
                    else if (monthDiff === 3) values[0] += exp.amount;
                }
            }
            return {
                labels: months,
                values: values
            };
        }

        function closeAnalysisModal() {
            document.getElementById('analysisModal').classList.remove('show');
            if (window.categoryTrendChartInstance) window.categoryTrendChartInstance.destroy();
            if (window.monthlyTrendChartInstance) window.monthlyTrendChartInstance.destroy();
        }

        function renderTable() {
            const container = document.getElementById('expensesContainer');
            const filteredExpenses = getFilteredExpenses();
            const totalPages = Math.ceil(filteredExpenses.length / rowsPerPage);

            if (filteredExpenses.length === 0) {
                container.innerHTML = `<div class="empty-state"><i class="fas fa-receipt"></i><h3>No Expenses Found</h3><p>${searchTerm ? 'No results matching "' + searchTerm + '"' : 'No expenses match the selected filter.'}</p></div>`;
                return;
            }

            if (currentPage > totalPages) currentPage = 1;
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageExpenses = filteredExpenses.slice(start, end);

            let html = `<table class="expenses-table"><thead><tr><th>Category</th><th>Supplier</th><th>Amount</th><th>Invoice Date</th><th>Status</th><th>Action</th></tr></thead><tbody>`;

            for (let i = 0; i < pageExpenses.length; i++) {
                const exp = pageExpenses[i];
                const statusBadge = exp.flagged == 1 ? '<span class="flagged-badge"><i class="fas fa-flag"></i> Flagged</span>' : '<span class="normal-badge"><i class="fas fa-check-circle"></i> Normal</span>';
                html += `<tr><td><strong>${escapeHtml(exp.category)}</strong></td><td>${escapeHtml(exp.supplier_name)}</td><td><strong>R${formatNumber(exp.amount)}</strong></td><td>${exp.invoice_date}</td><td>${statusBadge}</td>
            <td class="action-buttons"><button class="analysis-btn" onclick='openAnalysisModal(${JSON.stringify(exp).replace(/'/g, "&apos;")})'><i class="fas fa-chart-line"></i> Analyse</button>
            ${exp.flagged == 1 ? `<button class="review-btn" onclick="reviewExpense(${exp.id})"><i class="fas fa-check-circle"></i> Review</button>` : ''}</td></tr>`;
            }

            html += `</tbody></table><div class="pagination"><div class="page-info">Showing ${start + 1} to ${Math.min(end, filteredExpenses.length)} of ${filteredExpenses.length} expenses</div><div class="pagination-controls"><button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i> Previous</button>`;

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

            html += `<button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next <i class="fas fa-chevron-right"></i></button></div></div>`;
            container.innerHTML = html;
        }

        function changePage(page) {
            const filteredExpenses = getFilteredExpenses();
            const totalPages = Math.ceil(filteredExpenses.length / rowsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable();
        }

        function formatNumber(amount) {
            return Number(amount).toLocaleString('en-ZA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
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

        document.getElementById('analysisModal').addEventListener('click', function(e) {
            if (e.target === this) closeAnalysisModal();
        });

        const notificationStyle = document.createElement('style');
        notificationStyle.textContent = `.notification { position: fixed; top: 20px; right: 20px; z-index: 3000; min-width: 320px; max-width: 400px; background: white; border-radius: 5px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); transform: translateX(400px); transition: transform 0.3s ease-in-out; overflow: hidden; } .notification.show { transform: translateX(0); } .notification-content { padding: 16px; display: flex; align-items: flex-start; gap: 12px; } .notification-icon { flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; } .notification-icon i { font-size: 14px; } .notification-message { flex: 1; } .notification-message strong { display: block; margin-bottom: 4px; } .notification-message p { font-size: 13px; color: #666; margin: 0; } .notification-close { cursor: pointer; color: #999; font-size: 12px; } .notification-close:hover { color: #666; } .notification.success { border-left: 4px solid #10b981; } .notification.success .notification-icon { background: #d1fae5; color: #10b981; } .notification.error { border-left: 4px solid #dc2626; } .notification.error .notification-icon { background: #fee2e2; color: #dc2626; } .notification.info { border-left: 4px solid #3b82f6; } .notification.info .notification-icon { background: #dbeafe; color: #3b82f6; } .notification.warning { border-left: 4px solid #f59e0b; } .notification.warning .notification-icon { background: #fed7aa; color: #f59e0b; }`;
        document.head.appendChild(notificationStyle);

        loadExpenses();
        setInterval(loadExpenses, 30000);
    </script>

</body>

</html>