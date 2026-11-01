<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Check if user is logged in
if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

include("../../connection.php");
$connection->set_charset("utf8mb4");

// Create expenses table if not exists
$connection->query("
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    category VARCHAR(255) NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    amount_cents BIGINT NOT NULL,
    invoice_date DATE NOT NULL,
    flagged BOOLEAN DEFAULT FALSE,
    flagged_reason VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['expenses'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$expenses = $input['expenses'];
$company_id = intval($_SESSION['company_id']);

if (empty($expenses)) {
    echo json_encode(['success' => false, 'message' => 'No expenses to upload']);
    exit();
}

//GET ALL EXISTING EXPENSES FROM DATABASE
$stmt = $connection->prepare("
    SELECT category, amount_cents, supplier_name, invoice_date
    FROM expenses
    WHERE company_id = ?
");

$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

// Build lookup arrays from database
 // For exact duplicate detection (supplier + amount + date)
$existingExactKeys = [];
$categorySpend = [];
$categoryCount = [];
$supplierMonthMap = [];

while ($row = $result->fetch_assoc()) {
    $cat = $row['category'];

    $invoiceDate = $row['invoice_date'];
    $threeMonthsAgo = date('Y-m-d', strtotime('-3 months'));

    if ($invoiceDate >= $threeMonthsAgo) {
        $categorySpend[$cat] = ($categorySpend[$cat] ?? 0) + $row['amount_cents'];
        $categoryCount[$cat] = ($categoryCount[$cat] ?? 0) + 1;
    }

    $supplier = strtolower(trim($row['supplier_name']));
    $exactKey = $supplier . '|' . $row['amount_cents'] . '|' . $row['invoice_date'];
    $existingExactKeys[$exactKey] = true;

    $month = date('Y-m', strtotime($row['invoice_date']));
    $supplierMonthKey = $supplier . '|' . $month;
    $supplierMonthMap[$supplierMonthKey] = true;
}

$stmt->close();

// Calculate average spend per category
$categoryAvg = [];
foreach ($categoryCount as $cat => $count) {
    $categoryAvg[$cat] = ($count > 0) ? ($categorySpend[$cat] / $count) : 0;
}

//FILTER OUT EXACT DUPLICATES
// Only keep expenses that are NOT exact duplicates
$newExpenses = [];
$skippedDuplicates = [];

foreach ($expenses as $expense) {
    $supplier = strtolower(trim($expense['supplier_name'] ?? ''));
    $amount = intval($expense['amount_cents'] ?? 0);
    $date = $expense['invoice_date'] ?? '';

    $exactKey = $supplier . '|' . $amount . '|' . $date;

    if (isset($existingExactKeys[$exactKey])) {
        $skippedDuplicates[] = [
            'supplier' => $expense['supplier_name'],
            'amount' => $amount / 100,
            'date' => $date,
            'reason' => 'Exact duplicate already exists in database'
        ];
    } else {
        $newExpenses[] = $expense;
    }
}

// If no new expenses, return early
if (empty($newExpenses)) {
    echo json_encode([
        'success' => false,
        'uploaded' => 0,
        'skipped_duplicates' => count($skippedDuplicates),
        'message' => 'No new expenses to upload. All ' . count($skippedDuplicates) . ' expense(s) already exist in database.',
        'skipped_items' => $skippedDuplicates
    ]);
    exit();
}

//INSERT ONLY NEW EXPENSES
$insert = $connection->prepare("
    INSERT INTO expenses
    (company_id, category, supplier_name, amount_cents, invoice_date, flagged, flagged_reason)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$insert) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed']);
    exit();
}

$successCount = 0;
$errorCount = 0;
$flaggedCount = 0;
$anomalyCount = 0;
$validationErrors = [];
$flaggedItems = [];

$batchSupplierMonthMap = $supplierMonthMap;

foreach ($newExpenses as $index => $expense) {
    $category = trim($expense['category'] ?? '');
    $supplierRaw = $expense['supplier_name'] ?? '';
    $supplier = strtolower(trim($supplierRaw));
    $amount = intval($expense['amount_cents'] ?? 0);
    $date = $expense['invoice_date'] ?? '';
    $rowNumber = $index + 2;

    if (!$category || !$supplier || !$amount || !$date) {
        $validationErrors[] = "Row $rowNumber: Missing required fields";
        $errorCount++;
        continue;
    }

    if ($amount <= 0) {
        $validationErrors[] = "Row $rowNumber: Amount must be greater than 0";
        $errorCount++;
        continue;
    }

    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dt || $dt->format('Y-m-d') !== $date) {
        $validationErrors[] = "Row $rowNumber: Invalid date format. Use YYYY-MM-DD";
        $errorCount++;
        continue;
    }

    $flagged = 0;
    $reasons = [];

    // Check for high spend anomaly (50% above category average)
    if (isset($categoryAvg[$category]) && $categoryAvg[$category] > 0) {
        if ($amount > $categoryAvg[$category] * 1.5) {
            $flagged = 1;
            $percent = round((($amount - $categoryAvg[$category]) / $categoryAvg[$category]) * 100);
            $reasons[] = "High spend: R" . number_format($amount / 100, 2) . " is " . $percent . "% above average " . $category . " spend (R" . number_format($categoryAvg[$category] / 100, 2) . ")";
            $anomalyCount++;
        }
    }

    // Check for repeated supplier in same month
    $month = date('Y-m', strtotime($date));
    $supplierMonthKey = $supplier . '|' . $month;

    if (isset($batchSupplierMonthMap[$supplierMonthKey])) {
        $flagged = 1;
        $reasons[] = "Repeated supplier in same month - possible duplicate billing";
        $anomalyCount++;
    } else {
        $batchSupplierMonthMap[$supplierMonthKey] = true;
    }

    $flaggedReason = !empty($reasons) ? implode(" | ", $reasons) : null;

    $insert->bind_param(
        "issisis",
        $company_id,
        $category,
        $supplierRaw,
        $amount,
        $date,
        $flagged,
        $flaggedReason
    );

    if ($insert->execute()) {
        $successCount++;
        if ($flagged) {
            $flaggedCount++;
            $flaggedItems[] = [
                "row" => $rowNumber,
                "supplier" => $supplierRaw,
                "category" => $category,
                "amount" => $amount / 100,
                "amount_formatted" => "R " . number_format($amount / 100, 2),
                "reason" => $flaggedReason
            ];
        }
    } else {
        $errorCount++;
        $validationErrors[] = "Row $rowNumber: Database error - " . $insert->error;
    }
}

expenseWatchdog($company_id, $connection, 20);


function expenseWatchdog($company_id, $connection, $threshold = 20) {
    
    // Create resolved_flags table if not exists (for tracking resolved duplicate flags)
    $connection->query("
    CREATE TABLE IF NOT EXISTS resolved_flags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        expense_id INT NOT NULL,
        month_year VARCHAR(7) NOT NULL,
        flag_type VARCHAR(50) NOT NULL,
        resolved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_resolution (company_id, expense_id, month_year, flag_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Get all resolved flags
    $resolvedQuery = "SELECT expense_id, month_year, flag_type FROM resolved_flags WHERE company_id = ?";
    $resolvedStmt = $connection->prepare($resolvedQuery);
    $resolvedStmt->bind_param("i", $company_id);
    $resolvedStmt->execute();
    $resolvedResult = $resolvedStmt->get_result();
    
    $resolvedMap = [];
    while ($row = $resolvedResult->fetch_assoc()) {
        $key = $row['expense_id'] . '|' . $row['month_year'] . '|' . $row['flag_type'];
        $resolvedMap[$key] = true;
    }
    $resolvedStmt->close();
    
    // Get existing unresolved alerts to avoid duplicates
    $existingAlertsQuery = "SELECT message FROM alerts WHERE company_id = ? AND resolved = 0";
    $existingStmt = $connection->prepare($existingAlertsQuery);
    $existingStmt->bind_param("i", $company_id);
    $existingStmt->execute();
    $existingResult = $existingStmt->get_result();
    
    $existingAlertMessages = [];
    while ($row = $existingResult->fetch_assoc()) {
        $existingAlertMessages[] = $row['message'];
    }
    $existingStmt->close();
    
    // CREATE ALERTS FOR CATEGORY ANOMALIES
    $categoryQuery = "
        SELECT 
            category,
            SUM(CASE WHEN invoice_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN amount_cents ELSE 0 END) as current_month,
            SUM(CASE WHEN invoice_date < CURDATE() AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) THEN amount_cents ELSE 0 END) / 2 as three_month_avg
        FROM expenses 
        WHERE company_id = ? 
        GROUP BY category
    ";
    
    $stmt = $connection->prepare($categoryQuery);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $categoryResult = $stmt->get_result();
    
    while ($row = $categoryResult->fetch_assoc()) {
        $current = $row['current_month'] / 100;
        $avg = $row['three_month_avg'] / 100;
        
        if ($avg > 0) {
            $variance = (($current - $avg) / $avg) * 100;
            
            if ($variance > $threshold) {
                $severity = $variance > 50 ? 'critical' : 'warning';
                $message = "Spending in " . $row['category'] . " is " . round($variance) . "% above the 3-month average (R" . number_format($current, 2) . " vs R" . number_format($avg, 2) . ")";
                
                if (!in_array($message, $existingAlertMessages)) {
                    $alertStmt = $connection->prepare("
                        INSERT INTO alerts (company_id, module, severity, message, action_label, resolved)
                        VALUES (?, 'expenses', ?, ?, 'Review Category', 0)
                    ");
                    $alertStmt->bind_param("iss", $company_id, $severity, $message);
                    $alertStmt->execute();
                    $alertStmt->close();
                }
            }
        }
    }
    $stmt->close();
    
    //FLAG DUPLICATE INVOICES (Leave ONE unflagged per supplier/month)
    // First, find the first occurrence (lowest ID) for each supplier/month combo
    $firstOccurrenceQuery = "
        SELECT MIN(id) as first_id, supplier_name, DATE_FORMAT(invoice_date, '%Y-%m') as month_year
        FROM expenses
        WHERE company_id = ?
        GROUP BY supplier_name, DATE_FORMAT(invoice_date, '%Y-%m')
        HAVING COUNT(*) > 1
    ";
    
    $firstStmt = $connection->prepare($firstOccurrenceQuery);
    $firstStmt->bind_param("i", $company_id);
    $firstStmt->execute();
    $firstResult = $firstStmt->get_result();
    
    $excludeIds = [];
    while ($row = $firstResult->fetch_assoc()) {
        $excludeIds[] = $row['first_id'];
    }
    $firstStmt->close();
    
    // Now flag ALL duplicate expenses EXCEPT the first occurrence
    $duplicateQuery = "
        SELECT e1.id, e1.supplier_name, DATE_FORMAT(e1.invoice_date, '%Y-%m') as month_year, e1.amount_cents
        FROM expenses e1
        WHERE e1.company_id = ?
        AND EXISTS (
            SELECT 1 FROM expenses e2 
            WHERE e2.supplier_name = e1.supplier_name 
            AND MONTH(e2.invoice_date) = MONTH(e1.invoice_date)
            AND YEAR(e2.invoice_date) = YEAR(e1.invoice_date)
            AND e2.id != e1.id
            AND e2.company_id = ?
        )
        AND e1.flagged = 0
    ";
    
    // Add condition to exclude the first occurrence if we have any
    if (!empty($excludeIds)) {
        $excludeString = implode(',', $excludeIds);
        $duplicateQuery .= " AND e1.id NOT IN ($excludeString)";
    }
    
    $dupStmt = $connection->prepare($duplicateQuery);
    $dupStmt->bind_param("ii", $company_id, $company_id);
    $dupStmt->execute();
    $dupResult = $dupStmt->get_result();
    
    $duplicateIds = [];
    while ($row = $dupResult->fetch_assoc()) {
        $resolvedKey = $row['id'] . '|' . $row['month_year'] . '|duplicate';
        if (!isset($resolvedMap[$resolvedKey])) {
            $duplicateIds[] = $row['id'];
            
            $message = "Duplicate expense detected for supplier " . $row['supplier_name'] . " in " . $row['month_year'];
            if (!in_array($message, $existingAlertMessages)) {
                $alertStmt = $connection->prepare("
                    INSERT INTO alerts (company_id, module, severity, message, action_label, resolved)
                    VALUES (?, 'expenses', 'critical', ?, 'Review Expense', 0)
                ");
                $alertStmt->bind_param("is", $company_id, $message);
                $alertStmt->execute();
                $alertStmt->close();
            }
        }
    }
    $dupStmt->close();
    
    if (!empty($duplicateIds)) {
        $idsString = implode(',', $duplicateIds);
        $updateStmt = $connection->prepare("
            UPDATE expenses 
            SET flagged = 1, 
                flagged_reason = 'Duplicate expense: Same supplier in the same month'
            WHERE id IN ($idsString) AND flagged = 0
        ");
        $updateStmt->execute();
        $updateStmt->close();
    }
    
    return true;
}

$insert->close();
$connection->close();

// Build response
$response = [
    "success" => ($successCount > 0),
    "uploaded" => $successCount,
    "flagged" => $flaggedCount,
    "anomalies" => $anomalyCount,
    "failed" => $errorCount,
    "skipped_duplicates" => count($skippedDuplicates)
];

if (!empty($flaggedItems)) {
    $response["flagged_items"] = $flaggedItems;
}

if (!empty($validationErrors)) {
    $response["validation_errors"] = $validationErrors;
}

if (!empty($skippedDuplicates)) {
    $response["skipped_items"] = $skippedDuplicates;
}

if ($successCount > 0) {
    $response["message"] = $successCount . " new expense(s) uploaded successfully";
    if (count($skippedDuplicates) > 0) {
        $response["message"] .= ", " . count($skippedDuplicates) . " duplicate(s) skipped";
    }
    if ($flaggedCount > 0) {
        $response["message"] .= ", " . $flaggedCount . " flagged for review";
    }
    if ($errorCount > 0) {
        $response["message"] .= ", " . $errorCount . " failed";
    }
} else {
    $response["message"] = "No new expenses were uploaded. " . count($skippedDuplicates) . " duplicate(s) skipped.";
}

echo json_encode($response);
