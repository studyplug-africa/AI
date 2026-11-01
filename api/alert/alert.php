<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Check if user is logged in
if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

include("../../connection.php");
$connection->set_charset("utf8mb4");

// CREATE ALERTS TABLE IF NOT EXISTS 
$createTableSQL = "
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    module VARCHAR(50) NOT NULL,
    severity VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    action_label VARCHAR(100) DEFAULT NULL,
    resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$connection->query($createTableSQL)) {
    echo json_encode(['success' => false, 'message' => 'Failed to create alerts table: ' . $connection->error]);
    exit();
}

$company_id = $_SESSION['company_id'];

function generateAlerts($company_id, $connection) {
    
    $alertsInserted = 0;
    
    //  INVOICE ALERTS 
    $invoiceQuery = "
        SELECT invoices.id, invoices.client_name, invoices.amount_cents, invoices.due_date, 
               DATEDIFF(CURDATE(), invoices.due_date) as days_overdue
        FROM invoices 
        WHERE invoices.company_id = ? 
        AND invoices.status != 'paid'
        AND invoices.due_date < CURDATE()
    ";
    
    $statement = $connection->prepare($invoiceQuery);
    if ($statement) {
        $statement->bind_param("i", $company_id);
        $statement->execute();
        $result = $statement->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $daysOverdue = $row['days_overdue'];
            $alertMessage = '';
            $severity = '';
            
            if ($daysOverdue > 30) {
                $severity = 'critical';
                $alertMessage = "Invoice from " . $row['client_name'] . " for R" . number_format($row['amount_cents']/100, 2) . " is " . $daysOverdue . " days overdue. Immediate action required.";
            } elseif ($daysOverdue > 7) {
                $severity = 'warning';
                $alertMessage = "Invoice from " . $row['client_name'] . " for R" . number_format($row['amount_cents']/100, 2) . " is " . $daysOverdue . " days overdue.";
            }
            
            if (!empty($alertMessage)) {
                if (insertAlert($connection, $company_id, 'invoices', $severity, $alertMessage, 'View Invoice')) {
                    $alertsInserted++;
                }
            }
        }
        $statement->close();
    }
    
//  EXPENSE ALERTS (for already flagged duplicates)
$flaggedQuery = "
    SELECT id, supplier_name, amount_cents, invoice_date, flagged_reason
    FROM expenses 
    WHERE company_id = ? 
    AND flagged = 1
";

$statement = $connection->prepare($flaggedQuery);
if ($statement) {
    $statement->bind_param("i", $company_id);
    $statement->execute();
    $result = $statement->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $alertMessage = "Duplicate expense detected for supplier " . $row['supplier_name'] . " for R" . number_format($row['amount_cents']/100, 2) . " on " . $row['invoice_date'];
        
        if (!alertExists($connection, $company_id, 'expenses', $alertMessage)) {
            if (insertAlert($connection, $company_id, 'expenses', 'critical', $alertMessage, 'Review Expense')) {
                $alertsInserted++;
            }
        }
    }
    $statement->close();
}
    
    //  EXPENSE ALERTS (High spend anomalies >50% above average) 
    $anomalyQuery = "
        SELECT expenses_current.id, expenses_current.category, expenses_current.supplier_name, 
               expenses_current.amount_cents, expenses_current.invoice_date,
               AVG(expenses_previous.amount_cents) as average_amount
        FROM expenses as expenses_current
        LEFT JOIN expenses as expenses_previous ON expenses_current.category = expenses_previous.category 
            AND expenses_previous.id != expenses_current.id
        WHERE expenses_current.company_id = ? 
        AND expenses_current.invoice_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        GROUP BY expenses_current.id
        HAVING expenses_current.amount_cents > AVG(expenses_previous.amount_cents) * 1.5
    ";
    
    $statement = $connection->prepare($anomalyQuery);
    if ($statement) {
        $statement->bind_param("i", $company_id);
        $statement->execute();
        $result = $statement->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $averageAmount = $row['average_amount'] ?? $row['amount_cents'];
            if ($averageAmount > 0) {
                $percentAbove = round((($row['amount_cents'] - $averageAmount) / $averageAmount) * 100);
                
                $alertMessage = "Unusually high expense in " . $row['category'] . " category. " . $row['supplier_name'] . " - R" . number_format($row['amount_cents']/100, 2) . " is " . $percentAbove . "% above average.";
                
                if (!alertExists($connection, $company_id, 'expenses', $alertMessage)) {
                    if (insertAlert($connection, $company_id, 'expenses', 'warning', $alertMessage, 'Investigate')) {
                        $alertsInserted++;
                    }
                }
            }
        }
        $statement->close();
    }
    
    // COMPLIANCE ALERTS 
    $complianceQuery = "
        SELECT compliance_items.id, compliance_items.title, compliance_items.due_date, 
               DATEDIFF(compliance_items.due_date, CURDATE()) as days_remaining
        FROM compliance_items 
        WHERE compliance_items.company_id = ?
    ";
    
    $statement = $connection->prepare($complianceQuery);
    if ($statement) {
        $statement->bind_param("i", $company_id);
        $statement->execute();
        $result = $statement->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $daysRemaining = $row['days_remaining'];
            $alertMessage = '';
            $severity = '';
            
            if ($daysRemaining < 0) {
                $severity = 'critical';
                $alertMessage = $row['title'] . " was due on " . $row['due_date'] . ". This is OVERDUE by " . abs($daysRemaining) . " days.";
            } elseif ($daysRemaining <= 7 && $daysRemaining >= 0) {
                $severity = 'warning';
                $alertMessage = $row['title'] . " is due in " . $daysRemaining . " days on " . $row['due_date'] . ". Please take action.";
            }
            
            if (!empty($alertMessage)) {
                if (!alertExists($connection, $company_id, 'compliance', $alertMessage)) {
                    if (insertAlert($connection, $company_id, 'compliance', $severity, $alertMessage, 'View Details')) {
                        $alertsInserted++;
                    }
                }
            }
        }
        $statement->close();
    }
    
    return $alertsInserted;
}

// Helper function to insert an alert into the alerts table
function insertAlert($connection, $company_id, $module, $severity, $message, $action_label) {
    
    // Remove severity prefix from message if present
    $cleanMessage = preg_replace('/^(CRITICAL:|WARNING:|INFO:)\s*/', '', $message);
    
    // Check if ANY alert with same module and message already exists (resolved or not)
    $checkStatement = $connection->prepare("
        SELECT alerts.id FROM alerts 
        WHERE alerts.company_id = ? 
        AND alerts.module = ? 
        AND alerts.message = ? 
        LIMIT 1
    ");
    
    if ($checkStatement) {
        $checkStatement->bind_param("iss", $company_id, $module, $cleanMessage);
        $checkStatement->execute();
        $checkResult = $checkStatement->get_result();
        
        if ($checkResult->num_rows > 0) {
            $checkStatement->close();
            return false;
        }
        $checkStatement->close();
    }
    
    $insertStatement = $connection->prepare("
        INSERT INTO alerts (company_id, module, severity, message, action_label, resolved, created_at)
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");
    
    if ($insertStatement) {
        $insertStatement->bind_param("issss", $company_id, $module, $severity, $cleanMessage, $action_label);
        $result = $insertStatement->execute();
        $insertStatement->close();
        return $result;
    }
    
    return false;
}

// Helper function to check if an alert already exists (including resolved ones)
function alertExists($connection, $company_id, $module, $message) {
    $cleanMessage = preg_replace('/^(CRITICAL:|WARNING:|INFO:)\s*/', '', $message);
    
    $statement = $connection->prepare("
        SELECT alerts.id FROM alerts 
        WHERE alerts.company_id = ? 
        AND alerts.module = ? 
        AND alerts.message = ? 
        LIMIT 1
    ");
    
    if ($statement) {
        $statement->bind_param("iss", $company_id, $module, $cleanMessage);
        $statement->execute();
        $result = $statement->get_result();
        $exists = $result->num_rows > 0;
        $statement->close();
        return $exists;
    }
    
    return false;
}

// Function to get all unresolved alerts for dashboard
function getUnresolvedAlerts($company_id, $connection, $limit = 10) {
    $statement = $connection->prepare("
        SELECT alerts.id, alerts.module, alerts.severity, alerts.message, alerts.action_label, alerts.created_at
        FROM alerts 
        WHERE alerts.company_id = ? AND alerts.resolved = 0
        ORDER BY 
            CASE alerts.severity 
                WHEN 'critical' THEN 1 
                WHEN 'warning' THEN 2 
                ELSE 3 
            END,
            alerts.created_at DESC
        LIMIT ?
    ");
    
    if ($statement) {
        $statement->bind_param("ii", $company_id, $limit);
        $statement->execute();
        $result = $statement->get_result();
        
        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        $statement->close();
        return $alerts;
    }
    
    return [];
}

// Function to resolve a single alert
function resolveAlert($alert_id, $company_id, $connection) {
    $statement = $connection->prepare("
        UPDATE alerts 
        SET alerts.resolved = 1
        WHERE alerts.id = ? AND alerts.company_id = ?
    ");
    
    if ($statement) {
        $statement->bind_param("ii", $alert_id, $company_id);
        $result = $statement->execute();
        $statement->close();
        return $result;
    }
    
    return false;
}

// Function to get alert counts by severity
function getAlertCounts($company_id, $connection) {
    $statement = $connection->prepare("
        SELECT 
            SUM(CASE WHEN alerts.severity = 'critical' AND alerts.resolved = 0 THEN 1 ELSE 0 END) as critical_count,
            SUM(CASE WHEN alerts.severity = 'warning' AND alerts.resolved = 0 THEN 1 ELSE 0 END) as warning_count,
            SUM(CASE WHEN alerts.severity = 'info' AND alerts.resolved = 0 THEN 1 ELSE 0 END) as info_count,
            COUNT(alerts.id) as total_count
        FROM alerts 
        WHERE alerts.company_id = ? AND alerts.resolved = 0
    ");
    
    if ($statement) {
        $statement->bind_param("i", $company_id);
        $statement->execute();
        $result = $statement->get_result();
        $counts = $result->fetch_assoc();
        $statement->close();
        return $counts;
    }
    
    return ['critical_count' => 0, 'warning_count' => 0, 'info_count' => 0, 'total_count' => 0];
}

$alertsGenerated = generateAlerts($company_id, $connection);

echo json_encode([
    'success' => true,
    'alerts_generated' => $alertsGenerated,
    'message' => 'Alert engine ran successfully',
    'timestamp' => date('Y-m-d H:i:s')
]);

$connection->close();
?>