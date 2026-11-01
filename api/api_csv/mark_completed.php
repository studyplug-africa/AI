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

$stmt = $connection->prepare("UPDATE compliance_items SET status = 'completed' WHERE id = ? AND company_id = ?");
$stmt->bind_param("ii", $id, $company_id);
$result = $stmt->execute();

echo json_encode([
    'success' => $result,
    'message' => $result ? 'Item marked as completed' : 'Failed to update'
]);

$stmt->close();
$connection->close();
?>