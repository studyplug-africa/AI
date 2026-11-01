<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

session_start();

if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

include("../../connection.php");
$connection->set_charset("utf8mb4");

$company_id = $_SESSION['company_id'];

// Count all not paid invoices
$query = "SELECT COUNT(*) as total FROM invoices WHERE company_id = ? AND status!='paid'";
$statement = $connection->prepare($query);
$statement->bind_param("i", $company_id);
$statement->execute();
$result = $statement->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'total' => $row['total']
]);

$statement->close();
$connection->close();
?>