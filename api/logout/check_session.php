<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_name'])) {
    echo json_encode(['session_expired' => true, 'message' => 'Session expired']);
    exit();
}

echo json_encode(['session_expired' => false, 'user_email' => $_SESSION['user_email']]);
?>