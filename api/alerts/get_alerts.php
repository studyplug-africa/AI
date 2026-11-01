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
    SELECT id, module, severity, message, action_label, created_at
    FROM alerts 
    WHERE company_id = ? AND resolved = 0
    ORDER BY 
        CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END,
        created_at DESC 
";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();

$alerts = [];
$critical = 0;
$warning = 0;
$info = 0;

while ($row = $result->fetch_assoc()) {
    $alerts[] = $row;
    if ($row['severity'] === 'critical') $critical++;
    elseif ($row['severity'] === 'warning') $warning++;
    else $info++;
}

echo json_encode([
    'success' => true,
    'alerts' => $alerts,
    'total' => count($alerts),
    'critical_count' => $critical,
    'warning_count' => $warning,
    'info_count' => $info
]);

$stmt->close();
$connection->close();
?>