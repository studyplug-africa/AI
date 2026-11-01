<?php
session_start();

header('Content-Type: application/json');

include("../../connection.php");

$connection->set_charset("utf8mb4");


if (!isset($_SESSION['company_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit();
}


$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input'
    ]);
    exit();
}
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
$company_id = intval($_SESSION['company_id']);

$title = trim($input['title'] ?? '');
$due_date = trim($input['due_date'] ?? '');
$recurrence = trim($input['recurrence'] ?? 'monthly');


$reminder_days = $input['reminder_days'] ?? [];

if (is_array($reminder_days)) {
    $reminder_days = json_encode($reminder_days);
} else {
    $reminder_days = json_encode(
        explode(',', str_replace(' ', '', $reminder_days))
    );
}


if (empty($title) || empty($due_date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit();
}

$stmt = $connection->prepare("
    INSERT INTO compliance_items
    (
        company_id,
        title,
        due_date,
        reminder_days,
        status
    )
    VALUES
    (?, ?, ?, ?, ?)
");

$status = 'pending';
$both =$title." ".$recurrence;
$stmt->bind_param(
    "issss",
    $company_id,
    $both,
    $due_date,
    $reminder_days,
    $status
);

$result = $stmt->execute();

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Execute failed: ' . $stmt->error
    ]);
    exit();
}

$stmt->close();
$connection->close();

echo json_encode([
    'success' => true,
    'message' => 'Compliance item added successfully'
]);
?>