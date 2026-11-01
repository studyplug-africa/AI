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
    <title>Upload Invoices</title>
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
            padding: 0px;
            color: #1a1a2e;
            min-height: 100vh;
        }

        :root {
            --primary: #2373BA;
            --primary-light: #0c96D4;
            --dark: #464646;
            --gray: #989798;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --critical: #dc2626;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--gray);
            border: 1px solid var(--border);
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: var(--border);
            cursor: pointer;
            margin-bottom: 24px;
            transition: all 0.2s;
            float: left;
            clear: both;
        }

        .back-btn:hover {
            background: #f8fafc;
            border-color: var(--primary);
            color: var(--primary);
        }

        .page-header {
            text-align: center;
            margin-bottom: 48px;
            position: relative;
            border-radius: 2px;
            overflow: hidden;
        }

        .page-header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=2070&auto=format');
            background-size: cover;
            background-position: center;
            z-index: 0;
        }

        .page-header-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(35, 115, 186, 0.85) 0%, rgba(12, 150, 212, 0.85) 100%);
        }

        .page-header-content {
            position: relative;
            z-index: 1;
            padding: 40px 20px;
        }

        .page-header-content h1 {
            font-size: 32px;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 0;
        }

        .page-header-content h1 i {
            color: white;
            font-size: 32px;
        }

        .page-header-content p {
            color: rgba(255, 255, 255, 0.9);
            margin-top: 12px;
            font-size: 16px;
            margin-bottom: 0;
        }

        /* Three Instruction Cards */
        .instructions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            max-width: 1000px;
            margin: 0 auto 40px auto;
            transition: all 0.3s ease;
        }

        .instructions-grid.hide {
            display: none;
        }

        .instruction-card {
            background: linear-gradient(135deg, #2373BA 0%, #0c96D4 100%);
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            border: none;
            box-shadow: 0 4px 15px rgba(35, 115, 186, 0.2);
        }

        .instruction-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px auto;
        }

        .instruction-icon i {
            font-size: 28px;
            color: white;
        }

        .step-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .instruction-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }

        .instruction-card p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.5;
        }

        .upload-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .upload-area {
            transition: all 0.3s ease;
        }

        .upload-area.hide {
            display: none;
        }

        .drop-zone {
            border: 2px dashed var(--border);
            border-radius: 24px;
            padding: 60px 48px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafcff;
        }

        .drop-zone:hover {
            border-color: var(--primary);
            background: #eef4fc;
        }

        .drop-zone i {
            font-size: 64px;
            color: var(--gray);
            margin-bottom: 20px;
        }

        .drop-zone h4 {
            font-size: 20px;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .drop-zone p {
            font-size: 14px;
            color: var(--gray);
        }

        .file-input {
            display: none;
        }

        .validation-progress {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .validation-progress.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .progress-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--border);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .preview-section {
            background: white;
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
            display: none;
            width: 100%;
            animation: slideUp 0.4s ease;
        }

        .preview-section.show {
            display: block;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .preview-header {
            padding: 20px 24px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .preview-header h3 {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .validation-summary {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .validation-badge {
            padding: 6px 14px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .validation-badge.valid {
            background: #d1fae5;
            color: var(--success);
        }

        .validation-badge.invalid {
            background: #fee2e2;
            color: var(--critical);
        }

        .validation-badge.warning {
            background: #fed7aa;
            color: var(--warning);
        }

        .table-container {
            overflow-x: auto;
            max-height: 500px;
            
            overflow-y: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th {
            background: var(--primary);
            color: white;
            padding: 14px 12px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
        }

        .data-table tr:hover {
            background: #f8fafc;
        }

        .data-table tr.valid-row {
            background: white;
        }

        .data-table tr.invalid-row {
            background: #fee2e2;
        }

        .data-table tr.invalid-row:hover {
            background: #fecaca;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .pagination-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .page-btn {
            background: white;
            border: 1px solid var(--border);
            padding: 8px 14px;
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

        .error-list {
            background: #fee2e2;
            border-left: 4px solid var(--critical);
            padding: 16px 20px;
            border-radius: 12px;
            margin: 20px;
        }

        .error-list h4 {
            color: var(--critical);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-list ul {
            margin-left: 24px;
            color: var(--critical);
            font-size: 13px;
        }

        .error-list li {
            margin: 6px 0;
        }

        .success-message {
            background: #d1fae5;
            border-left: 4px solid var(--success);
            padding: 16px 20px;
            border-radius: 12px;
            margin: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            background: #fafcff;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: #1b5a96;
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: white;
            color: var(--dark);
            border: 1px solid var(--border);
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 3000;
            min-width: 320px;
            max-width: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
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

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @media (max-width: 1024px) {
            .instructions-grid {
                gap: 16px;
            }

            .instruction-card {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .page-header-content h1 {
                font-size: 24px;
            }

            .instructions-grid {
                grid-template-columns: 1fr;
            }

            .preview-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            .notification {
                left: 20px;
                right: 20px;
                min-width: auto;
            }

            .pagination-container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>



    <div class="page-header">
        <div class="page-header-bg"></div>
        <div class="page-header-content">
            <button class="back-btn" onclick="goBack()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h1>
                <i class="fas fa-file-invoice-dollar"></i>
                Upload Invoices
            </h1>
            <p>Upload your CSV file. System will validate the format before storing to database.</p>
        </div>
    </div>

    <div class="upload-container">
        <!-- Three Instruction Cards -->
        <div class="instructions-grid" id="instructionsGrid">
            <div class="instruction-card">
                <div class="instruction-icon">
                    <i class="fas fa-download"></i>
                </div>
                <div class="step-label">STEP 01</div>
                <h3>Download Template</h3>
                <p>Download our CSV template and fill it with your invoice data</p>
            </div>
            <div class="instruction-card">
                <div class="instruction-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="step-label">STEP 02</div>
                <h3>Validate Format</h3>
                <p>System checks your data format automatically</p>
            </div>
            <div class="instruction-card">
                <div class="instruction-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="step-label">STEP 03</div>
                <h3>Upload to Database</h3>
                <p>Click proceed when all rows are valid</p>
            </div>
        </div>

        <!-- Upload Area -->
        <div class="upload-area" id="uploadArea">
            <div class="drop-zone" id="dropZone">
                <i class="fas fa-cloud-upload-alt"></i>
                <h4>Drag & Drop your CSV file here</h4>
                <p>or click to browse</p>
                <input type="file" class="file-input" id="fileInput" accept=".csv">
            </div>
        </div>

        <!-- Validation Progress -->
        <div class="validation-progress" id="validationProgress">
            <div class="progress-spinner"></div>
            <h3 style="color: var(--dark);">Validating CSV file...</h3>
            <p style="color: var(--gray);">Please wait while we check your data format</p>
        </div>

        <!-- Preview Section -->
        <div class="preview-section" id="previewSection">
            <div class="preview-header">
                <h3><i class="fas fa-table"></i> Data Preview & Validation</h3>
                <div class="validation-summary" id="validationSummary"></div>
            </div>
            <div class="table-container" id="tableContainer">
                <table class="data-table" id="dataTable">
                    <thead id="tableHeader"></thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
            <div id="paginationContainer" class="pagination-container" style="display: none;"></div>
            <div id="errorsContainer"></div>
            <div class="action-buttons">
                <button class="btn-secondary" onclick="resetUpload()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-primary" id="uploadBtn" disabled>
                    <i class="fas fa-database"></i> Proceed to Upload
                </button>
            </div>
        </div>
    </div>

    <div id="notificationContainer"></div>

    <script>
        let parsedData = [];
        let validationErrors = [];
        let isValid = false;
        let currentPage = 1;
        let rowsPerPage = 8;
        let paginatedData = [];

        const uploadArea = document.getElementById('uploadArea');
        const instructionsGrid = document.getElementById('instructionsGrid');
        const validationProgress = document.getElementById('validationProgress');
        const previewSection = document.getElementById('previewSection');
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const tableHeader = document.getElementById('tableHeader');
        const tableBody = document.getElementById('tableBody');
        const validationSummary = document.getElementById('validationSummary');
        const errorsContainer = document.getElementById('errorsContainer');
        const uploadBtn = document.getElementById('uploadBtn');
        const paginationContainer = document.getElementById('paginationContainer');

        function showNotification(title, message, type = 'info', duration = 5000) {
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

        function goBack() {
            window.location.href = '../../../company/details/uploads.php';
        }

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--primary)';
            dropZone.style.background = '#eef4fc';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = 'var(--border)';
            dropZone.style.background = '#fafcff';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--border)';
            dropZone.style.background = '#fafcff';
            const file = e.dataTransfer.files[0];
            if (file && file.type === 'text/csv') {
                processFile(file);
            } else {
                showNotification('Invalid File', 'Please upload a valid CSV file', 'error');
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                processFile(e.target.files[0]);
            }
        });

        function processFile(file) {
            uploadArea.classList.add('hide');
            instructionsGrid.classList.add('hide');
            validationProgress.classList.add('show');
            previewSection.classList.remove('show');

            setTimeout(() => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const content = event.target.result;
                    parseCSV(content);
                };
                reader.readAsText(file);
            }, 800);
        }

        function parseCSV(content) {
            const lines = content.trim().split('\n');

            if (lines.length < 2) {
                validationProgress.classList.remove('show');
                uploadArea.classList.remove('hide');
                instructionsGrid.classList.remove('hide');
                showNotification('Invalid CSV', 'CSV file must contain headers and at least one data row', 'error');
                return;
            }

            const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
            const expectedHeaders = ['client_name', 'amount', 'due_date', 'status'];

            const missingHeaders = expectedHeaders.filter(h => !headers.includes(h));
            if (missingHeaders.length > 0) {
                validationProgress.classList.remove('show');
                uploadArea.classList.remove('hide');
                instructionsGrid.classList.remove('hide');
                showNotification('Invalid Format', 'Missing required columns: ' + missingHeaders.join(', '), 'error');
                return;
            }

            parsedData = [];
            validationErrors = [];

            for (let i = 1; i < lines.length; i++) {
                const values = lines[i].split(',').map(v => v.trim());
                const row = {};
                const rowErrors = [];

                headers.forEach((header, idx) => {
                    row[header] = values[idx] || '';
                });

                if (!row.client_name) {
                    rowErrors.push('client_name is required');
                    row._valid = false;
                }

                const amount = parseFloat(row.amount);
                if (isNaN(amount) || amount <= 0) {
                    rowErrors.push('amount must be a positive number');
                    row._valid = false;
                } else {
                    row.amount_cents = Math.round(amount * 100);
                    row.display_amount = 'R ' + amount.toLocaleString();
                }

                const dueDateRegex = /^\d{4}-\d{2}-\d{2}$/;
                if (!dueDateRegex.test(row.due_date)) {
                    rowErrors.push('due_date must be YYYY-MM-DD format');
                    row._valid = false;
                } else {
                    const date = new Date(row.due_date);
                    if (isNaN(date.getTime())) {
                        rowErrors.push('due_date is invalid');
                        row._valid = false;
                    }
                }

                const validStatuses = ['pending', 'overdue', 'paid'];
                if (!validStatuses.includes(row.status.toLowerCase())) {
                    rowErrors.push('status must be: pending, overdue, or paid');
                    row._valid = false;
                } else {
                    row.status = row.status.toLowerCase();
                }

                row._errors = rowErrors;
                row._valid = rowErrors.length === 0;
                if (!row._valid) validationErrors.push({
                    row: i,
                    errors: rowErrors
                });
                parsedData.push(row);
            }

            validationProgress.classList.remove('show');
            displayPreview();
        }

        function renderTablePage() {
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageRows = parsedData.slice(start, end);

            tableBody.innerHTML = '';
            for (let i = 0; i < pageRows.length; i++) {
                const row = pageRows[i];
                const actualIndex = start + i;
                const rowClass = row._valid ? 'valid-row' : 'invalid-row';

                let statusBadgeClass = '';
                let statusIcon = '';
                if (row.status === 'overdue') {
                    statusBadgeClass = 'invalid';
                    statusIcon = '<i class="fas fa-exclamation-circle"></i> ';
                } else if (row.status === 'pending') {
                    statusBadgeClass = 'warning';
                    statusIcon = '<i class="fas fa-clock"></i> ';
                } else {
                    statusBadgeClass = 'valid';
                    statusIcon = '<i class="fas fa-check-circle"></i> ';
                }

                tableBody.innerHTML += `
                <tr class="${rowClass}">
                    <td>${escapeHtml(row.client_name)}</td>
                    <td><strong>${row.display_amount || 'R ' + parseFloat(row.amount).toLocaleString()}</strong></td>
                    <td>${escapeHtml(row.due_date)}</td>
                    <td><span class="validation-badge ${statusBadgeClass}">${statusIcon}${row.status}</span></td>
                    <td>${row._valid ? '<i class="fas fa-check-circle" style="color: var(--success); font-size: 18px;"></i>' : '<i class="fas fa-times-circle" style="color: var(--critical); font-size: 18px;"></i>'}</td>
                </tr>
            `;
            }
        }

        function setupPagination() {
            const totalPages = Math.ceil(parsedData.length / rowsPerPage);

            if (totalPages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            paginationContainer.style.display = 'flex';

            let paginationHtml = `
            <div class="page-info">
                Showing ${((currentPage - 1) * rowsPerPage) + 1} to ${Math.min(currentPage * rowsPerPage, parsedData.length)} of ${parsedData.length} rows
            </div>
            <div class="pagination-controls">
                <button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
        `;

            const maxButtons = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);

            if (endPage - startPage < maxButtons - 1) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            if (startPage > 1) {
                paginationHtml += `<button class="page-btn" onclick="changePage(1)">1</button>`;
                if (startPage > 2) paginationHtml += `<button class="page-btn" disabled>...</button>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) paginationHtml += `<button class="page-btn" disabled>...</button>`;
                paginationHtml += `<button class="page-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
            }

            paginationHtml += `
                <button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;

            paginationContainer.innerHTML = paginationHtml;
        }

        function changePage(page) {
            const totalPages = Math.ceil(parsedData.length / rowsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTablePage();
            setupPagination();
            document.getElementById('tableContainer').scrollTop = 0;
        }

        function displayPreview() {
            const headers = ['client_name', 'amount', 'due_date', 'status', 'valid'];
            tableHeader.innerHTML = '<tr>' + headers.map(h => '<th>' + h.toUpperCase() + '</th>').join('') + '</tr>';

            currentPage = 1;
            renderTablePage();

            const validCount = parsedData.filter(r => r._valid).length;
            const invalidCount = validationErrors.length;
            const totalCount = parsedData.length;

            if (invalidCount === 0) {
                validationSummary.innerHTML = `
                <span class="validation-badge valid"><i class="fas fa-check"></i> ${validCount} valid rows</span>
                <span class="validation-badge valid"><i class="fas fa-chart-line"></i> ${totalCount} total rows</span>
            `;
                isValid = true;
                uploadBtn.disabled = false;
                errorsContainer.innerHTML = '';
                showNotification('Validation Passed', `All ${validCount} rows are valid and ready to upload`, 'success', 4000);
            } else {
                validationSummary.innerHTML = `
                <span class="validation-badge valid"><i class="fas fa-check"></i> ${validCount} valid</span>
                <span class="validation-badge invalid"><i class="fas fa-times"></i> ${invalidCount} invalid</span>
                <span class="validation-badge warning"><i class="fas fa-chart-line"></i> ${totalCount} total</span>
            `;
                isValid = false;
                uploadBtn.disabled = true;

                let errorsHtml = '<div class="error-list"><h4><i class="fas fa-exclamation-triangle"></i> Validation Errors Found</h4><ul>';
                for (let i = 0; i < validationErrors.length; i++) {
                    const err = validationErrors[i];
                    errorsHtml += `<li><strong>Row ${err.row}:</strong> ${err.errors.join(', ')}</li>`;
                }
                errorsHtml += '</ul><p style="margin-top: 12px; font-size: 12px;"><i class="fas fa-info-circle"></i> Please fix these errors in your CSV and upload again.</p></div>';
                errorsContainer.innerHTML = errorsHtml;
                showNotification('Validation Failed', `${invalidCount} row(s) have errors. Please check the details below.`, 'error', 5000);
            }

            setupPagination();
            previewSection.classList.add('show');
        }

        function resetUpload() {
            parsedData = [];
            validationErrors = [];
            isValid = false;
            currentPage = 1;
            previewSection.classList.remove('show');
            uploadArea.classList.remove('hide');
            instructionsGrid.classList.remove('hide');
            fileInput.value = '';
            uploadBtn.disabled = true;
            errorsContainer.innerHTML = '';
            showNotification('Upload Cancelled', 'You can select a new file to upload', 'info', 3000);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

document.getElementById('uploadBtn').addEventListener('click', async () => {
    if (!isValid || parsedData.length === 0) {
        showNotification('Cannot Upload', 'Please fix validation errors before uploading', 'error');
        return;
    }

    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Uploading...';

    const invoices = [];
    for (let i = 0; i < parsedData.length; i++) {
        const row = parsedData[i];
        if (row._valid) {
            invoices.push({
                client_name: row.client_name,
                amount_cents: row.amount_cents,
                due_date: row.due_date,
                status: row.status,
                company_id: '<?php echo $company_id; ?>'
            });
        }
    }

    try {
        const response = await fetch('../../../api/uploads/invoices.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                invoices: invoices,
                company_id: '<?php echo $company_id; ?>'
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Upload Successful', result.message || `${invoices.length} invoices have been uploaded`, 'success', 5000);
            setTimeout(() => {
                window.location.href = '../../../company/details/uploads/upload_invoice.php';
            }, 5000);
        } else {
            showNotification('Upload Failed', result.message || 'Something went wrong', 'error');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-database"></i> Proceed to Upload';
        }

    } catch (error) {
        showNotification('Upload Failed', 'Network error. Please try again.', 'error');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-database"></i> Proceed to Upload';
    }
});
    </script>

</body>

</html>