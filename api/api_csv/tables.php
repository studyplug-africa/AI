<?php 



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



// Create compliance table if not exists
$connection->query("
CREATE TABLE IF NOT EXISTS compliance_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    due_date DATE NOT NULL,
    reminder_days TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");




// Creating invoices table if not exists
$createTableSQL = "
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    amount_cents BIGINT NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$connection->query($createTableSQL)) {
    echo json_encode(['success' => false, 'message' => 'Failed to create table: ' . $connection->error]);
    exit();
}





  // Create resolved_flags table if not exists (for tracking resolved duplicate flags)
    $connection->query("
    CREATE TABLE IF NOT EXISTS resolved_flags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        expense_id INT NOT NULL,
        month_year VARCHAR(7) NOT NULL,
        flag_type VARCHAR(50) NOT NULL,
        resolved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_resolution (company_id, expense_id, month_year, flag_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");






    
// Create expenses table if not exists
$connection->query("
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    category VARCHAR(255) NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    amount_cents BIGINT NOT NULL,
    invoice_date DATE NOT NULL,
    flagged BOOLEAN DEFAULT FALSE,
    flagged_reason VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");
