<?php
session_start();

if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
    header("Location: ../login.php");
    exit();
}

$company_id = $_SESSION["company_id"];
$company_name = $_SESSION["company_name"];
$admin_name = $_SESSION["admin_name"];

include("../../connection.php");
include("../../api/api_csv/tables.php");


$connection->set_charset("utf8mb4");

function calculateHealthScore($company_id, $connection)
{

    $dataCheckQuery = "
        SELECT 
            (SELECT COUNT(*) FROM invoices WHERE company_id = ?) as invoice_count,
            (SELECT COUNT(*) FROM expenses WHERE company_id = ?) as expense_count,
            (SELECT COUNT(*) FROM compliance_items WHERE company_id = ?) as compliance_count
    ";
    $stmt = $connection->prepare($dataCheckQuery);
    $stmt->bind_param("iii", $company_id, $company_id, $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dataCounts = $result->fetch_assoc();
    $stmt->close();

    $hasInvoices = $dataCounts['invoice_count'] > 0;
    $hasExpenses = $dataCounts['expense_count'] > 0;
    $hasCompliance = $dataCounts['compliance_count'] > 0;

    if (!$hasInvoices && !$hasExpenses && !$hasCompliance) {
        return [
            'score' => null,
            'status' => 'No Data',
            'message' => 'Upload your financial data to see your health score.',
            'breakdown' => [
                'cash_ratio' => ['points' => 0, 'max' => 25, 'value' => 'Upload invoices'],
                'overdue_invoices' => ['points' => 0, 'max' => 25, 'value' => 'Upload invoices'],
                'expense_growth' => ['points' => 0, 'max' => 20, 'value' => 'Upload expenses'],
                'runway' => ['points' => 0, 'max' => 25, 'value' => 'Upload data'],
                'compliance' => ['points' => 0, 'max' => 20, 'value' => 'Upload compliance']
            ]
        ];
    }

    if (!$hasInvoices) {
        $cashRatioPoints = 0;
        $cashDisplay = 'No invoices uploaded';
    } else {
        $cashQuery = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid FROM invoices WHERE company_id = ?";
        $stmt = $connection->prepare($cashQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $total = $row['total'] ?: 1;
        $paid = $row['paid'] ?: 0;
        $cashRatioPoints = round(($paid / $total) * 25);
        $cashDisplay = round(($paid / $total) * 100, 1) . '% paid';
        $stmt->close();
    }

    if (!$hasInvoices) {
        $overduePoints = 0;
        $overdueDisplay = 'No invoices uploaded';
    } else {
        $overdueQuery = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue FROM invoices WHERE company_id = ?";
        $stmt = $connection->prepare($overdueQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $total = $row['total'] ?: 1;
        $overdue = $row['overdue'] ?: 0;
        $overduePoints = round((1 - ($overdue / $total)) * 25);
        $overdueDisplay = $overdue . ' overdue';
        $stmt->close();
    }

    if (!$hasExpenses) {
        $expensePoints = 0;
        $growthDisplay = 'No expenses uploaded';
    } else {
        $currentMonthStart = date('Y-m-01');
        $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

        $currentQuery = "SELECT COALESCE(SUM(amount_cents), 0) as total FROM expenses WHERE company_id = ? AND invoice_date >= ?";
        $stmt = $connection->prepare($currentQuery);
        $stmt->bind_param("is", $company_id, $currentMonthStart);
        $stmt->execute();
        $currentMonth = ($stmt->get_result()->fetch_assoc()['total'] ?? 0) / 100;
        $stmt->close();

        $lastQuery = "SELECT COALESCE(SUM(amount_cents), 0) as total FROM expenses WHERE company_id = ? AND invoice_date >= ? AND invoice_date <= ?";
        $stmt = $connection->prepare($lastQuery);
        $stmt->bind_param("iss", $company_id, $lastMonthStart, $lastMonthEnd);
        $stmt->execute();
        $lastMonth = ($stmt->get_result()->fetch_assoc()['total'] ?? 0) / 100;
        $stmt->close();

        if ($currentMonth == 0 && $lastMonth == 0) {
            $expensePoints = 0;
            $growthDisplay = 'No expense data';
        } elseif ($lastMonth == 0 && $currentMonth > 0) {
            $expensePoints = 20;
            $growthDisplay = 'First month recorded';
        } else {
            $growthRate = (($currentMonth - $lastMonth) / $lastMonth) * 100;
            $growthDisplay = ($growthRate >= 0 ? '+' : '') . round($growthRate, 1) . '%';
            if ($growthRate < 5 && $growthRate > -5) {
                $expensePoints = 20;
            } elseif ($growthRate <= 20 && $growthRate >= -20) {
                $expensePoints = 10;
            } else {
                $expensePoints = 0;
            }
        }
    }

    if (!$hasExpenses || !$hasInvoices) {
        $runwayPoints = 0;
        $runwayDisplay = 'Need both';
    } else {
        $avgQuery = "SELECT COALESCE(AVG(amount_cents), 0) as avg FROM expenses WHERE company_id = ? AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        $stmt = $connection->prepare($avgQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $avgMonthlyExpense = ($stmt->get_result()->fetch_assoc()['avg'] ?? 0) / 100;
        $stmt->close();

        $overdueQuery = "SELECT COALESCE(SUM(amount_cents), 0) as total FROM invoices WHERE company_id = ? AND status = 'overdue'";
        $stmt = $connection->prepare($overdueQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $cashAtRisk = ($stmt->get_result()->fetch_assoc()['total'] ?? 0) / 100;
        $stmt->close();

        if ($avgMonthlyExpense <= 0 || $cashAtRisk <= 0) {
            $runwayPoints = 0;
            $runwayDisplay = 'Insufficient data';
        } else {
            $runwayMonths = $cashAtRisk / $avgMonthlyExpense;
            if ($runwayMonths >= 6) {
                $runwayPoints = 25;
                $runwayDisplay = round($runwayMonths, 1) . ' months';
            } elseif ($runwayMonths >= 3) {
                $runwayPoints = 15;
                $runwayDisplay = round($runwayMonths, 1) . ' months';
            } elseif ($runwayMonths >= 1) {
                $runwayPoints = 8;
                $runwayDisplay = round($runwayMonths, 1) . ' months';
            } else {
                $runwayPoints = 0;
                $runwayDisplay = round($runwayMonths, 1) . ' months';
            }
        }
    }

    if (!$hasCompliance) {
        $compliancePoints = 0;
        $complianceDisplay = 'No compliance items';
    } else {
        $compQuery = "SELECT COUNT(*) as total, SUM(CASE WHEN due_date < CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as overdue FROM compliance_items WHERE company_id = ?";
        $stmt = $connection->prepare($compQuery);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $total = $row['total'] ?? 0;
        $overdue = $row['overdue'] ?? 0;
        $stmt->close();

        if ($total == 0) {
            $compliancePoints = 0;
            $complianceDisplay = 'No items';
        } else {
            if ($overdue == 0) {
                $compliancePoints = 20;
                $complianceDisplay = 'All good';
            } elseif ($overdue == 1) {
                $compliancePoints = 10;
                $complianceDisplay = '1 overdue';
            } else {
                $compliancePoints = 0;
                $complianceDisplay = $overdue . ' overdue';
            }
        }
    }

    $totalScore = $cashRatioPoints + $overduePoints + $expensePoints + $runwayPoints + $compliancePoints;
    $totalScore = max(0, min(100, $totalScore));

    if ($totalScore >= 70) {
        $status = 'Excellent';
        $message = 'Your business is in great shape. Keep up the good work!';
    } elseif ($totalScore >= 50) {
        $status = 'Fair';
        $message = 'Your business has some areas that need attention.';
    } elseif ($totalScore > 0) {
        $status = 'Critical';
        $message = 'Your business needs immediate attention.';
    } else {
        $status = 'Setup Required';
        $message = 'Complete your data upload to see your health score.';
    }

    return [
        'score' => $totalScore > 0 ? $totalScore : null,
        'status' => $status,
        'message' => $message,
        'breakdown' => [
            'cash_ratio' => ['points' => $cashRatioPoints, 'max' => 25, 'value' => $cashDisplay],
            'overdue_invoices' => ['points' => $overduePoints, 'max' => 25, 'value' => $overdueDisplay],
            'expense_growth' => ['points' => $expensePoints, 'max' => 20, 'value' => $growthDisplay],
            'runway' => ['points' => $runwayPoints, 'max' => 25, 'value' => $runwayDisplay],
            'compliance' => ['points' => $compliancePoints, 'max' => 20, 'value' => $complianceDisplay]
        ]
    ];
}

function getChartData($company_id, $connection)
{
    $invoiceQuery = "SELECT status, COUNT(*) as count, SUM(amount_cents) as total FROM invoices WHERE company_id = ? GROUP BY status";
    $stmt = $connection->prepare($invoiceQuery);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $invoiceData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $expenseQuery = "
        SELECT DATE_FORMAT(invoice_date, '%M %Y') as month, SUM(amount_cents) as total 
        FROM expenses 
        WHERE company_id = ? AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(invoice_date), MONTH(invoice_date)
        ORDER BY invoice_date ASC
    ";
    $stmt = $connection->prepare($expenseQuery);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $expenseData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return ['invoices' => $invoiceData, 'expenses' => $expenseData];
}

function getPriorityActions($company_id, $connection)
{
    $actions = [];

    $stmt = $connection->prepare("SELECT client_name, amount_cents, due_date, DATEDIFF(CURDATE(), due_date) as days_overdue FROM invoices WHERE company_id = ? AND status = 'overdue' AND due_date < CURDATE() ORDER BY days_overdue DESC LIMIT 2");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($invoices as $inv) {
        $actions[] = [
            'message' => 'Chase ' . $inv['client_name'] . ' - R ' . number_format($inv['amount_cents'] / 100, 2) . ' invoice is ' . $inv['days_overdue'] . ' days overdue',
            'risk' => 'Critical - Cash at risk',
            'link' => 'invoices.php',
            'linkText' => 'Send Reminder →'
        ];
    }

    $stmt = $connection->prepare("SELECT title, due_date, DATEDIFF(due_date, CURDATE()) as days_remaining FROM compliance_items WHERE company_id = ? AND status != 'completed' AND due_date >= CURDATE() ORDER BY days_remaining ASC LIMIT 1");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $compliance = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($compliance && count($actions) < 3) {
        $severity = $compliance['days_remaining'] <= 7 ? 'Critical' : 'Warning';
        $actions[] = [
            'message' => $compliance['title'] . ' due in ' . $compliance['days_remaining'] . ' days',
            'risk' => $severity . ' - Deadline approaching',
            'link' => 'compliance.php',
            'linkText' => 'View Calendar →'
        ];
    }

    $stmt = $connection->prepare("SELECT supplier_name, amount_cents FROM expenses WHERE company_id = ? AND flagged = 1 LIMIT 1");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($expense && count($actions) < 3) {
        $actions[] = [
            'message' => 'Duplicate expense detected from ' . $expense['supplier_name'] . ' - R ' . number_format($expense['amount_cents'] / 100, 2),
            'risk' => 'Warning - Expense leakage',
            'link' => 'expenses.php',
            'linkText' => 'Review →'
        ];
    }

    return array_slice($actions, 0, 3);
}

$healthData = calculateHealthScore($company_id, $connection);
$priorityActions = getPriorityActions($company_id, $connection);
$chartData = getChartData($company_id, $connection);
$breakdown = $healthData['breakdown'];

$hour = date('H');
if ($hour < 12) {
    $greetingIcon = 'fa-sun';
    $greetingText = 'Good Morning';
} elseif ($hour < 17) {
    $greetingIcon = 'fa-cloud-sun';
    $greetingText = 'Good Afternoon';
} else {
    $greetingIcon = 'fa-moon';
    $greetingText = 'Good Evening';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: white;
            padding: 5px;
        }

        @media (min-width: 768px) {
            body {
                padding: 2px;
            }
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

        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: transparent;

            display: flex;
            justify-content: space-between;
            align-items: start;
            flex-wrap: wrap;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .sticky-header {
                margin: -32px -32px 0 -32px;
                padding: 16px 32px;
            }
        }

        .greeting-widget {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 12px;
            padding: 12px 20px;
            color: white;
            display: inline-flex;
            align-items: start;
            gap: 16px;
            box-shadow: 0 4px 12px rgba(35, 115, 186, 0.2);
        }

        .greeting-widget .greeting-info {
            display: flex;
            flex-direction: column;
        }

        .greeting-widget .greeting-time {
            font-size: 13px;
            opacity: 0.9;
        }

        .greeting-widget .greeting-name {
            font-size: 16px;
            font-weight: 700;
        }

        @media (min-width: 768px) {
            .greeting-widget .greeting-name {
                font-size: 18px;
            }
        }

        .greeting-widget .greeting-date {
            font-size: 10px;
            opacity: 0.8;
        }

        @media (min-width: 768px) {
            .greeting-widget .greeting-date {
                font-size: 11px;
            }
        }

        .greeting-widget .greeting-icon {
            font-size: 28px;
        }

        @media (min-width: 768px) {
            .greeting-widget .greeting-icon {
                font-size: 32px;
            }
        }

        .page-title {
            text-align: right;
        }

        .page-title h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }

        @media (min-width: 768px) {
            .page-title h1 {
                font-size: 24px;
            }
        }

        .page-title p {
            color: var(--gray);
            font-size: 11px;
        }

        @media (min-width: 768px) {
            .page-title p {
                font-size: 13px;
            }
        }

        .health-main {
            background: linear-gradient(rgba(35, 115, 186, 0.85), rgba(35, 115, 186, 0.85)), url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            background-size: cover, auto;
            background-position: center;
            background-repeat: no-repeat, repeat;
            border-radius: 5px;
            border: 1px solid var(--border);

            text-align: center;
            color: white;
        }

        @media (min-width: 768px) {
            .health-main {
                padding: 40px;
                margin: 32px 0;
            }
        }

        .score-ring-container {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 0 auto 20px;
        }

        @media (min-width: 768px) {
            .score-ring-container {
                width: 220px;
                height: 220px;
                margin: 0 auto 24px;
            }
        }

        .score-number {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 40px;
            font-weight: 800;
        }

        .score-number span {
            font-size: 18px;
            font-weight: 400;
        }

        @media (min-width: 768px) {
            .score-number {
                font-size: 56px;
            }

            .score-number span {
                font-size: 24px;
            }
        }

        .score-status {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        @media (min-width: 768px) {
            .score-status {
                font-size: 20px;
            }
        }

        .score-message {
            font-size: 12px;
            opacity: 0.9;
        }

        @media (min-width: 768px) {
            .score-message {
                font-size: 14px;
            }
        }

        .factors-section {
            margin-bottom: 24px;
        }

        @media (min-width: 768px) {
            .factors-section {
                margin-bottom: 32px;
            }
        }

        .section-header {
            margin-bottom: 16px;
        }

        @media (min-width: 768px) {
            .section-header {
                margin-bottom: 20px;
            }
        }

        .section-header h2 {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (min-width: 768px) {
            .section-header h2 {
                font-size: 18px;
            }
        }

        .factors-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        @media (min-width: 640px) {
            .factors-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .factors-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 20px;
            }
        }

        .factor-card {
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
            padding: 16px;
            text-align: center;
            transition: all 0.2s;
        }

        @media (min-width: 768px) {
            .factor-card {
                padding: 20px;
            }
        }

        .factor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .factor-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }

        @media (min-width: 768px) {
            .factor-icon {
                width: 48px;
                height: 48px;
                margin: 0 auto 16px;
            }
        }

        .factor-icon.cash {
            background: #eef4fc;
            color: var(--primary);
        }

        .factor-icon.overdue {
            background: #fee2e2;
            color: var(--critical);
        }

        .factor-icon.expense {
            background: #fed7aa;
            color: var(--warning);
        }

        .factor-icon.runway {
            background: #d1fae5;
            color: var(--success);
        }

        .factor-icon.compliance {
            background: #dbeafe;
            color: var(--info);
        }

        .factor-icon i {
            font-size: 18px;
        }

        @media (min-width: 768px) {
            .factor-icon i {
                font-size: 24px;
            }
        }

        .factor-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        @media (min-width: 768px) {
            .factor-name {
                font-size: 14px;
            }
        }

        .factor-value {
            font-size: 16px;
            font-weight: 800;
            color: var(--dark);
        }

        @media (min-width: 768px) {
            .factor-value {
                font-size: 20px;
            }
        }

        .factor-points {
            font-size: 11px;
            color: var(--gray);
            margin-top: 6px;
        }

        @media (min-width: 768px) {
            .factor-points {
                font-size: 12px;
                margin-top: 8px;
            }
        }

        .factor-progress {
            margin-top: 10px;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .factor-progress {
                margin-top: 12px;
            }
        }

        .factor-progress-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s;
        }

        .charts-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (min-width: 768px) {
            .charts-section {
                gap: 24px;
                margin-bottom: 32px;
            }
        }

        @media (min-width: 1024px) {
            .charts-section {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .chart-card {
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
            padding: 16px;
        }

        @media (min-width: 768px) {
            .chart-card {
                padding: 20px;
            }
        }

        .chart-card h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (min-width: 768px) {
            .chart-card h3 {
                font-size: 16px;
                margin-bottom: 20px;
            }
        }

        .chart-card h3 i {
            color: var(--primary);
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 250px;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .chart-container {
                min-height: 300px;
            }
        }

        canvas {
            max-width: 100%;
            height: auto !important;
        }

        .actions-section {
            background: white;
            border-radius: 5px;
            border: 1px solid var(--border);
            padding: 16px;
            margin-bottom: 24px;
        }

        @media (min-width: 768px) {
            .actions-section {
                padding: 24px;
                margin-bottom: 32px;
            }
        }

        .actions-header h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        @media (min-width: 768px) {
            .actions-header h3 {
                font-size: 16px;
                margin-bottom: 20px;
            }
        }

        .numbered-list {
            list-style: none;
        }

        .numbered-list li {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        @media (min-width: 640px) {
            .numbered-list li {
                flex-direction: row;
                align-items: center;
                gap: 16px;
                padding: 16px 0;
            }
        }

        .numbered-list li:last-child {
            border-bottom: none;
        }

        .action-number {
            width: 28px;
            height: 28px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
        }

        @media (min-width: 768px) {
            .action-number {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }
        }

        .action-details {
            flex: 1;
        }

        .action-message {
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 4px;
        }

        @media (min-width: 768px) {
            .action-message {
                font-size: 14px;
            }
        }

        .action-risk {
            font-size: 11px;
            color: var(--gray);
        }

        @media (min-width: 768px) {
            .action-risk {
                font-size: 12px;
            }
        }

        .action-btn {
            background: var(--primary);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
        }

        @media (min-width: 640px) {
            .action-btn {
                padding: 8px 20px;
                font-size: 13px;
                white-space: nowrap;
            }
        }

        .action-btn:hover {
            background: var(--primary-light);
        }

        .recommendations-section {
            background: #f8fafc;
            border-radius: 5px;
            padding: 16px;
        }

        @media (min-width: 768px) {
            .recommendations-section {
                padding: 24px;
            }
        }

        .recommendations-header h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        @media (min-width: 768px) {
            .recommendations-header h3 {
                font-size: 16px;
                margin-bottom: 20px;
            }
        }

        .recommendations-list {
            list-style: none;
        }

        .recommendations-list li {
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (min-width: 640px) {
            .recommendations-list li {
                gap: 12px;
                flex-wrap: nowrap;
            }
        }

        @media (min-width: 768px) {
            .recommendations-list li {
                padding: 12px 0;
            }
        }

        .recommendations-list li:last-child {
            border-bottom: none;
        }

        .recommendations-list i {
            width: 20px;
            color: var(--primary);
        }

        @media (min-width: 768px) {
            .recommendations-list i {
                width: 24px;
            }
        }

        .recommendation-text {
            flex: 1;
            font-size: 12px;
            color: var(--dark);
        }

        @media (min-width: 768px) {
            .recommendation-text {
                font-size: 14px;
            }
        }

        .recommendation-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
        }

        @media (min-width: 768px) {
            .recommendation-link {
                font-size: 13px;
            }
        }

        .no-data-error {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 60, 255, 0.42);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .error-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            max-width: 450px;
            margin: 20px;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2373BA, #0c96D4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .error-icon i {
            font-size: 40px;
            color: white;
        }

        .error-container h3 {
            font-size: 24px;
            font-weight: 700;
            color: #464646;
            margin-bottom: 12px;
        }

        .error-container p {
            font-size: 14px;
            color: #989798;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .error-btn {
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .error-btn.primary {
            background: #2373BA;
            color: white;
        }

        .error-btn.primary:hover {
            background: #0c96D4;
            transform: translateY(-2px);
        }

        .error-btn.secondary {
            background: #f1f5f9;
            color: #464646;
        }

        .error-btn.secondary:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>


    <?php

    if ($healthData['score'] > 0) {
    ?>


        <div class="health-main">
            <div class="sticky-header">
                <div class="greeting-widget">
                    <div class="greeting-info">
                        <div class="greeting-time"><i class="fas <?php echo $greetingIcon; ?>"></i> <?php echo $greetingText; ?></div>
                        <div class="greeting-name"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div class="greeting-date"><i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?></div>
                    </div>
                    <div class="greeting-icon"><i class="fas fa-smile-wink"></i></div>
                </div>

            </div>
            <div class="score-ring-container">
                <canvas id="healthScoreCanvas" class="score-ring" width="220" height="220"></canvas>
                <div class="score-number" id="scoreNumber"><?php echo $healthData['score'] ?? '--'; ?><span>/100</span></div>
            </div>
            <div class="score-status"><?php echo $healthData['status']; ?></div>
            <div class="score-message"><?php echo $healthData['message']; ?></div>
        </div>

        <div class="factors-section">
            <div class="section-header">
                <h2><i class="fas fa-chart-pie"></i> Five Factors</h2>
            </div>
            <div class="factors-grid">
                <?php
                $factors = [
                    'cash_ratio' => ['name' => 'Cash Ratio', 'icon' => 'fa-coins', 'iconClass' => 'cash', 'color' => 'var(--primary)'],
                    'overdue_invoices' => ['name' => 'Overdue Invoices', 'icon' => 'fa-exclamation-circle', 'iconClass' => 'overdue', 'color' => 'var(--critical)'],
                    'expense_growth' => ['name' => 'Expense Growth', 'icon' => 'fa-chart-line', 'iconClass' => 'expense', 'color' => 'var(--warning)'],
                    'runway' => ['name' => 'Months of Runway', 'icon' => 'fa-calendar-week', 'iconClass' => 'runway', 'color' => 'var(--success)'],
                    'compliance' => ['name' => 'Compliance', 'icon' => 'fa-calendar-check', 'iconClass' => 'compliance', 'color' => 'var(--info)']
                ];

                foreach ($factors as $key => $factor):
                    $points = $breakdown[$key]['points'];
                    $max = $breakdown[$key]['max'];
                    $percent = ($points / $max) * 100;
                ?>
                    <div class="factor-card">
                        <div class="factor-icon <?php echo $factor['iconClass']; ?>"><i class="fas <?php echo $factor['icon']; ?>"></i></div>
                        <div class="factor-name"><?php echo $factor['name']; ?></div>
                        <div class="factor-value"><?php echo htmlspecialchars($breakdown[$key]['value']); ?></div>
                        <div class="factor-points"><?php echo $points; ?>/<?php echo $max; ?> pts</div>
                        <div class="factor-progress">
                            <div class="factor-progress-fill" style="width: <?php echo $percent; ?>%; background: <?php echo $factor['color']; ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Invoice Status Distribution</h3>
                <div class="chart-container">
                    <canvas id="invoicePieChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-bar"></i> Monthly Expenses Trend</h3>
                <div class="chart-container">
                    <canvas id="expenseBarChart"></canvas>
                </div>
            </div>
        </div>

        <div class="actions-section">
            <div class="actions-header">
                <h3><i class="fas fa-list-ol"></i> What needs your attention today</h3>
            </div>
            <ul class="numbered-list">
                <?php if (count($priorityActions) > 0): ?>
                    <?php foreach ($priorityActions as $index => $action): ?>
                        <li>
                            <div class="action-number"><?php echo $index + 1; ?></div>
                            <div class="action-details">
                                <div class="action-message"><?php echo htmlspecialchars($action['message']); ?></div>
                                <div class="action-risk"><?php echo $action['risk']; ?></div>
                            </div>
                            <a href="javascript:void(0)" onclick="window.location.href='<?php echo $action['link']; ?>'" class="action-btn"><?php echo $action['linkText']; ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <div class="action-number">1</div>
                        <div class="action-details">
                            <div class="action-message">No urgent tasks required</div>
                            <div class="action-risk">All caught up - Great job!</div>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    <?php
    } else {

    ?>


        <div id="noDataError" class="no-data-error">
            <div class="error-container">
                <div class="error-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>No Data Available</h3>
                <p>Upload your financial data to see your business health score and insights. Click Uploads and select File you want to upload for, or check guide there... </p>
                <div class="error-actions">

                    <button onclick="location.reload()" class="error-btn secondary">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

    <?php


    }

    ?>
    <div class="recommendations-section">
        <div class="recommendations-header">
            <h3><i class="fas fa-lightbulb"></i> Recommendations</h3>
        </div>
        <ul class="recommendations-list">
            <?php
            $recommendations = [];

            if ($breakdown['cash_ratio']['points'] < 20) {
                $recommendations[] = ['text' => 'Improve your cash ratio by collecting outstanding payments faster.', 'link' => 'invoices.php', 'linkText' => 'View Invoices →'];
            }
            if ($breakdown['overdue_invoices']['points'] < 20) {
                $recommendations[] = ['text' => 'You have overdue invoices. Send reminders to customers.', 'link' => 'invoices.php', 'linkText' => 'Send Reminders →'];
            }
            if ($breakdown['expense_growth']['points'] < 15) {
                $recommendations[] = ['text' => 'Your expenses are growing. Review your spending patterns.', 'link' => 'expenses.php', 'linkText' => 'Review Expenses →'];
            }
            if ($breakdown['runway']['points'] < 15) {
                $recommendations[] = ['text' => 'Your cash runway is low. Focus on collecting overdue payments.', 'link' => 'invoices.php', 'linkText' => 'View Overdue →'];
            }
            if ($breakdown['compliance']['points'] < 15 && $breakdown['compliance']['value'] != 'All good' && $breakdown['compliance']['value'] != 'No items') {
                $recommendations[] = ['text' => 'You have upcoming or overdue compliance deadlines.', 'link' => 'compliance.php', 'linkText' => 'View Calendar →'];
            }

            if (count($recommendations) > 0):
                foreach ($recommendations as $rec):
            ?>
                    <li>
                        <i class="fas fa-arrow-right"></i>
                        <span class="recommendation-text"><?php echo $rec['text']; ?></span>
                        <a href="javascript:void(0)" onclick="window.location.href='<?php echo $rec['link']; ?>'" class="recommendation-link"><?php echo $rec['linkText']; ?></a>
                    </li>
                <?php
                endforeach;
            else:
                ?>
                <li>
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <span class="recommendation-text">Great job! Your business is in excellent health. Keep it up!</span>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <script>
        function drawScoreRing(score) {
            const canvas = document.getElementById('healthScoreCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const centerX = 110,
                centerY = 110,
                radius = 100;
            const startAngle = -0.5 * Math.PI;
            const endAngle = startAngle + (score / 100) * 2 * Math.PI;

            ctx.clearRect(0, 0, 220, 220);

            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 14;
            ctx.stroke();

            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, startAngle, endAngle);

            let color;
            if (score >= 70) color = '#3b82f6';
            else if (score >= 50) color = '#f59e0b';
            else color = '#dc2626';

            ctx.strokeStyle = color;
            ctx.lineWidth = 14;
            ctx.lineCap = 'round';
            ctx.stroke();
        }

        const score = <?php echo $healthData['score'] !== null ? $healthData['score'] : 0; ?>;
        if (score > 0) {
            drawScoreRing(score);
        }

        const invoiceCtx = document.getElementById('invoicePieChart').getContext('2d');
        const invoiceData = <?php
                            $labels = [];
                            $counts = [];
                            foreach ($chartData['invoices'] as $inv) {
                                $labels[] = ucfirst($inv['status']);
                                $counts[] = $inv['count'];
                            }
                            echo json_encode(['labels' => $labels, 'counts' => $counts]);
                            ?>;

        new Chart(invoiceCtx, {
            type: 'pie',
            data: {
                labels: invoiceData.labels,
                datasets: [{
                    data: invoiceData.counts,
                    backgroundColor: ['#2373BA', '#f59e0b', '#10b981', '#dc2626', '#989798'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        const expenseCtx = document.getElementById('expenseBarChart').getContext('2d');
        const expenseData = <?php
                            $months = [];
                            $amounts = [];
                            foreach ($chartData['expenses'] as $exp) {
                                $months[] = $exp['month'];
                                $amounts[] = round($exp['total'] / 100, 2);
                            }
                            echo json_encode(['months' => $months, 'amounts' => $amounts]);
                            ?>;

        new Chart(expenseCtx, {
            type: 'bar',
            data: {
                labels: expenseData.months,
                datasets: [{
                    label: 'Expenses (R)',
                    data: expenseData.amounts,
                    backgroundColor: '#2373BA',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (R)',
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10
                            },
                            rotation: 45,
                            autoSkip: true
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>