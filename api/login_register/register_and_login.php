<?php
// api/register_and_login.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
session_start();
include("../../connection.php");

// Check connection
if ($connection->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $connection->connect_error]);
    exit();
}

// Set charset
$connection->set_charset("utf8mb4");

// Create tables if they don't exist
$create_companies_table = "CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$create_users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
)";

// Execute table creation
$connection->query($create_companies_table);
$connection->query($create_users_table);

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$action = $input['action'] ?? '';

if ($action === 'register') {
    registerUser($connection, $input);
} elseif ($action === 'login') {
    loginUser($connection, $input);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function registerUser($connection, $data)
{
    $full_name = trim($data['full_name'] ?? '');
    $company_name = trim($data['company_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';

    // Validation
    if (empty($full_name) || empty($company_name) || empty($email) || empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        return;
    }

    if (!preg_match('/[A-Z]/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain at least one uppercase letter']);
        return;
    }

    if (!preg_match('/[0-9]/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain at least one number']);
        return;
    }

    // Check if email already exists
    $stmt = $connection->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        $stmt->close();
        return;
    }
    $stmt->close();

    // Start transaction
    $connection->begin_transaction();

    try {
        // Create company
        $stmt = $connection->prepare("INSERT INTO companies (name) VALUES (?)");
        $stmt->bind_param("s", $company_name);
        $stmt->execute();
        $company_id = $stmt->insert_id;
        $stmt->close();

        // Create user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';

        $stmt = $connection->prepare("INSERT INTO users (company_id, full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $company_id, $full_name, $email, $phone, $password_hash, $role);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Commit transaction
        $connection->commit();
        $_SESSION["admin_email"] = $email;
    $_SESSION["admin_email"] = $email;
    $_SESSION["admin_name"] =  $full_name;
    $_SESSION["company_name"] =  $company_name;
    $_SESSION["company_id"] =  $company_id;


        // Generate token
        $token_data = [
            'user_id' => $user_id,
            'company_id' => $company_id,
            'email' => $email,
            'expires' => time() + (7 * 24 * 60 * 60)
        ];
        $token = base64_encode(json_encode($token_data));

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'user' => [
                'id' => $user_id,
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'company_id' => $company_id,
                'company_name' => $company_name,
                'role' => $role
            ]
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
}

function loginUser($connection, $data)
{
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password required']);
        return;
    }

    // Get user with company info using JOIN
    $query = "
        SELECT u.*, c.name as company_name 
        FROM users u 
        JOIN companies c ON u.company_id = c.id 
        WHERE u.email = ?
    ";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        return;
    }

    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        return;
    }


    $_SESSION["admin_email"] = $email;
    $_SESSION["admin_name"] =  $user['full_name'];
    $_SESSION["company_name"] =  $user['company_name'];
    $_SESSION["company_id"] =  $user['company_id'];

    // Generate token
    $token_data = [
        'user_id' => $user['id'],
        'company_id' => $user['company_id'],
        'email' => $user['email'],
        'expires' => time() + (7 * 24 * 60 * 60)
    ];
    $token = base64_encode(json_encode($token_data));

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'company_id' => $user['company_id'],
            'company_name' => $user['company_name'],
            'role' => $user['role']
        ]
    ]);
}

// Close connection
$connection->close();
