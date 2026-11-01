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
    SELECT id, title, due_date, reminder_days, status, created_at,
           DATEDIFF(due_date, CURDATE()) as days_remaining
    FROM compliance_items 
    WHERE company_id = ?
    ORDER BY 
        CASE 
            WHEN due_date < CURDATE() THEN 0 
            ELSE 1 
        END,
        due_date desc
";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$overdue_count = 0;
$upcoming_count = 0;
$completed_count = 0;

while ($row = $result->fetch_assoc()) {
    $daysRemaining = $row['days_remaining'];
    
    if ($row['status'] === 'completed') {
        $row['status'] = 'completed';
        $completed_count++;
    } elseif ($daysRemaining < 0) {
        $row['status'] = 'overdue';
        $overdue_count++;
    } elseif ($daysRemaining <= 7) {
        $row['status'] = 'upcoming';
        $upcoming_count++;
    } else {
        $row['status'] = 'pending';
    }
    
    $items[] = $row;
}

echo json_encode([
    'success' => true,
    'items' => $items,
    'total' => count($items),
    'overdue_count' => $overdue_count,
    'upcoming_count' => $upcoming_count,
    'completed_count' => $completed_count
]);

$stmt->close();
$connection->close();
?>