<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["admin_name"])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

include("../../connection.php");
$connection->set_charset("utf8mb4");

$company_id = $_SESSION['company_id'];

// Get all expenses with flagged_reason
$expenseQuery = "SELECT id, category, supplier_name, amount_cents, invoice_date, flagged, flagged_reason, created_at FROM expenses WHERE company_id = ? ORDER BY invoice_date DESC";
$expenseStmt = $connection->prepare($expenseQuery);
$expenseStmt->bind_param("i", $company_id);
$expenseStmt->execute();
$expenseResult = $expenseStmt->get_result();

$expenses = [];
$flaggedCount = 0;
$anomalyCount = 0;
$duplicateCount = 0;

while ($row = $expenseResult->fetch_assoc()) {
    $row['amount'] = $row['amount_cents'] / 100;
    unset($row['amount_cents']);
    $expenses[] = $row;
    if ($row['flagged'] == 1) $flaggedCount++;
}

// Find duplicate alerts (same supplier, same month)
$duplicateQuery = "
    SELECT e1.id, e1.supplier_name, e1.amount_cents, e1.invoice_date, e1.category
    FROM expenses e1
    WHERE e1.company_id = ? AND e1.flagged = 1
    AND EXISTS (
        SELECT 1 FROM expenses e2 
        WHERE e2.supplier_name = e1.supplier_name 
        AND MONTH(e2.invoice_date) = MONTH(e1.invoice_date)
        AND YEAR(e2.invoice_date) = YEAR(e1.invoice_date)
        AND e2.id != e1.id
        AND e2.company_id = ?
    )
";
$dupStmt = $connection->prepare($duplicateQuery);
$dupStmt->bind_param("ii", $company_id, $company_id);
$dupStmt->execute();
$dupResult = $dupStmt->get_result();

$duplicateAlerts = [];
while ($row = $dupResult->fetch_assoc()) {
    $row['amount'] = $row['amount_cents'] / 100;
    unset($row['amount_cents']);
    $duplicateAlerts[] = $row;
    $duplicateCount++;
}

// Get category analysis for last 3 months
$threeMonthsAgo = date('Y-m-d', strtotime('-3 months'));

$categoryQuery = "
    SELECT 
        category,
        
        SUM(
            CASE 
                WHEN DATE_FORMAT(invoice_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
                THEN amount_cents 
                ELSE 0 
            END
        ) as current_month,

        SUM(
            CASE 
                WHEN invoice_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                AND DATE_FORMAT(invoice_date, '%Y-%m') != DATE_FORMAT(CURDATE(), '%Y-%m')
                THEN amount_cents 
                ELSE 0 
            END
        ) / 3 as three_month_avg

    FROM expenses 
    WHERE company_id = ? 
    GROUP BY category
";

$categoryStmt = $connection->prepare($categoryQuery);
$categoryStmt->bind_param("i", $company_id);
$categoryStmt->execute();

$categoryResult = $categoryStmt->get_result();

$categoryAnalysis = [];

while ($row = $categoryResult->fetch_assoc()) {

    $current = $row['current_month'] / 100;
    $avg = $row['three_month_avg'] / 100;

    $variance = ($avg > 0)
        ? (($current - $avg) / $avg) * 100
        : 0;

    if ($variance > 100) {
        $variance = 100;
    }

    if ($variance < -100) {
        $variance = -100;
    }

    $categoryAnalysis[] = [
        'category' => $row['category'],
        'current_month' => round($current, 2),
        'three_month_avg' => round($avg, 2),
        'variance_percent' => round($variance, 1),
        'trend' => $variance > 0
            ? 'up'
            : ($variance < 0 ? 'down' : 'stable')
    ];

    if (abs($variance) > 20) {
        $anomalyCount++;
    }
}
echo json_encode([
    'success' => true,
    'expenses' => $expenses,
    'total' => count($expenses),
    'flagged_count' => $flaggedCount,
    'anomaly_count' => $anomalyCount,
    'duplicate_count' => $duplicateCount,
    'category_count' => count($categoryAnalysis),
    'category_analysis' => $categoryAnalysis,
    'duplicate_alerts' => $duplicateAlerts
]);

$expenseStmt->close();
$categoryStmt->close();
$dupStmt->close();
$connection->close();
