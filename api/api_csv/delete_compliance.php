<?php
session_start();
header('Content-Type: application/json');
include("../../connection.php");

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'];
$company_id = $_SESSION['company_id'];

$stmt = $connection->prepare("DELETE FROM compliance_items WHERE id = ? AND company_id = ?");
$stmt->bind_param("ii", $id, $company_id);
$result = $stmt->execute();

echo json_encode(['success' => $result]);
?>