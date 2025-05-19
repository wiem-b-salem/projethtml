<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required_fields = ['email', 'password', 'first_name', 'last_name', 'phone', 'child_name', 'child_age', 'grade_level'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate phone number (8 digits)
    if (!preg_match('/^[0-9]{8}$/', $data['phone'])) {
        throw new Exception('Invalid phone number format');
    }

    // Validate secondary phone if provided
    if (!empty($data['secondary_phone']) && !preg_match('/^[0-9]{8}$/', $data['secondary_phone'])) {
        throw new Exception('Invalid secondary phone number format');
    }

    $conn = getDBConnection();

    // Start transaction
    $conn->beginTransaction();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM parents WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        throw new Exception('Email already registered');
    }

    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert parent
    $stmt = $conn->prepare("
        INSERT INTO parents (email, password, first_name, last_name, phone, secondary_phone)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['email'],
        $hashed_password,
        $data['first_name'],
        $data['last_name'],
        $data['phone'],
        $data['secondary_phone'] ?? null
    ]);
    
    $parent_id = $conn->lastInsertId();

    // Calculate birth date based on age
    $birth_date = date('Y-m-d', strtotime("-{$data['child_age']} years"));

    // Insert child
    $stmt = $conn->prepare("
        INSERT INTO children (parent_id, first_name, last_name, birth_date, grade_level, allergies, special_needs, other_health_info)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $parent_id,
        $data['child_name'],
        $data['child_name'], // Using same name for last name as it's not provided
        $birth_date,
        $data['grade_level'],
        $data['allergies'] ?? null,
        $data['special_needs'] ?? null,
        $data['other_health_info'] ?? null
    ]);

    // Commit transaction
    $conn->commit();

    // Start session and store user info
    session_start();
    $_SESSION['user_id'] = $parent_id;
    $_SESSION['email'] = $data['email'];
    $_SESSION['first_name'] = $data['first_name'];

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user' => [
            'id' => $parent_id,
            'email' => $data['email'],
            'first_name' => $data['first_name']
        ]
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 