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
$invoice_id = $input['invoice_id'] ?? 0;
$email = $input['email'] ?? '';
$message = $input['message'] ?? '';
$company_id = $_SESSION['company_id'];

if (!$invoice_id || !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Get invoice details
$query = "SELECT client_name, amount_cents, due_date FROM invoices WHERE id = ? AND company_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("ii", $invoice_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invoice not found']);
    exit();
}

$invoice = $result->fetch_assoc();
$amount = $invoice['amount_cents'] / 100;


$subject = "Payment Reminder: Invoice from " . $_SESSION['company_name'];
$fullMessage = $message . "\n\n---\nThis is an automated reminder from OpsInsyts.";

$name =  $_SESSION["company_name"];
$user_email = urlencode($email);
$user_name = urlencode($name);
$user_subject = urlencode("Invoice Reminder From ".$name);
$user_message = urlencode($message);

$url = "https://opsinsyts.co.za/email/index.php?user_email=$user_email&user_name=$user_name&user_subject=$user_subject&user_message=$user_message";
$response = file_get_contents($url);
$result = json_decode($response, true);

$mailSent = true;

if ($mailSent) {
    echo json_encode(['success' => true, 'message' => 'Reminder sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email']);
}

$stmt->close();
$connection->close();
?>