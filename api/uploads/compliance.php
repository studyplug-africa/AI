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

// Create compliance table if not exists
$connection->query("
CREATE TABLE IF NOT EXISTS compliance_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    due_date DATE NOT NULL,
    reminder_days TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['compliance_items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$complianceItems = $input['compliance_items'];
$company_id = intval($_SESSION['company_id']);

if (empty($complianceItems)) {
    echo json_encode(['success' => false, 'message' => 'No compliance items to upload']);
    exit();
}

// Get all existing compliance items to check for duplicates
$stmt = $connection->prepare("
    SELECT title, due_date FROM compliance_items WHERE company_id = ?
");

$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

$existingKeys = [];
while ($row = $result->fetch_assoc()) {
    $key = $row['title'] . '|' . $row['due_date'];
    $existingKeys[$key] = true;
}
$stmt->close();

// First pass: Filter out duplicates
$newItems = [];
$skippedDuplicates = [];
$batchKeys = [];

foreach ($complianceItems as $item) {
    $title = trim($item['title'] ?? '');
    $due_date = $item['due_date'] ?? '';
    $reminder_days = $item['reminder_days'] ?? [];
    
    // Validate required fields
    if (!$title || !$due_date || empty($reminder_days)) {
        continue;
    }
    
    // Validate date format
    $dt = DateTime::createFromFormat('Y-m-d', $due_date);
    if (!$dt || $dt->format('Y-m-d') !== $due_date) {
        continue;
    }
    
    $key = $title . '|' . $due_date;
    
    // Check if exists in database
    if (isset($existingKeys[$key])) {
        $skippedDuplicates[] = [
            "title" => $title,
            "due_date" => $due_date,
            "reason" => "Already exists in database"
        ];
        continue;
    }
    
    // Check for duplicate within same file
    if (isset($batchKeys[$key])) {
        $skippedDuplicates[] = [
            "title" => $title,
            "due_date" => $due_date,
            "reason" => "Duplicate within same file"
        ];
        continue;
    }
    $batchKeys[$key] = true;
    
    // Convert reminder_days array to JSON string for storage
    $reminder_days_json = json_encode($reminder_days);
    
    $newItems[] = [
        "title" => $title,
        "due_date" => $due_date,
        "reminder_days" => $reminder_days_json,
        "reminder_days_array" => $reminder_days
    ];
}

if (empty($newItems)) {
    echo json_encode([
        'success' => false,
        'message' => 'No new compliance items to add. ' . count($skippedDuplicates) . ' duplicate(s) skipped.',
        'uploaded' => 0,
        'duplicates_skipped' => count($skippedDuplicates),
        'skipped_duplicates' => $skippedDuplicates
    ]);
    exit();
}

// Prepare insert statement
$insert = $connection->prepare("
    INSERT INTO compliance_items (company_id, title, due_date, reminder_days, status, created_at)
    VALUES (?, ?, ?, ?, 'pending', NOW())
");

if (!$insert) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed']);
    exit();
}

// Process new items
$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($newItems as $item) {
    $insert->bind_param(
        "isss",
        $company_id,
        $item['title'],
        $item['due_date'],
        $item['reminder_days']
    );
    
    if ($insert->execute()) {
        $successCount++;
    } else {
        $errorCount++;
        $errors[] = "Failed to insert: " . $item['title'];
    }
}

$insert->close();
$connection->close();

// Build response
$response = [
    "success" => $successCount > 0,
    "uploaded" => $successCount,
    "duplicates_skipped" => count($skippedDuplicates),
    "failed" => $errorCount
];

if (!empty($skippedDuplicates)) {
    $response["skipped_duplicates"] = $skippedDuplicates;
}

if (!empty($errors)) {
    $response["errors"] = $errors;
}

if ($successCount > 0) {
    $response["message"] = $successCount . " compliance item(s) uploaded";
} else {
    $response["message"] = "No new compliance items to upload";
}

if (count($skippedDuplicates) > 0) {
    $response["message"] .= ", " . count($skippedDuplicates) . " duplicate(s) skipped";
}

if ($errorCount > 0) {
    $response["message"] .= ", " . $errorCount . " failed";
}

echo json_encode($response);
?>