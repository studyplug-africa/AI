<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Checking if user is logged in
if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

include("../../connection.php");
$connection->set_charset("utf8mb4");

// Creating invoices table if not exists
$createTableSQL = "
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    amount_cents BIGINT NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$connection->query($createTableSQL)) {
    echo json_encode(['success' => false, 'message' => 'Failed to create table: ' . $connection->error]);
    exit();
}

// Getting POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. No data received.']);
    exit();
}

$invoices = isset($input['invoices']) ? $input['invoices'] : [];
$company_id = isset($input['company_id']) ? $input['company_id'] : $_SESSION['company_id'];

if (empty($invoices)) {
    echo json_encode(['success' => false, 'message' => 'No invoices to upload']);
    exit();
}

// Validating company_id
$company_id = intval($company_id);
if ($company_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid company ID']);
    exit();
}

//all existing invoices for this company
$existingQuery = "SELECT client_name, amount_cents, due_date FROM invoices WHERE company_id = ?";
$existingStmt = $connection->prepare($existingQuery);
$existingStmt->bind_param("i", $company_id);
$existingStmt->execute();
$existingResult = $existingStmt->get_result();

// Creating a unique key for each existing invoice
$existingKeys = [];
while ($row = $existingResult->fetch_assoc()) {
    $key = $row['client_name'] . '|' . $row['amount_cents'] . '|' . $row['due_date'];
    $existingKeys[$key] = true;
}
$existingStmt->close();

$newInvoices = [];
$duplicateCount = 0;
$skippedDuplicates = [];

// Filtering out invoices that already exist
foreach ($invoices as $invoice) {
    $key = $invoice['client_name'] . '|' . intval($invoice['amount_cents']) . '|' . $invoice['due_date'];
    
    if (isset($existingKeys[$key])) {
        $duplicateCount++;
        $skippedDuplicates[] = $invoice['client_name'] . ' (R' . number_format(intval($invoice['amount_cents'])/100, 2) . ' due ' . $invoice['due_date'] . ')';
    } else {
        $newInvoices[] = $invoice;
    }
}

if (empty($newInvoices)) {
    echo json_encode([
        'success' => false,
        'message' => 'No new invoices to add. All ' . $duplicateCount . ' invoice(s) already exist in the database.',
        'new' => 0,
        'duplicates' => $duplicateCount,
        'skipped' => $skippedDuplicates
    ]);
    exit();
}

// Insert only new invoices
$successCount = 0;
$errorCount = 0;
$errors = [];

$stmt = $connection->prepare("INSERT INTO invoices (company_id, client_name, amount_cents, due_date, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $connection->error]);
    exit();
}

foreach ($newInvoices as $invoice) {
    // Validate required fields
    if (empty($invoice['client_name']) || empty($invoice['amount_cents']) || empty($invoice['due_date']) || empty($invoice['status'])) {
        $errorCount++;
        $errors[] = 'Missing required fields for invoice: ' . json_encode($invoice);
        continue;
    }
    
    $client_name = $invoice['client_name'];
    $amount_cents = intval($invoice['amount_cents']);
    $due_date = $invoice['due_date'];
    $status = $invoice['status'];
    
    // Validate amount
    if ($amount_cents <= 0) {
        $errorCount++;
        $errors[] = 'Invalid amount for client: ' . $client_name;
        continue;
    }
    
    // Validate date format
    $date = DateTime::createFromFormat('Y-m-d', $due_date);
    if (!$date || $date->format('Y-m-d') !== $due_date) {
        $errorCount++;
        $errors[] = 'Invalid date format for client: ' . $client_name . ' (use YYYY-MM-DD)';
        continue;
    }
    
    // Validate status
    $validStatuses = ['pending', 'overdue', 'paid'];
    if (!in_array($status, $validStatuses)) {
        $errorCount++;
        $errors[] = 'Invalid status for client: ' . $client_name . ' (must be pending, overdue, or paid)';
        continue;
    }
    
    // Insert new invoice
    $stmt->bind_param("isiss", $company_id, $client_name, $amount_cents, $due_date, $status);
    
    if ($stmt->execute()) {
        $successCount++;
    } else {
        $errorCount++;
        $errors[] = 'Database error for client ' . $client_name . ': ' . $stmt->error;
    }
}

$stmt->close();
$connection->close();

// Build response message
$messageParts = [];
if ($successCount > 0) $messageParts[] = $successCount . ' new invoice(s) added';
if ($duplicateCount > 0) $messageParts[] = $duplicateCount . ' duplicate(s) skipped';
if ($errorCount > 0) $messageParts[] = $errorCount . ' failed';

$response = [
    'success' => ($successCount > 0),
    'message' => implode(', ', $messageParts),
    'new' => $successCount,
    'duplicates' => $duplicateCount,
    'failed' => $errorCount
];

if ($duplicateCount > 0) {
    $response['skipped_duplicates'] = $skippedDuplicates;
}

if ($errorCount > 0) {
    $response['errors'] = $errors;
}

echo json_encode($response);
?>