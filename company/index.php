<?php
session_start();


//blocking url typing, only with right navigation
if (!isset($_SERVER['HTTP_REFERER'])) {
    echo "<script> window.location.href='https://opsinsyts.co.za';</script>";
    exit;
}

// Check if user is logged in
if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
    echo "<script> window.location.href='https://opsinsyts.co.za';</script>";
    exit();
}

// Get session data
$admin_name = $_SESSION["admin_name"];
$company_name = $_SESSION["company_name"];

// Get initials from name
$name_parts = explode(' ', trim($admin_name));
$initials = '';
if (count($name_parts) >= 2) {
    $initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
} else {
    $initials = strtoupper(substr($admin_name, 0, 2));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>OpsInsyts</title>
    <link rel="icon" href="../src/image/logos.png"
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
            background: #f0f2f5;
            overflow: hidden;
        }

        :root {
            --primary: #2373BA;
            --primary-light: #0c96D4;
            --dark: #464646;
            --gray: #989798;
            --border: #e2e8f0;
        }

        /* Network detection */
        .OpsInsyts-global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        .OpsInsyts-global-loader.hide {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .OpsInsyts-global-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--border);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .OpsInsyts-global-text {
            font-size: 14px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .OpsInsyts-dashboard {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* SIDEBAR */
        .OpsInsyts-sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--border);
            transition: width 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 100;
            overflow-x: hidden;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .OpsInsyts-sidebar.collapsed {
            width: 80px;
        }

        /* Mobile sidebar */
        @media (max-width: 768px) {
            .OpsInsyts-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100%;
                transform: translateX(-100%);
                z-index: 1000;
                transition: transform 0.3s ease;
            }

            .OpsInsyts-sidebar.mobile-open {
                transform: translateX(0);
            }

            .OpsInsyts-sidebar.collapsed {
                width: 280px;
            }

            .OpsInsyts-sidebar.collapsed .OpsInsyts-logo-text,
            .OpsInsyts-sidebar.collapsed .OpsInsyts-nav-text,
            .OpsInsyts-sidebar.collapsed .OpsInsyts-user-details {
                display: block !important;
            }
        }

        .OpsInsyts-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .OpsInsyts-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .OpsInsyts-logo img {
            height: 45px;
            width: auto;
        }

        .OpsInsyts-logo-text {
            white-space: nowrap;
        }

        .OpsInsyts-logo-text span {
            font-size: 18px;
            font-weight: 800;
            color: var(--dark);
        }

        .OpsInsyts-logo-text small {
            font-size: 9px;
            color: var(--gray);
            display: block;
        }

        .OpsInsyts-sidebar.collapsed .OpsInsyts-logo-text,
        .OpsInsyts-sidebar.collapsed .OpsInsyts-nav-text,
        .OpsInsyts-sidebar.collapsed .OpsInsyts-user-details {
            display: none;
        }

        /* Toggle button  */
        .OpsInsyts-toggle-btn {
            position: absolute;
            right: -12px;
            top: 30px;
            width: 24px;
            height: 24px;
            background: var(--primary);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 101;
        }

        .OpsInsyts-sidebar.collapsed .OpsInsyts-toggle-btn {
            transform: rotate(180deg);
        }

        @media (max-width: 768px) {
            .OpsInsyts-toggle-btn {
                display: none;
            }
        }

        .OpsInsyts-nav-menu {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .OpsInsyts-nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--dark);
            margin-bottom: 4px;
            position: relative;
            white-space: nowrap;
        }

        .OpsInsyts-nav-item:hover {
            background: #eef4fc;
            color: var(--primary);
        }

        .OpsInsyts-nav-item.active {
            background: var(--primary);
            color: white;
        }

        .OpsInsyts-nav-item i {
            width: 22px;
            font-size: 18px;
            min-width: 22px;
            text-align: center;
        }

        .OpsInsyts-nav-text {
            white-space: nowrap;
            font-size: 14px;
            font-weight: 500;
        }

        /* Tooltip  */
        .OpsInsyts-tooltip {
            position: fixed;
            background: var(--dark);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .OpsInsyts-tooltip.show {
            opacity: 1;
        }

        .OpsInsyts-sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .OpsInsyts-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .OpsInsyts-user-avatar {
            width: 42px;
            height: 42px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            min-width: 42px;
            text-transform: uppercase;
        }

        .OpsInsyts-user-name {
            font-weight: 700;
            font-size: 14px;
            color: var(--dark);
        }

        .OpsInsyts-user-role {
            font-size: 11px;
            color: var(--gray);
        }

        /* MAIN CONTENT */
        .OpsInsyts-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        .OpsInsyts-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: white;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .OpsInsyts-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .OpsInsyts-mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .OpsInsyts-mobile-menu-btn {
                display: block;
            }
        }

        .OpsInsyts-page-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .OpsInsyts-page-title i {
            font-size: 24px;
            color: var(--primary);
        }

        .OpsInsyts-page-title h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
        }

        /* SEARCH BAR */
        .OpsInsyts-search-container {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 6px 16px;
            gap: 8px;
            min-width: 260px;
        }

        .OpsInsyts-search-container i {
            color: var(--gray);
            font-size: 14px;
        }

        .OpsInsyts-search-input {
            border: none;
            background: transparent;
            padding: 8px 0;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            width: 100%;
        }

        .OpsInsyts-search-input::placeholder {
            color: var(--gray);
        }

        .OpsInsyts-search-btn,
        .OpsInsyts-clear-search {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            transition: all 0.2s;
        }

        .OpsInsyts-search-btn:hover,
        .OpsInsyts-clear-search:hover {
            background: #eef4fc;
        }

        .OpsInsyts-company-badge {
            background: #eef4fc;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 13px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .OpsInsyts-topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .OpsInsyts-topbar-left {
                justify-content: space-between;
            }

            .OpsInsyts-search-container {
                width: 100%;
            }

            .OpsInsyts-company-badge {
                align-self: flex-start;
            }
        }

        .OpsInsyts-iframe-container,
        .OpsInsyts-iframe {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .OpsInsyts-iframe-container::-webkit-scrollbar,
        .OpsInsyts-iframe::-webkit-scrollbar {
            display: none;
        }

        .OpsInsyts-iframe-container {
            flex: 1;
            position: relative;
            background: #ffffff;
            overflow: auto;
        }

        .OpsInsyts-iframe {
            width: 100%;
            height: 100%;
            border: none;
            overflow: auto;
        }

        /* IFRAME LOADING OVERLAY */
        .OpsInsyts-iframe-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.96);
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
            z-index: 100;
        }

        .OpsInsyts-iframe-loading.show {
            display: flex;
        }

        .OpsInsyts-iframe-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--border);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        /* Logout button */
        .OpsInsyts-logout-btn {
            margin-top: 12px;
            width: 100%;
            padding: 10px;
            background: none;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #dc2626;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .OpsInsyts-logout-btn:hover {
            background: #fee2e2;
            border-color: #dc2626;
        }

        .OpsInsyts-sidebar.collapsed .OpsInsyts-logout-btn span {
            display: none;
        }

        /* Mobile overlay */
        .OpsInsyts-mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
            display: none;
        }

        .OpsInsyts-mobile-overlay.show {
            display: block;
        }

        /* Logout Modal Popup */
        .OpsInsyts-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .OpsInsyts-modal.show {
            display: flex;
        }

        .OpsInsyts-modal-content {
            background: white;
            border-radius: 20px;
            padding: 32px;
            max-width: 380px;
            width: 90%;
            text-align: center;
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

        .OpsInsyts-modal-content i {
            font-size: 56px;
            color: #dc2626;
            margin-bottom: 16px;
        }

        .OpsInsyts-modal-content h3 {
            font-size: 22px;
            margin-bottom: 12px;
            color: var(--dark);
        }

        .OpsInsyts-modal-content p {
            color: var(--gray);
            margin-bottom: 24px;
        }

        .OpsInsyts-modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .OpsInsyts-modal-btn {
            padding: 10px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .OpsInsyts-modal-btn.confirm {
            background: #dc2626;
            color: white;
        }

        .OpsInsyts-modal-btn.confirm:hover {
            background: #b91c1c;
        }

        .OpsInsyts-modal-btn.cancel {
            background: #e2e8f0;
            color: var(--dark);
        }

        .OpsInsyts-modal-btn.cancel:hover {
            background: #cbd5e1;
        }

        /* Slide-in Notification */
        .notification {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 3000;
            min-width: 320px;
            max-width: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateX(400px);
            transition: transform 0.3s ease-in-out;
            overflow: hidden;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification-content {
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .notification-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-icon i {
            font-size: 14px;
        }

        .notification-message {
            flex: 1;
        }

        .notification-message strong {
            display: block;
            margin-bottom: 4px;
        }

        .notification-message p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }

        .notification-close {
            cursor: pointer;
            color: #999;
            font-size: 12px;
        }

        .notification-close:hover {
            color: #666;
        }

        .notification.success {
            border-left: 4px solid #10b981;
        }

        .notification.success .notification-icon {
            background: #d1fae5;
            color: #10b981;
        }

        .notification.error {
            border-left: 4px solid #dc2626;
        }

        .notification.error .notification-icon {
            background: #fee2e2;
            color: #dc2626;
        }

        .notification.info {
            border-left: 4px solid #3b82f6;
        }

        .notification.info .notification-icon {
            background: #dbeafe;
            color: #3b82f6;
        }

        .notification.warning {
            border-left: 4px solid #f59e0b;
        }

        .notification.warning .notification-icon {
            background: #fed7aa;
            color: #f59e0b;
        }
    </style>
</head>

<body>

    <!-- Network detection -->
    <div class="OpsInsyts-global-loader" id="globalLoader">
        <div class="OpsInsyts-global-spinner"></div>
        <div class="OpsInsyts-global-text">
            <i class="fas fa-wifi"></i>
            <span>Connecting...</span>
        </div>
    </div>

    <div class="OpsInsyts-dashboard">
        <!-- Mobile Overlay -->
        <div class="OpsInsyts-mobile-overlay" id="mobileOverlay"></div>

        <!-- Logout Confirmation Modal -->
        <div class="OpsInsyts-modal" id="logoutModal">
            <div class="OpsInsyts-modal-content">
                <i class="fas fa-sign-out-alt"></i>
                <h3>Logout Confirmation</h3>
                <p>Are you sure you want to logout from OpsInsyts?</p>
                <div class="OpsInsyts-modal-buttons">
                    <button class="OpsInsyts-modal-btn cancel" onclick="closeLogoutModal()">Cancel</button>
                    <button class="OpsInsyts-modal-btn confirm" onclick="confirmLogout()">Logout</button>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <aside class="OpsInsyts-sidebar" id="sidebar">
            <button class="OpsInsyts-toggle-btn" id="toggleBtn">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="OpsInsyts-sidebar-header">
                <div class="OpsInsyts-logo">
                    <img src="../src/image/logos.png" alt="OpsInsyts Logo" onerror="this.src='https://via.placeholder.com/45x45?text=OI'">
                    <div class="OpsInsyts-logo-text">
                        <span>OpsInsyts</span>
                        <small>AI Operations Assistant</small>
                    </div>
                </div>
            </div>

            <nav class="OpsInsyts-nav-menu">
                <div class="OpsInsyts-nav-item active" data-page="details/dashboard.php" data-tooltip="Dashboard">
                    <i class="fas fa-users"></i>
                    <span class="OpsInsyts-nav-text">Dashboard</span>
                </div>



                <div class="OpsInsyts-nav-item" data-page="details/uploads.php" data-tooltip="Uploads">
                    <i class="fas fa-upload"></i>
                    <span class="OpsInsyts-nav-text">Uploads</span>
                </div>



                <div class="OpsInsyts-nav-item" data-page="details/invoices.php" data-tooltip="Invoices">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span class="OpsInsyts-nav-text">Invoices</span>
                </div>
                <div class="OpsInsyts-nav-item" data-page="details/expenses.php" data-tooltip="Expenses">
                    <i class="fas fa-coins"></i>
                    <span class="OpsInsyts-nav-text">Expenses</span>
                </div>
                <div class="OpsInsyts-nav-item" data-page="details/compliance.php" data-tooltip="Compliance">
                    <i class="fas fa-calendar-check"></i>
                    <span class="OpsInsyts-nav-text">Compliance</span>
                </div>
                <div class="OpsInsyts-nav-item" data-page="details/alerts.php" data-tooltip="Alerts">
                    <i class="fas fa-bell"></i>
                    <span class="OpsInsyts-nav-text">Alerts</span>
                </div>
                <div class="OpsInsyts-nav-item" data-page="details/health.php" data-tooltip=" Health Score ">
                    <i class="fas fa-heartbeat"></i>
                    <span class="OpsInsyts-nav-text"> Health Score </span>
                </div>
                <div class="OpsInsyts-nav-item" data-page="details/reports.php" data-tooltip="Reports" style="display: none;">
                    <i class="fas fa-chart-bar"></i>
                    <span class="OpsInsyts-nav-text">Reports</span>
                </div>
                <div class="OpsInsyts-nav-item" data-page="details/settings.php" data-tooltip="Settings" style="display: none;">
                    <i class="fas fa-cog"></i>
                    <span class="OpsInsyts-nav-text">Settings</span>
                </div>
            </nav>

            <div class="OpsInsyts-sidebar-footer">
                <div class="OpsInsyts-user-info">
                    <div class="OpsInsyts-user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="OpsInsyts-user-details">
                        <div class="OpsInsyts-user-name"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div class="OpsInsyts-user-role"><?php echo htmlspecialchars($company_name); ?></div>
                    </div>
                </div>
                <button class="OpsInsyts-logout-btn" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT WITH IFRAME -->
        <main class="OpsInsyts-main">
            <div class="OpsInsyts-topbar">
                <div class="OpsInsyts-topbar-left">
                    <button class="OpsInsyts-mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="OpsInsyts-page-title">
                        <i id="pageIcon" class="fas fa-users"></i>
                        <h1 id="pageTitle">Dashboard</h1>
                    </div>
                </div>

                <!-- SEARCH FIELD -->
                <div class="OpsInsyts-search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="OpsInsyts-search-input" id="searchInput" placeholder="Search in page... (Ctrl+F)">
                    <button class="OpsInsyts-search-btn" id="searchBtn">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button class="OpsInsyts-clear-search" id="clearSearchBtn" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="OpsInsyts-company-badge">
                    <i class="fas fa-building"></i>
                    <?php echo htmlspecialchars($company_name); ?>
                </div>
            </div>

            <div class="OpsInsyts-iframe-container">
                <div class="OpsInsyts-iframe-loading" id="iframeLoading">
                    <div class="OpsInsyts-iframe-spinner"></div>
                    <div style="color: var(--gray);">Loading page...</div>
                </div>
                <iframe id="contentFrame" class="OpsInsyts-iframe" src="details/dashboard.php"></iframe>
            </div>
        </main>
    </div>
    <!-- Slide-in Notification Container -->
    <div id="notificationContainer"></div>
    <script>
        //NETWORK DETECTION
        const globalLoader = document.getElementById('globalLoader');

        function hideGlobalLoader() {
            globalLoader.classList.add('hide');
            setTimeout(() => {
                if (globalLoader.parentNode) globalLoader.style.display = 'none';
            }, 500);
        }

        function checkNetworkAndProceed() {
            if (navigator.onLine) {
                setTimeout(hideGlobalLoader, 500);
            } else {
                window.addEventListener('online', () => {
                    hideGlobalLoader();
                });
                setTimeout(hideGlobalLoader, 3000);
            }
        }

        checkNetworkAndProceed();

        //TOOLTIP 
        let currentTooltip = null;

        function createTooltip() {
            if (!currentTooltip) {
                currentTooltip = document.createElement('div');
                currentTooltip.className = 'OpsInsyts-tooltip';
                document.body.appendChild(currentTooltip);
            }
        }

        function showTooltip(element, text) {
            if (!sidebar.classList.contains('collapsed')) return;
            if (window.innerWidth <= 768) return;

            createTooltip();

            const rect = element.getBoundingClientRect();
            const tooltip = currentTooltip;

            tooltip.textContent = text;
            tooltip.classList.add('show');

            let left = rect.right + 10;
            let top = rect.top + (rect.height / 2) - 10;

            tooltip.style.visibility = 'hidden';
            tooltip.style.display = 'block';
            const tooltipRect = tooltip.getBoundingClientRect();

            if (left + tooltipRect.width > window.innerWidth) {
                left = rect.left - tooltipRect.width - 10;
            }

            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.style.visibility = 'visible';
        }

        function hideTooltip() {
            if (currentTooltip) {
                currentTooltip.classList.remove('show');
                currentTooltip.style.visibility = 'hidden';
            }
        }

        // SIDEBAR TOGGLE
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileOverlay = document.getElementById('mobileOverlay');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Tooltip event listeners
        const navItems = document.querySelectorAll('.OpsInsyts-nav-item');

        navItems.forEach(item => {
            item.addEventListener('mouseenter', (e) => {
                const tooltipText = item.getAttribute('data-tooltip');
                if (tooltipText) {
                    showTooltip(item, tooltipText);
                }
            });
            item.addEventListener('mouseleave', () => {
                hideTooltip();
            });
        });

        window.addEventListener('scroll', hideTooltip);
        window.addEventListener('resize', hideTooltip);

        // Mobile menu
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.add('mobile-open');
            mobileOverlay.classList.add('show');
        });

        function closeMobileMenu() {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('show');
        }

        mobileOverlay.addEventListener('click', closeMobileMenu);

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
            hideTooltip();
        });

        // IFRAME LOADING
        const contentFrame = document.getElementById('contentFrame');
        const iframeLoading = document.getElementById('iframeLoading');
        const pageTitle = document.getElementById('pageTitle');
        const pageIcon = document.getElementById('pageIcon');

        const pageConfig = {
            'details/dashboard.php': {
                title: 'Dashboard',
                icon: 'fas fa-users'
            },
            'details/uploads.php': {
                title: 'Uploads',
                icon: 'fas fa-upload'
            },
            'details/invoices.php': {
                title: 'Invoices',
                icon: 'fas fa-file-invoice-dollar'
            },
            'details/expenses.php': {
                title: 'Expenses',
                icon: 'fas fa-coins'
            },
            'details/compliance.php': {
                title: 'Compliance',
                icon: 'fas fa-calendar-check'
            },
            'details/alerts.php': {
                title: 'Alerts',
                icon: 'fas fa-bell'
            },
            'details/health.php': {
                title: ' Health Score ',
                icon: 'fas fa-heartbeat'
            },
            'details/reports.php': {
                title: 'Reports',
                icon: 'fas fa-chart-bar'
            },
            'details/settings.php': {
                title: 'Settings',
                icon: 'fas fa-cog'
            }
        };

        function loadPage(page, showLoader = true) {
            if (showLoader) {
                iframeLoading.classList.add('show');
            }

            navItems.forEach(item => item.classList.remove('active'));
            const activeItem = document.querySelector(`[data-page="${page}"]`);
            if (activeItem) activeItem.classList.add('active');

            const config = pageConfig[page] || {
                title: 'Dashboard',
                icon: 'fas fa-chart-line'
            };
            pageTitle.innerText = config.title;
            pageIcon.className = config.icon;

            contentFrame.onload = () => {
                iframeLoading.classList.remove('show');
                clearSearch();
            };

            contentFrame.src = page;

            if (window.innerWidth <= 768) {
                closeMobileMenu();
            }
        }

        navItems.forEach(item => {
            item.addEventListener('click', () => {
                const page = item.getAttribute('data-page');
                loadPage(page);
            });
        });

        //SEARCH FUNCTIONALITY
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        let currentSearchTerm = '';
        let currentHighlightIndex = -1;
        let highlightSpans = [];

        function clearHighlights() {
            try {
                const iframeDoc = contentFrame.contentDocument || contentFrame.contentWindow.document;
                if (!iframeDoc) return;

                const highlighted = iframeDoc.querySelectorAll('.opsinsyts-highlight');
                highlighted.forEach(el => {
                    const parent = el.parentNode;
                    parent.replaceChild(document.createTextNode(el.textContent), el);
                    parent.normalize();
                });
                highlightSpans = [];
            } catch (e) {
                console.log('Cannot access iframe content');
            }
        }

        function performSearch() {
            const searchTerm = searchInput.value.trim();

            if (!searchTerm) {
                clearSearch();
                return;
            }

            currentSearchTerm = searchTerm;

            try {
                const iframeDoc = contentFrame.contentDocument || contentFrame.contentWindow.document;
                if (!iframeDoc) {
                    console.log('Cannot access iframe content');
                    return;
                }

                clearHighlights();

                const body = iframeDoc.body;
                if (!body) return;

                const walker = iframeDoc.createTreeWalker(
                    body,
                    NodeFilter.SHOW_TEXT, {
                        acceptNode: function(node) {
                            if (node.parentElement &&
                                (node.parentElement.tagName === 'SCRIPT' ||
                                    node.parentElement.tagName === 'STYLE' ||
                                    node.parentElement.classList?.contains('opsinsyts-highlight'))) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            if (node.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                                return NodeFilter.FILTER_ACCEPT;
                            }
                            return NodeFilter.FILTER_SKIP;
                        }
                    }
                );

                const matches = [];
                let node;
                while (node = walker.nextNode()) {
                    matches.push(node);
                }

                matches.forEach(matchNode => {
                    const text = matchNode.textContent;
                    const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                    const span = iframeDoc.createElement('span');
                    span.innerHTML = text.replace(regex, `<span class="opsinsyts-highlight" style="background-color: #ffeb3b; color: #000; padding: 0 2px; border-radius: 3px;">$1</span>`);
                    matchNode.parentNode.replaceChild(span, matchNode);
                });

                highlightSpans = Array.from(iframeDoc.querySelectorAll('.opsinsyts-highlight'));

                if (highlightSpans.length > 0) {
                    currentHighlightIndex = 0;
                    highlightSpans[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    highlightSpans[0].style.backgroundColor = '#ff9800';
                    clearSearchBtn.style.display = 'flex';
                    showSearchToast(`Found ${highlightSpans.length} result(s)`);
                } else {
                    showSearchToast(`No results found for "${searchTerm}"`);
                    clearSearchBtn.style.display = 'flex';
                }

            } catch (e) {
                console.error('Search error:', e);
                showSearchToast('Cannot search - iframe content not accessible');
            }
        }

        function escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function showSearchToast(message) {
            let toast = document.getElementById('searchToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'searchToast';
                toast.style.cssText = `
                position: fixed;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                background: var(--dark);
                color: white;
                padding: 10px 20px;
                border-radius: 40px;
                font-size: 13px;
                z-index: 2000;
                animation: fadeIn 0.2s ease;
            `;
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2000);
        }

        function clearSearch() {
            searchInput.value = '';
            currentSearchTerm = '';
            currentHighlightIndex = -1;
            clearHighlights();
            clearSearchBtn.style.display = 'none';
        }

        function navigateHighlight(direction) {
            if (highlightSpans.length === 0) return;

            if (currentHighlightIndex >= 0 && currentHighlightIndex < highlightSpans.length) {
                highlightSpans[currentHighlightIndex].style.backgroundColor = '#ffeb3b';
            }

            currentHighlightIndex += direction;

            if (currentHighlightIndex < 0) currentHighlightIndex = highlightSpans.length - 1;
            if (currentHighlightIndex >= highlightSpans.length) currentHighlightIndex = 0;

            highlightSpans[currentHighlightIndex].style.backgroundColor = '#ff9800';
            highlightSpans[currentHighlightIndex].scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') performSearch();
        });
        clearSearchBtn.addEventListener('click', clearSearch);

        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            if (e.key === 'F3' || (e.ctrlKey && e.key === 'g')) {
                e.preventDefault();
                if (highlightSpans.length > 0) {
                    navigateHighlight(1);
                } else if (currentSearchTerm) {
                    performSearch();
                }
            }
        });

        // LOGOUT
        const logoutModal = document.getElementById('logoutModal');
        const logoutBtn = document.getElementById('logoutBtn');

        function openLogoutModal() {
            logoutModal.classList.add('show');
        }

        function closeLogoutModal() {
            logoutModal.classList.remove('show');
        }

        async function confirmLogout() {
            try {

                const response = await fetch('../api/logout/logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('Logout Success', data.message, 'success');

                    setTimeout(() => {
                        window.location.href = 'https://opsinsyts.co.za/';
                    }, 1500);

                } else {
                    showNotification('Logout Failed', data.message || 'Logout failed', 'error');

                }

            } catch (error) {
                console.error('Logout error:', error);

                showNotification('Logout Failed', 'Something went wrong', 'error');
            }
        }



        function checkSession() {
            fetch('../api/logout/check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (data.session_expired) {


                        showNotification('Session Expired', 'Session expired, login again', 'error');
                        setTimeout(() => {
                            location.reload();
                        }, 2500);


                    }
                });
        }
        // Check every 30 seconds
        setInterval(checkSession, 30000);


//runs every second to mark alerts
setInterval(function() {
    fetch('../api/alert/alert.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'generate_alerts' })
    }).catch(function(e) {
        // Silent fail
        //alert("");
    });
}, 1000);


// Function to get total and update the updateexpenseBadge badge
function updateexpenseBadge() {
    fetch('../api/counts_notify/expense.php')
        .then(response => response.json())
        .then(data => {
            let alertMenuItem = document.querySelector('.OpsInsyts-nav-item[data-page="details/expenses.php"]');
            
            if (!alertMenuItem) return;
            
            // Check if badge already exists
            let existingBadge = alertMenuItem.querySelector('.alert-badge');
            let sidebar = document.getElementById('sidebar');
            
            if (data.total > 0) {
                if (existingBadge) {
                    existingBadge.innerText = data.total;
                    // Hide badge if sidebar is collapsed
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        existingBadge.style.display = 'none';
                    } else {
                        existingBadge.style.display = '';
                    }
                } else {
                    // Create new badge
                    let badge = document.createElement('span');
                    badge.className = 'alert-badge';
                    badge.innerText = data.total;
                    badge.style.cssText = `
                        background-color: #dc2626;
                        color: white;
                        border-radius: 20%;
                        padding: 2px 6px;
                        font-size: 11px;
                        font-weight: bold;
                        margin-left: auto;
                        min-width: 18px;
                        text-align: center;
                    `;
                    alertMenuItem.appendChild(badge);
                    
                    // Hide badge if sidebar is collapsed
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        badge.style.display = 'none';
                    }
                }
            } else {
                if (existingBadge) {
                    existingBadge.remove();
                }
            }
        })
        .catch(function(e) {});
}

// Run every second
setInterval(updateexpenseBadge, 1000);

updateexpenseBadge();




// Function to get total and update the updateinvoiceBadge badge
function updateinvoiceBadge() {
    fetch('../api/counts_notify/invoice.php')
        .then(response => response.json())
        .then(data => {
            let alertMenuItem = document.querySelector('.OpsInsyts-nav-item[data-page="details/invoices.php"]');
            
            if (!alertMenuItem) return;
            
            // Check if badge already exists
            let existingBadge = alertMenuItem.querySelector('.alert-badge');
            let sidebar = document.getElementById('sidebar');
            
            if (data.total > 0) {
                if (existingBadge) {
                    existingBadge.innerText = data.total;
                    // Hide badge if sidebar is collapsed
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        existingBadge.style.display = 'none';
                    } else {
                        existingBadge.style.display = '';
                    }
                } else {
                    // Create new badge
                    let badge = document.createElement('span');
                    badge.className = 'alert-badge';
                    badge.innerText = data.total;
                    badge.style.cssText = `
                        background-color: #dc2626;
                        color: white;
                        border-radius: 20%;
                        padding: 2px 6px;
                        font-size: 11px;
                        font-weight: bold;
                        margin-left: auto;
                        min-width: 18px;
                        text-align: center;
                    `;
                    alertMenuItem.appendChild(badge);
                    
                    // Hide badge if sidebar is collapsed
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        badge.style.display = 'none';
                    }
                }
            } else {
                if (existingBadge) {
                    existingBadge.remove();
                }
            }
        })
        .catch(function(e) {});
}

// Run every second
setInterval(updateinvoiceBadge, 1000);

updateinvoiceBadge();


// Function to get total and update the updatecomplianceBadge badge
function updatecomplianceBadge() {
    fetch('../api/counts_notify/compliance.php')
        .then(response => response.json())
        .then(data => {
            let alertMenuItem = document.querySelector('.OpsInsyts-nav-item[data-page="details/compliance.php"]');
            
            if (!alertMenuItem) return;
            
            // Check if badge already exists
            let existingBadge = alertMenuItem.querySelector('.alert-badge');
            let sidebar = document.getElementById('sidebar');
            
            if (data.total > 0) {
                if (existingBadge) {
                    existingBadge.innerText = data.total;
                    // Hide badge if sidebar is collapsed
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        existingBadge.style.display = 'none';
                    } else {
                        existingBadge.style.display = '';
                    }
                } else {
                    // Create new badge
                    let badge = document.createElement('span');
                    badge.className = 'alert-badge';
                    badge.innerText = data.total;
                    badge.style.cssText = `
                        background-color: #dc2626;
                        color: white;
                        border-radius: 20%;
                        padding: 2px 6px;
                        font-size: 11px;
                        font-weight: bold;
                        margin-left: auto;
                        min-width: 18px;
                        text-align: center;
                    `;
                    alertMenuItem.appendChild(badge);
                    
                    // Hide badge if sidebar is collapsed
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        badge.style.display = 'none';
                    }
                }
            } else {
                if (existingBadge) {
                    existingBadge.remove();
                }
            }
        })
        .catch(function(e) {});
}

// Run every second
setInterval(updatecomplianceBadge, 1000);

updatecomplianceBadge();


// Function to get total and update the alerts badge
function updateAlertBadge() {
    fetch('../api/alert/alerts_count.php')
        .then(response => response.json())
        .then(data => {
            let alertMenuItem = document.querySelector('.OpsInsyts-nav-item[data-page="details/alerts.php"]');
            
            if (!alertMenuItem) return;
            
            // Check if badge already exists
            let existingBadge = alertMenuItem.querySelector('.alert-badge');
            let sidebar = document.getElementById('sidebar');
            
            if (data.total > 0) {
                if (existingBadge) {
                    existingBadge.innerText = data.total;
                    // Hide badge if sidebar is collapsed
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        existingBadge.style.display = 'none';
                    } else {
                        existingBadge.style.display = '';
                    }
                } else {
                    // Create new badge
                    let badge = document.createElement('span');
                    badge.className = 'alert-badge';
                    badge.innerText = data.total;
                    badge.style.cssText = `
                        background-color: #dc2626;
                        color: white;
                        border-radius: 20%;
                        padding: 2px 6px;
                        font-size: 11px;
                        font-weight: bold;
                        margin-left: auto;
                        min-width: 18px;
                        text-align: center;
                    `;
                    alertMenuItem.appendChild(badge);
                    
                    // Hide badge if sidebar is collapsed
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        badge.style.display = 'none';
                    }
                }
            } else {
                if (existingBadge) {
                    existingBadge.remove();
                }
            }
        })
        .catch(function(e) {});
}

// Run every second
setInterval(updateAlertBadge, 1000);

updateAlertBadge();

function checkForAlert() {
    fetch('../api/alert/alert_notify.php')
        .then(response => response.json())
        .then(data => {
            // See what's coming back
            console.log('Response:', data); 
            
            if (data.should_show && data.alert) {
                let notificationType = 'info';
                let title = '';
                
                if (data.alert.severity === 'critical') {
                    notificationType = 'error';
                    title = 'CRITICAL ALERT';
                } else if (data.alert.severity === 'warning') {
                    notificationType = 'warning';
                    title = 'WARNING ALERT';
                } else {
                    notificationType = 'info';
                    title = 'INFO ALERT';
                }
                
                showNotification(title, data.alert.message, notificationType, 8000);
                console.log('Showing alert:', data.alert.id);
            } else {
                console.log('No alert to show. Total alerts:', data.total_alerts);
            }
        })
        .catch(function(e) {
            console.error('Fetch error:', e);
        });
}

// Check every 10 seconds
setInterval(checkForAlert, 10000);
checkForAlert();

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




        logoutBtn.addEventListener('click', openLogoutModal);

        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
                closeLogoutModal();
            }
        });

        // Initial load
        iframeLoading.classList.add('show');
        contentFrame.onload = () => {
            iframeLoading.classList.remove('show');
        };
    </script>

</body>

</html>