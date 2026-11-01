<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

include("../../connection.php");
$connection->set_charset("utf8mb4");

$company_id = $_SESSION['company_id'];

// Initialize session variables
if (!isset($_SESSION['last_show_time'])) {
    $_SESSION['last_show_time'] = 0;
}
if (!isset($_SESSION['shown_alerts'])) {
    $_SESSION['shown_alerts'] = [];
}

$current_time = time();
$five_minutes = 300;

// Get ALL unresolved alerts
$query = "
    SELECT id, module, severity, message, action_label, created_at
    FROM alerts 
    WHERE company_id = ? 
    AND resolved = 0
    ORDER BY 
        CASE severity 
            WHEN 'critical' THEN 1 
            WHEN 'warning' THEN 2 
            ELSE 3 
        END,
        id DESC
";

$statement = $connection->prepare($query);
$statement->bind_param("i", $company_id);
$statement->execute();
$result = $statement->get_result();

$alerts = [];
while ($row = $result->fetch_assoc()) {
    $alerts[] = $row;
}
$statement->close();
$connection->close();

$should_show = false;
$alert_to_show = null;
$is_new_alert = false;

if (!empty($alerts)) {
    // Get the first alert (most critical + newest)
    $latest_alert = $alerts[0];
    
    // Check if this alert has been shown before
    $is_new_alert = !in_array($latest_alert['id'], $_SESSION['shown_alerts']);
    
    // Get time since last show
    $time_since_last = $current_time - $_SESSION['last_show_time'];
    
    if ($is_new_alert) {
        // NEW ALERT - Show immediately
        $should_show = true;
        $alert_to_show = $latest_alert;
        $_SESSION['shown_alerts'][] = $latest_alert['id'];
        $_SESSION['last_show_time'] = $current_time;
    } 
    elseif ($time_since_last >= $five_minutes) {
        // Same alert, but 5 minutes passed - show again
        $should_show = true;
        $alert_to_show = $latest_alert;
        $_SESSION['last_show_time'] = $current_time;
    }
}

$time_remaining = ($_SESSION['last_show_time'] + $five_minutes) - $current_time;
if ($time_remaining < 0) $time_remaining = 0;

echo json_encode([
    'success' => true,
    'should_show' => $should_show,
    'alert' => $alert_to_show,
    'total_alerts' => count($alerts),
    'is_new_alert' => $is_new_alert,
    'time_remaining' => $time_remaining,
    'debug' => [
        'session_shown' => $_SESSION['shown_alerts'],
        'last_show_time' => $_SESSION['last_show_time'],
        'current_time' => $current_time,
        'time_since_last' => ($current_time - $_SESSION['last_show_time'])
    ]
]);
?>