<?php
session_start();

header('Content-Type: application/json');

include("../../connection.php");

$connection->set_charset("utf8mb4");

if (!isset($_SESSION['company_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

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

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input'
    ]);
    exit();
}

$id = intval($input['id'] ?? 0);
$title = trim($input['title'] ?? '');
$due_date = trim($input['due_date'] ?? '');
$recurrence = trim($input['recurrence'] ?? '');
$reminder_days = json_encode($input['reminder_days'] ?? []);
$company_id = intval($_SESSION['company_id']);

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ID'
    ]);
    exit();
}

if (empty($title) || empty($due_date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit();
}

$cleanTitle = preg_replace('/\s*(monthly|quarterly|yearly|none)$/i', '', $title);
$cleanTitle = trim($cleanTitle);

$finalTitle = $cleanTitle;
if (!empty($recurrence) && $recurrence !== 'none' && $recurrence !== '') {
    $finalTitle .= " " . $recurrence;
}

$selectStmt = $connection->prepare("
    SELECT title, due_date, reminder_days
    FROM compliance_items
    WHERE id = ? AND company_id = ?
");
$selectStmt->bind_param("ii", $id, $company_id);
$selectStmt->execute();
$result = $selectStmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Compliance item not found'
    ]);
    $selectStmt->close();
    $connection->close();
    exit();
}

$current = $result->fetch_assoc();
$selectStmt->close();

$titleChanged = ($current['title'] !== $finalTitle);
$dueDateChanged = ($current['due_date'] !== $due_date);
$reminderDaysChanged = ($current['reminder_days'] !== $reminder_days);

if (!$titleChanged && !$dueDateChanged && !$reminderDaysChanged) {
    echo json_encode([
        'success' => false,
        'message' => 'No changes detected. Compliance item already up to date.',
        'no_changes' => true
    ]);
    $connection->close();
    exit();
}

$stmt = $connection->prepare("
    UPDATE compliance_items
    SET
        title = ?,
        due_date = ?,
        reminder_days = ?
    WHERE id = ?
    AND company_id = ?
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Prepare failed: ' . $connection->error
    ]);
    exit();
}

$stmt->bind_param(
    "sssii",
    $finalTitle,
    $due_date,
    $reminder_days,
    $id,
    $company_id
);

$result = $stmt->execute();

if ($result && $stmt->affected_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Compliance item updated successfully',
        'changes' => [
            'title_changed' => $titleChanged,
            'due_date_changed' => $dueDateChanged,
            'reminder_days_changed' => $reminderDaysChanged
        ]
    ]);
} elseif ($result && $stmt->affected_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'No changes were made',
        'no_changes' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update compliance item: ' . $stmt->error
    ]);
}

$stmt->close();
$connection->close();
?>