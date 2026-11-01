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
$invoice_id = $input['id'] ?? 0;
$company_id = $_SESSION['company_id'];

if ($invoice_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
    exit();
}

$stmt = $connection->prepare("UPDATE invoices SET status = 'paid' WHERE id = ? AND company_id = ?");
$stmt->bind_param("ii", $invoice_id, $company_id);
$result = $stmt->execute();

if ($result && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Invoice marked as paid']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invoice not found or already paid']);
}

$stmt->close();
$connection->close();
?>