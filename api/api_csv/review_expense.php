<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["admin_name"])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

include("../../connection.php");
$connection->set_charset("utf8mb4");

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? 0;
$company_id = $_SESSION['company_id'];

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid expense ID']);
    exit();
}

// Get expense details to know month and flag type
$expenseQuery = "SELECT invoice_date, flagged_reason FROM expenses WHERE id = ? AND company_id = ?";
$expenseStmt = $connection->prepare($expenseQuery);
$expenseStmt->bind_param("ii", $id, $company_id);
$expenseStmt->execute();
$expenseResult = $expenseStmt->get_result();

if ($expenseResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Expense not found']);
    exit();
}

$expense = $expenseResult->fetch_assoc();
$monthYear = date('Y-m', strtotime($expense['invoice_date']));
$expenseStmt->close();

// Determine flag type from flagged_reason
$flagType = 'anomaly';
if (stripos($expense['flagged_reason'], 'duplicate') !== false) {
    $flagType = 'duplicate';
}

// Create resolved_flags table if not exists
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

// Insert resolved record (ignore if already exists)
$insertStmt = $connection->prepare("
    INSERT IGNORE INTO resolved_flags (company_id, expense_id, month_year, flag_type) 
    VALUES (?, ?, ?, ?)
");
$insertStmt->bind_param("iiss", $company_id, $id, $monthYear, $flagType);
$insertStmt->execute();
$insertStmt->close();

// Clear the flag from the expense
$clearStmt = $connection->prepare("UPDATE expenses SET flagged = 0, flagged_reason = NULL WHERE id = ? AND company_id = ?");
$clearStmt->bind_param("ii", $id, $company_id);
$result = $clearStmt->execute();
$clearStmt->close();

echo json_encode([
    'success' => $result,
    'message' => $result ? 'Expense reviewed and resolved for this month' : 'Failed to review'
]);

$connection->close();
?>