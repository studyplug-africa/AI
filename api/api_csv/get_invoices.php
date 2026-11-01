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

$query = "
    SELECT id, client_name, amount_cents, due_date, status, created_at,
           DATEDIFF(CURDATE(), due_date) as days_overdue
    FROM invoices 
    WHERE company_id = ?
    ORDER BY 
        CASE WHEN status = 'overdue' THEN 0 ELSE 1 END,
        days_overdue DESC,
        due_date ASC
";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

$invoices = [];
$overdue_count = 0;
$pending_count = 0;
$paid_count = 0;

while ($row = $result->fetch_assoc()) {
    $row['amount'] = $row['amount_cents'] / 100;
    unset($row['amount_cents']);
    
    if ($row['status'] === 'overdue') $overdue_count++;
    elseif ($row['status'] === 'pending') $pending_count++;
    elseif ($row['status'] === 'paid') $paid_count++;
    
    $invoices[] = $row;
}

echo json_encode([
    'success' => true,
    'invoices' => $invoices,
    'total' => count($invoices),
    'overdue_count' => $overdue_count,
    'pending_count' => $pending_count,
    'paid_count' => $paid_count
]);

$stmt->close();
$connection->close();
?>