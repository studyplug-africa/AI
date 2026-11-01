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
    <title>Upload Data</title>
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

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            max-width: 1300px;
            margin: 0 auto 48px auto;
        }

        .step-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 24px;
            padding: 32px;
            text-align: center;
            color: white;
            box-shadow: 0 4px 15px rgba(35, 115, 186, 0.2);
        }

        .step-number-circle {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            margin: 0 auto 20px auto;
        }

        .step-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .step-card p {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.5;
        }

        .upload-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            max-width: 1300px;
            margin: 0 auto;
        }

        .upload-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .upload-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.1);
        }

        .upload-card-header {
            padding: 32px;
            text-align: center;
            background: #fafcff;
            border-bottom: 1px solid var(--border);
        }

        .upload-card-header i {
            font-size: 56px;
            margin-bottom: 20px;
        }

        .upload-card-header.invoices i {
            color: var(--primary);
        }

        .upload-card-header.expenses i {
            color: var(--primary-light);
        }

        .upload-card-header.compliance i {
            color: var(--primary);
        }

        .upload-card-header h3 {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
        }

        .upload-card-body {
            padding: 24px;
        }

        .upload-card-body p {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .card-buttons {
            display: flex;
            gap: 12px;
            padding: 0 24px 24px;
        }

        .btn-upload {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-upload:hover {
            background: #1b5a96;
        }

        .btn-guide {
            flex: 1;
            background: white;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-guide:hover {
            background: #eef4fc;
        }

        .modal {
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

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            max-width: 80%;
            width: 90%;
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
            padding: 24px 28px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            border-radius: 24px 24px 0 0;
        }

        .modal-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: var(--critical);
        }

        .modal-body {
            padding: 28px;
        }

        .guide-section {
            margin-bottom: 28px;
        }

        .guide-section h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guide-section h3 i {
            color: var(--primary);
        }

        .guide-section p {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .csv-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 13px;
            border-radius: 12px;
            overflow: hidden;
        }

        .csv-table th {
            background: var(--primary);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .csv-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            background: white;
        }

        .csv-table tr:last-child td {
            border-bottom: none;
        }

        .csv-table .example-row {
            background: #f8fafc;
        }

        .note-box {
            background: #fef3c7;
            border-left: 4px solid var(--warning);
            padding: 16px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .note-box i {
            color: var(--warning);
            margin-right: 8px;
        }

        .download-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            margin: 16px 0;
        }

        .download-btn:hover {
            background: #1b5a96;
        }

        @media (max-width: 1024px) {
            body {
                padding: 30px 20px;
            }

            .steps-grid,
            .upload-grid {
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .steps-grid,
            .upload-grid {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .modal-body {
                padding: 20px;
            }

            .csv-table th,
            .csv-table td {
                padding: 8px;
                font-size: 11px;
            }
        }



        /* Slide-in Notification */
        .notification {
            position: fixed;
            top: 10px;
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

    <div class="page-header">
        <div class="page-header-bg"></div>
        <div class="page-header-content">
            <h1>
                <i class="fas fa-upload"></i>
                Upload Your Data
            </h1>
            <p>Select which type of data you want to import. Click "Guide" to see the CSV format, then "Upload" to begin.</p>
        </div>
    </div>

    <div class="steps-grid">
        <div class="step-card">
            <div class="step-number-circle">1</div>
            <h3><i class="fas fa-download"></i> Download Template</h3>
            <p>Download the CSV template for your selected data type , click on the guide button of the csv you want to upload</p>
        </div>
        <div class="step-card">
            <div class="step-number-circle">2</div>
            <h3><i class="fas fa-pen"></i> Fill Your Data</h3>
            <p>Open the CSV file and fill it with your business data, make sure to follow the template</p>
        </div>
        <div class="step-card">
            <div class="step-number-circle">3</div>
            <h3><i class="fas fa-upload"></i> Upload & Import</h3>
            <p>Upload the filled CSV file. System validates and generates alerts</p>
        </div>
    </div>

    <div class="upload-grid">
        <div class="upload-card">
            <div class="upload-card-header invoices">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3>Invoices</h3>
            </div>
            <div class="upload-card-body">
                <p>Upload your outstanding invoices to track overdue accounts, receivables aging, and cash flow. The system will automatically generate overdue alerts based on due dates.</p>
            </div>
            <div class="card-buttons">
                <button class="btn-guide" onclick="openGuide('invoices')">
                    <i class="fas fa-question-circle"></i> Guide
                </button>
                <button class="btn-upload" onclick="navigateTo('upload_invoice.php')">
                    <i class="fas fa-arrow-right"></i> Upload
                </button>
            </div>
        </div>

        <div class="upload-card">
            <div class="upload-card-header expenses">
                <i class="fas fa-receipt"></i>
                <h3>Expenses</h3>
            </div>
            <div class="upload-card-body">
                <p>Upload your supplier payment records. The Expense Watchdog will automatically detect duplicate payments and unusual spending patterns across categories.</p>
            </div>
            <div class="card-buttons">
                <button class="btn-guide" onclick="openGuide('expenses')">
                    <i class="fas fa-question-circle"></i> Guide
                </button>
                <button class="btn-upload" onclick="navigateTo('upload_expenses.php')">
                    <i class="fas fa-arrow-right"></i> Upload
                </button>
            </div>
        </div>

        <div class="upload-card">
            <div class="upload-card-header compliance">
                <i class="fas fa-calendar-alt"></i>
                <h3>Compliance</h3>
            </div>
            <div class="upload-card-body">
                <p>Upload your statutory deadlines. The system will create a compliance calendar with automated reminders at 30, 14, 7, and 1 day before each deadline.</p>
            </div>
            <div class="card-buttons">
                <button class="btn-guide" onclick="openGuide('compliance')">
                    <i class="fas fa-question-circle"></i> Guide
                </button>
                <button class="btn-upload" onclick="navigateTo('upload_compliance.php')">
                    <i class="fas fa-arrow-right"></i> Upload
                </button>
            </div>
        </div>
    </div>

    <div class="modal" id="guideModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">
                    <i class="fas fa-book-open"></i>
                    <span>CSV Guide</span>
                </h2>
                <button class="modal-close" onclick="closeGuide()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
    <!-- Slide-in Notification Container -->
    <div id="notificationContainer"></div>
    <script>
        //csv guide
        const guideContent = {
            invoices: {
                title: 'Invoices CSV Guide',
                icon: 'fas fa-file-invoice-dollar',
                description: 'Upload your outstanding invoices using this format. The system will track due dates and generate overdue alerts.',
                columns: [{
                        name: 'client_name',
                        type: 'Text',
                        description: 'Name of the customer or client',
                        required: true,
                        example: 'Lerato Furniture'
                    },
                    {
                        name: 'amount',
                        type: 'Number',
                        description: 'Invoice amount in Rands (no currency symbol)',
                        required: true,
                        example: '18500'
                    },
                    {
                        name: 'due_date',
                        type: 'Date',
                        description: 'Payment due date (YYYY-MM-DD format)',
                        required: true,
                        example: '2025-06-15'
                    },
                    {
                        name: 'status',
                        type: 'Text',
                        description: 'Invoice status: overdue, pending, or paid',
                        required: true,
                        example: 'pending'
                    }
                ],
                sampleRows: [{
                        client_name: 'Lerato Furniture',
                        amount: '18500',
                        due_date: '2025-06-15',
                        status: 'pending'
                    },
                    {
                        client_name: 'Soweto Supermarket',
                        amount: '34200',
                        due_date: '2025-05-20',
                        status: 'overdue'
                    },
                    {
                        client_name: 'Durban Logistics',
                        amount: '12750',
                        due_date: '2025-07-01',
                        status: 'pending'
                    }
                ],
                notes: 'Dates must be in YYYY-MM-DD format. Amount should be in Rands without commas or currency symbols. Status must be one of: overdue, pending, paid.',
                csvContent: `client_name,amount,due_date,status
Lerato Furniture,1850000,2026-06-15,pending
Soweto Supermarket,3420000,2026-05-20,overdue
Durban Logistics,1275000,2026-07-01,pending
Cape Town Traders,890000,2026-05-10,overdue
Johannesburg Steel,4560000,2026-06-30,pending
Pretoria Hardware,2340000,2026-08-15,paid
Bloemfontein Supplies,5670000,2026-04-25,overdue
Port Elizabeth Electronics,3450000,2026-09-10,pending
East London Textiles,6780000,2026-07-20,paid
Kimberley Mining,7890000,2026-05-30,overdue
Polokwane Retail,1230000,2026-10-01,pending
Nelspruit Agri,4560000,2026-06-10,paid
Rustenburg Engineering,8900000,2026-08-05,overdue
Pietermaritzburg Motors,2345000,2026-07-15,pending
George Construction,5678000,2026-09-20,paid
Saldanha Fishing,3456000,2026-04-30,overdue
Mossel Bay Tourism,6789000,2026-11-01,pending
Knysna Properties,7891000,2026-06-25,paid
Oudtshoorn Farming,8912000,2026-10-15,overdue
Paarl Wine,2342000,2026-07-10,pending
Stellenbosch Tech,5673000,2026-08-25,paid
Somerset West Retail,6784000,2026-05-05,overdue
Cape Winelands,7895000,2026-09-30,pending
Helderberg Medical,2346000,2026-11-15,paid
West Coast Fisheries,5677000,2026-06-20,overdue
Overberg Logistics,6788000,2026-10-10,pending
Boland Transport,7899000,2026-07-25,paid
Klein Karoo Meat,8910000,2026-08-15,overdue
Garden Route Lodge,2341000,2026-09-05,pending
Tsitsikamma Tours,5672000,2026-11-20,paid
Jeffreys Bay Surf,6783000,2026-05-15,overdue
St Francis Bay,7894000,2026-10-25,pending
Humansdorp Retail,2345000,2026-07-30,paid
Kirkwood Citrus,5676000,2026-08-10,overdue
Patensie Produce,6787000,2026-09-15,pending
Cradock Meat,7898000,2026-11-05,paid
Middelburg Wool,8919000,2026-06-05,overdue
Graaff Reinet,2342000,2026-10-20,pending
Beaufort West,5673000,2026-07-15,paid
Victoria West,6784000,2026-08-30,overdue
Calvinia Farming,7895000,2026-09-10,pending
Springbok Mining,2346000,2026-11-25,paid
Upington Grapes,5677000,2026-05-10,overdue
Kakamas Irrigation,6788000,2026-10-05,pending
Keimoes Dates,7899000,2026-07-20,paid
Pofadder Logistics,8910000,2026-08-25,overdue
Kuruman Mining,2341000,2026-09-20,pending
Postmasburg Iron,5672000,2026-11-10,paid
Kathu Diamonds,6783000,2026-06-15,overdue
Olifantshoek,7894000,2026-10-30,pending
Griekwastad,2345000,2026-07-05,paid
Danielskuil,5676000,2026-08-20,overdue
Windsorton,6787000,2026-09-25,pending
Warrenton,7898000,2026-11-15,paid
Jan Kempdorp,8919000,2026-05-20,overdue
Hartswater,2342000,2026-10-10,pending
Taung,5673000,2026-07-25,paid
Vryburg Beef,6784000,2026-08-05,overdue
Schweizer Reneke,7895000,2026-09-30,pending
Bloemhof Dam,2346000,2026-11-20,paid
Christiana Maize,5677000,2026-06-10,overdue
Pampierstad,6788000,2026-10-15,pending
Barkley West,7899000,2026-07-30,paid
Delportshoop,8910000,2026-08-15,overdue
Ritchie,2341000,2026-09-10,pending
Modder River,5672000,2026-11-05,paid
Kimberley Airport,6783000,2026-05-25,overdue
Boshof,7894000,2026-10-20,pending
Jacobsdal,2345000,2026-07-10,paid
Petrusville,5676000,2026-08-25,overdue
Philippolis,6787000,2026-09-15,pending
Jagersfontein,7898000,2026-11-30,paid
Fauresmith,8919000,2026-06-20,overdue
Koffiefontein,2342000,2026-10-05,pending
Luckhoff,5673000,2026-07-20,paid
Edenburg,6784000,2026-08-05,overdue
Reddersburg,7895000,2026-09-25,pending
Smithfield,2346000,2026-11-10,paid
Rouxville,5677000,2026-05-15,overdue
Zastron,6788000,2026-10-30,pending
Hobhouse,7899000,2026-07-05,paid
Wepener,8910000,2026-08-20,overdue
Van Stadensrus,2341000,2026-09-15,pending
Koffiefontein Mine,5672000,2026-11-25,paid
Jagersfontein Mine,6783000,2026-06-10,overdue
Fauresmith Solar,7894000,2026-10-10,pending
Luckhoff Wind,2345000,2026-07-30,paid
Edenburg Mall,5676000,2026-08-15,overdue
Reddersburg Plaza,6787000,2026-09-20,pending
Smithfield Motors,7898000,2026-11-05,paid
Rouxville Garage,8919000,2026-05-30,overdue
Zastron Hotel,2342000,2026-10-25,pending
Hobhouse Lodge,5673000,2026-07-15,paid
Wepener B&B,6784000,2026-08-05,overdue
Van Stadensrus Inn,7895000,2026-09-30,pending
Johannesburg Mall,3456000,2026-12-01,pending
Pretoria Plaza,4567000,2026-11-28,paid
Cape Town Centre,5678000,2026-10-15,overdue
Durban Junction,6789000,2026-09-22,pending
Bloemfontein Square,7890000,2026-08-10,paid
Port Elizabeth Hub,8901000,2026-07-18,overdue
East London Crossing,9012000,2026-06-05,pending
Kimberley Gateway,1234000,2026-05-12,paid
Polokwane Link,2345000,2026-04-20,overdue
Nelspruit Connection,3456000,2026-03-15,pending
Rustenburg Bridge,4567000,2026-02-28,paid
Pietermaritzburg Park,5678000,2026-01-10,overdue
George Gardens,6789000,2026-12-20,pending
Saldanha Harbour,7890000,2026-11-05,paid
Mossel Bay Marina,8901000,2026-10-12,overdue
Knysna Quays,9012000,2026-09-08,pending
Oudtshoorn Ostrich,1234000,2026-08-25,paid
Paarl Mountain,2345000,2026-07-30,overdue
Stellenbosch Winelands,3456000,2026-06-15,pending
Somerset West Mall,4567000,2026-05-20,paid
Cape Winelands Estate,5678000,2026-04-10,overdue
Helderberg Village,6789000,2026-03-05,pending
West Coast Beach,7890000,2026-02-18,paid
Overberg Fields,8901000,2026-01-22,overdue
Boland Mountains,9012000,2025-12-10,pending
Klein Karoo Valley,1234000,2025-11-15,paid
Garden Route Casino,2345000,2025-10-20,overdue
Tsitsikamma Forest,3456000,2025-09-05,pending
Jeffreys Bay Beach,4567000,2025-08-12,paid
St Francis Canal,5678000,2025-07-18,overdue
Humansdorp Mall,6789000,2025-06-22,pending
Kirkwood Citrus,7890000,2025-05-10,paid
Patensie Farms,8901000,2025-04-15,overdue
Cradock Karoo,9012000,2025-03-20,pending
Middelburg Sheep,1234000,2025-02-25,paid
Graaff Reinet,2345000,2025-01-30,overdue
Beaufort West Desert,3456000,2025-12-05,pending
Victoria West Plains,4567000,2025-11-10,paid
Calvinia Flowers,5678000,2025-10-15,overdue
Springbok Namaqua,6789000,2025-09-20,pending
Upington Oasis,7890000,2025-08-25,paid
Kakamas Vineyards,8901000,2025-07-30,overdue
Keimoes Dates,9012000,2025-06-05,pending
Pofadder Solar,1234000,2025-05-10,paid
Kuruman Eye,2345000,2025-04-15,overdue
Postmasburg Manganese,3456000,2025-03-20,pending
Kathu Golf,4567000,2025-02-25,paid
Olifantshoek,5678000,2025-01-30,overdue
Griekwastad Ruins,6789000,2025-12-05,pending
Danielskuil Caves,7890000,2025-11-10,paid
Windsorton Dam,8901000,2025-10-15,overdue
Warrenton Bridge,9012000,2025-09-20,pending
Jan Kempdorp,1234000,2025-08-25,paid
Hartswater Irrigation,2345000,2025-07-30,overdue
Taung Skull,3456000,2025-06-05,pending
Vryburg Stargazing,4567000,2025-05-10,paid
Schweizer Reneke,5678000,2025-04-15,overdue
Bloemhof Resort,6789000,2025-03-20,pending
Christiana Weir,7890000,2025-02-25,paid
Pampierstad,8901000,2025-01-30,overdue
Barkley West Diamonds,9012000,2025-12-05,pending
Delportshoop,1234000,2025-11-10,paid
Ritchie,2345000,2025-10-15,overdue
Modder River Battle,3456000,2025-09-20,pending
Kimberley Big Hole,4567000,2025-08-25,paid
Boshof,5678000,2025-07-30,overdue
Jacobsdal,6789000,2025-06-05,pending
Petrusville,7890000,2025-05-10,paid
Philippolis,8901000,2025-04-15,overdue
Jagersfontein,9012000,2025-03-20,pending
Fauresmith,1234000,2025-02-25,paid
Koffiefontein,2345000,2025-01-30,overdue
Luckhoff,3456000,2025-12-05,pending
Edenburg,4567000,2025-11-10,paid
Reddersburg,5678000,2025-10-15,overdue
Smithfield,6789000,2025-09-20,pending
Rouxville,7890000,2025-08-25,paid
Zastron,8901000,2025-07-30,overdue
Hobhouse,9012000,2025-06-05,pending
Wepener,1234000,2025-05-10,paid
Van Stadensrus,2345000,2025-04-15,overdue
Sandton City,5678000,2025-12-10,pending
Midrand Estate,6789000,2025-11-15,paid
Randburg Square,7890000,2025-10-20,overdue
Roodepoort Mall,8901000,2025-09-25,pending
Bryanston Village,9012000,2025-08-30,paid
Fourways Crossing,1234000,2025-07-05,overdue
Waterfall City,2345000,2025-06-10,pending
Kyalami Park,3456000,2025-05-15,paid
Lonehill Estate,4567000,2025-04-20,overdue
Douglasdale Village,5678000,2025-03-25,pending
Dainfern Golf,6789000,2025-02-28,paid
Steyn City,7890000,2025-01-15,overdue
Broadacres Mall,8901000,2025-12-20,pending
Carlswald Centre,9012000,2025-11-25,paid
Richards Bay Port,1234000,2025-10-30,overdue
Newcastle Steel,2345000,2025-09-05,pending
Ladysmith Battlefield,3456000,2025-08-10,paid
Dundee Coal,4567000,2025-07-15,overdue
Vryheid Mine,5678000,2025-06-20,pending
Utrecht Forest,6789000,2025-05-25,paid
Paulpietersburg,7890000,2025-04-30,overdue
Pongola Sugar,8901000,2025-03-05,pending
Mkuze Game,9012000,2025-02-10,paid
Jozini Dam,1234000,2025-01-15,overdue
Hluhluwe Reserve,2345000,2025-12-20,pending
Sodwana Bay,3456000,2025-11-25,paid
St Lucia Wetlands,4567000,2025-10-30,overdue
Empangeni Mall,5678000,2025-09-05,pending
Eshowe Forest,6789000,2025-08-10,paid
Melmoth Centre,7890000,2025-07-15,overdue
Nkandla Village,8901000,2025-06-20,pending
Ulundi Kingdom,9012000,2025-05-25,paid
Vryheid Plains,1234000,2025-04-30,overdue
Dundee Hills,2345000,2025-03-05,pending
Ladysmith Town,3456000,2025-02-10,paid
Newcastle Industrial,4567000,2025-01-15,overdue
Richards Bay Harbour,5678000,2025-12-20,pending
Empangeni Sugar,6789000,2025-11-25,paid
Eshowe Mission,7890000,2025-10-30,overdue
Melmoth Church,8901000,2025-09-05,pending
Nkandla Royal,9012000,2025-08-10,paid
Ulundi Museum,1234000,2025-07-15,overdue
Vryheid Mall,2345000,2025-06-20,pending
Dundee Golf,3456000,2025-05-25,paid
Ladysmith Casino,4567000,2025-04-30,overdue
Newcastle Plaza,5678000,2025-03-05,pending
Richards Bay Mall,6789000,2025-02-10,paid
Empangeni Plaza,7890000,2025-01-15,overdue
Eshowe Heights,8901000,2025-12-20,pending
Melmoth Gardens,9012000,2025-11-25,paid
Nkandla Valley,1234000,2025-10-30,overdue
Ulundi Heights,2345000,2025-09-05,pending
Vryheid Heights,3456000,2025-08-10,paid`
            },
            expenses: {
                title: 'Expenses CSV Guide',
                icon: 'fas fa-receipt',
                description: 'Upload your supplier payment records. The Expense Watchdog will detect duplicates and unusual spending patterns.',
                columns: [{
                        name: 'category',
                        type: 'Text',
                        description: 'Expense category (e.g., Marketing, Operations, Supplies)',
                        required: true,
                        example: 'Marketing'
                    },
                    {
                        name: 'supplier_name',
                        type: 'Text',
                        description: 'Name of the supplier or vendor',
                        required: true,
                        example: 'Mzansi Media'
                    },
                    {
                        name: 'amount',
                        type: 'Number',
                        description: 'Expense amount in Rands (no currency symbol)',
                        required: true,
                        example: '8500'
                    },
                    {
                        name: 'invoice_date',
                        type: 'Date',
                        description: 'Date of the invoice (YYYY-MM-DD format)',
                        required: true,
                        example: '2025-05-01'
                    }
                ],
                sampleRows: [{
                        category: 'Marketing',
                        supplier_name: 'Mzansi Media',
                        amount: '8500',
                        invoice_date: '2025-05-01'
                    },
                    {
                        category: 'Operations',
                        supplier_name: 'KZN Freight',
                        amount: '15600',
                        invoice_date: '2025-05-10'
                    },
                    {
                        category: 'Supplies',
                        supplier_name: 'Makro SA',
                        amount: '4300',
                        invoice_date: '2025-05-15'
                    }
                ],
                notes: 'The system will flag duplicate payments from the same supplier in the same month. Category names are case-sensitive.',
                csvContent: `category,supplier_name,amount,invoice_date
Operations,FreightCore,16300,2025-12-18
Supplies,Office National,5000,2025-12-20
Utilities,City Power,3800,2025-12-24
Rent,Menlyn Properties,25000,2025-12-29
Marketing,Mzansi Media,8600,2026-01-03
Operations,KZN Freight,15500,2026-01-06
Supplies,Makro SA,4400,2026-01-08
Utilities,City Power,3300,2026-01-11
Rent,Menlyn Properties,25000,2026-01-01
Marketing,Mzansi Media,8600,2026-01-15
Operations,KZN Freight,15500,2026-01-18
Supplies,Makro SA,4400,2026-01-22
Utilities,City Power,3300,2026-01-25
Rent,Menlyn Properties,25000,2026-01-28
Marketing,Joburg Digital,9400,2026-02-03
Operations,FastTrack Logistics,14700,2026-02-05
Supplies,Builders Warehouse,5200,2026-02-09
Utilities,City Power,3500,2026-02-12
Rent,Menlyn Properties,25000,2026-02-01
Marketing,Joburg Digital,9400,2026-02-16
Operations,FastTrack Logistics,14700,2026-02-18
Supplies,Builders Warehouse,5200,2026-02-20
Utilities,City Power,3500,2026-02-24
Rent,Menlyn Properties,25000,2026-02-27
Marketing,Mzansi Media,8900,2026-03-02
Operations,KZN Freight,15900,2026-03-05
Supplies,Makro SA,4500,2026-03-08
Utilities,City Power,3600,2026-03-10
Rent,Menlyn Properties,25000,2026-03-01
Marketing,Mzansi Media,8900,2026-03-14
Operations,KZN Freight,15900,2026-03-17
Supplies,Makro SA,4500,2026-03-21
Utilities,City Power,3600,2026-03-24
Rent,Menlyn Properties,25000,2026-03-28
Marketing,Digital Jungle,9700,2026-04-03
Operations,FreightCore,16400,2026-04-05
Supplies,Office National,5100,2026-04-09
Utilities,City Power,3400,2026-04-12
Rent,Menlyn Properties,25000,2026-04-01
Marketing,Digital Jungle,9700,2026-04-15
Operations,FreightCore,16400,2026-04-19
Supplies,Office National,5100,2026-04-21
Utilities,City Power,3400,2026-04-24
Rent,Menlyn Properties,25000,2026-04-29
Marketing,Mzansi Media,9000,2026-05-02
Operations,KZN Freight,16000,2026-05-05
Supplies,Makro SA,4600,2026-05-07
Utilities,City Power,3700,2026-05-10
Rent,Menlyn Properties,25000,2026-05-01
Marketing,Mzansi Media,9000,2026-05-14
Operations,KZN Freight,16000,2026-05-18
Supplies,Makro SA,4600,2026-05-20
Utilities,City Power,3700,2026-05-23
Rent,Menlyn Properties,25000,2026-05-28`
            },
            compliance: {
                title: 'Compliance CSV Guide',
                icon: 'fas fa-calendar-alt',
                description: 'Upload your statutory deadlines. The system will create a compliance calendar with automated reminders.',
                columns: [{
                        name: 'title',
                        type: 'Text',
                        description: 'Name of the compliance obligation',
                        required: true,
                        example: 'VAT201 Return'
                    },
                    {
                        name: 'due_date',
                        type: 'Date',
                        description: 'Deadline date (YYYY-MM-DD format)',
                        required: true,
                        example: '2025-05-30'
                    },
                    {
                        name: 'reminder_days',
                        type: 'Text',
                        description: 'Days before deadline to send reminders (comma-separated)',
                        required: true,
                        example: '30,14,7,1'
                    }
                ],
                sampleRows: [{
                        title: 'VAT201 Return',
                        due_date: '2025-05-30',
                        reminder_days: '30,14,7,1'
                    },
                    {
                        title: 'PAYE Submission',
                        due_date: '2025-06-07',
                        reminder_days: '30,14,7,1'
                    },
                    {
                        title: 'COIDA Annual Return',
                        due_date: '2025-05-15',
                        reminder_days: '30,14,7,1'
                    }
                ],
                notes: 'Reminder days should be comma-separated without spaces. The system will send email alerts on each specified day before the due date.',
                csvContent: `title,due_date,reminder_days
VAT201 Return (Q2),2025-07-25,"30,14,7,1"
PAYE Submission,2025-06-07,"30,14,7,1"
UIF Declaration,2025-06-07,"30,14,7,1"
COIDA Annual Return,2025-05-15,"30,14,7,1"
CIPC Annual Return,2025-08-30,"30,14,7,1"
Skills Development Levy,2025-06-07,"30,14,7,1"
VAT201 Return (Q3),2025-10-25,"30,14,7,1"
PAYE Submission (Oct),2025-10-07,"30,14,7,1"
UIF Declaration (Oct),2025-10-07,"30,14,7,1"
VAT201 Return (Q4),2026-01-25,"30,14,7,1"
PAYE Submission (Jan),2026-01-07,"30,14,7,1"
UIF Declaration (Jan),2026-01-07,"30,14,7,1"
COIDA Annual Return,2026-05-15,"30,14,7,1"
CIPC Annual Return,2026-08-30,"30,14,7,1"
Skills Development Levy,2026-06-07,"30,14,7,1"
VAT201 Return (Q1),2026-04-25,"30,14,7,1"
PAYE Submission (Apr),2026-04-07,"30,14,7,1"
UIF Declaration (Apr),2026-04-07,"30,14,7,1"
VAT201 Return (Q2),2026-07-25,"30,14,7,1"
PAYE Submission (Jul),2026-07-07,"30,14,7,1"
UIF Declaration (Jul),2026-07-07,"30,14,7,1"
VAT201 Return (Q3),2026-10-25,"30,14,7,1"
PAYE Submission (Oct),2026-10-07,"30,14,7,1"
UIF Declaration (Oct),2026-10-07,"30,14,7,1"
VAT201 Return (Q4),2027-01-25,"30,14,7,1"
PAYE Submission (Jan),2027-01-07,"30,14,7,1"
UIF Declaration (Jan),2027-01-07,"30,14,7,1"
COIDA Annual Return,2027-05-15,"30,14,7,1"
CIPC Annual Return,2027-08-30,"30,14,7,1"
Skills Development Levy,2027-06-07,"30,14,7,1"
VAT201 Return (Q1),2027-04-25,"30,14,7,1"
PAYE Submission (Apr),2027-04-07,"30,14,7,1"
UIF Declaration (Apr),2027-04-07,"30,14,7,1"
VAT201 Return (Q2),2027-07-25,"30,14,7,1"
PAYE Submission (Jul),2027-07-07,"30,14,7,1"
UIF Declaration (Jul),2027-07-07,"30,14,7,1"
VAT201 Return (Q3),2027-10-25,"30,14,7,1"
PAYE Submission (Oct),2027-10-07,"30,14,7,1"
UIF Declaration (Oct),2027-10-07,"30,14,7,1"
VAT201 Return (Q4),2028-01-25,"30,14,7,1"
PAYE Submission (Jan),2028-01-07,"30,14,7,1"
UIF Declaration (Jan),2028-01-07,"30,14,7,1"
COIDA Annual Return,2028-05-15,"30,14,7,1"
CIPC Annual Return,2028-08-30,"30,14,7,1"
Skills Development Levy,2028-06-07,"30,14,7,1"
VAT201 Return (Q1),2028-04-25,"30,14,7,1"
PAYE Submission (Apr),2028-04-07,"30,14,7,1"
UIF Declaration (Apr),2028-04-07,"30,14,7,1"
Business License Renewal,2025-09-30,"30,14,7,1"
Fire Certificate Renewal,2025-11-15,"30,14,7,1"
Health Permit Renewal,2026-02-20,"30,14,7,1"
Trading License Renewal,2026-03-31,"30,14,7,1"`
            }
        };



        //generate modal per each guide
        function openGuide(type) {
            const content = guideContent[type];
            if (!content) return;

            const modal = document.getElementById('guideModal');
            const modalBody = document.getElementById('modalBody');
            const modalTitle = document.getElementById('modalTitle');

            modalTitle.innerHTML = '<i class="' + content.icon + '"></i> ' + content.title;

            let columnsHtml = `
            <div class="guide-section">
                <h3><i class="fas fa-table"></i> CSV Column Structure</h3>
                <p>` + content.description + `</p>
                <table class="csv-table">
                    <thead>
                        <tr>
                            <th>Column Name</th>
                            <th>Data Type</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

            for (let i = 0; i < content.columns.length; i++) {
                const col = content.columns[i];
                columnsHtml += `
                        <tr>
                            <td><strong>` + col.name + `</strong> ` + (col.required ? '<span style="color:#dc2626">*</span>' : '') + `</td>
                            <td>` + col.type + `</td>
                            <td>` + col.description + `</td>
                            <td><code>` + col.example + `</code></td>
                        </tr>
            `;
            }

            columnsHtml += `
                    </tbody>
                </table>
            </div>
        `;

            let sampleHtml = `
            <div class="guide-section">
                <h3><i class="fas fa-file-csv"></i> Sample CSV Data</h3>
                <p>Example of how your CSV file should look:</p>
                <table class="csv-table">
                    <thead>
                        <tr>
        `;

            const headers = Object.keys(content.sampleRows[0]);
            for (let i = 0; i < headers.length; i++) {
                sampleHtml += `<th>` + headers[i] + `</th>`;
            }

            sampleHtml += `
                        </tr>
                    </thead>
                    <tbody>
        `;

            for (let i = 0; i < content.sampleRows.length; i++) {
                const row = content.sampleRows[i];
                const values = Object.values(row);
                sampleHtml += `<tr class="example-row">`;
                for (let j = 0; j < values.length; j++) {
                    sampleHtml += `<td><code>` + values[j] + `</code></td>`;
                }
                sampleHtml += `</tr>`;
            }

            sampleHtml += `
                    </tbody>
                </table>
            </div>
        `;

            let downloadHtml = `
            <div class="guide-section">
                <h3><i class="fas fa-download"></i> Download Template</h3>
                <p>Click the button below to download the CSV template. Fill it with your data and upload.</p>
                <button class="download-btn" onclick="downloadTemplate('` + type + `')">
                    <i class="fas fa-file-csv"></i> Download ` + type.charAt(0).toUpperCase() + type.slice(1) + ` CSV Template
                </button>
            </div>
        `;

            let notesHtml = `
            <div class="note-box">
                <i class="fas fa-lightbulb"></i> <strong>Important Notes:</strong><br>
                ` + content.notes + `
            </div>
        `;

            modalBody.innerHTML = columnsHtml + sampleHtml + downloadHtml + notesHtml;
            modal.classList.add('show');
        }

        function closeGuide() {
            document.getElementById('guideModal').classList.remove('show');
        }



        showNotification('Upload Your Data', 'Select which type of data you want to import. Click "Guide" to see the CSV format, then "Upload" to begin.', 'error');


        function showNotification(title, message, type = 'info', duration = 10000) {
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
                setTimeout(() => notification.remove(), 20);
            }, duration);
        }





        //download template
        function downloadTemplate(type) {
            const content = guideContent[type];
            if (!content) return;

            const blob = new Blob([content.csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'template_' + type + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            const btn = event.target.closest('.download-btn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Downloaded!';
            setTimeout(function() {
                btn.innerHTML = originalHtml;
            }, 2000);
        }

        function navigateTo(page) {
            const companyId = '<?php echo $company_id; ?>';
            const companyName = '<?php echo $company_name; ?>';
            window.location.href = 'uploads/' + page;
        }

        document.getElementById('guideModal').addEventListener('click', function(e) {
            if (e.target === document.getElementById('guideModal')) {
                closeGuide();
            }
        });
    </script>

</body>

</html>