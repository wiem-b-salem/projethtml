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
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception('Email and password are required');
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    $conn = getDBConnection();

    // Get user by email
    $stmt = $conn->prepare("SELECT id, email, password, first_name, last_name FROM parents WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        throw new Exception('Invalid email or password');
    }

    // Start session and store user info
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 