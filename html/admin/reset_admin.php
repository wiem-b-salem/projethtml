<?php
require_once '../../config/database.php';

// Set up error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/admin_reset.log');

try {
    $pdo = getDBConnection();
    
    // Generate new password hash
    $password = 'Admin@123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, 'admin@petitsgenies.fr']);
    
    // Verify the update
    $stmt = $pdo->prepare("SELECT id, email, password FROM admins WHERE email = ?");
    $stmt->execute(['admin@petitsgenies.fr']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($password, $admin['password'])) {
        echo "Admin password has been reset successfully!<br>";
        echo "Email: admin@petitsgenies.fr<br>";
        echo "Password: Admin@123<br>";
        echo "<br>You can now <a href='login.php'>log in</a> with these credentials.";
    } else {
        echo "Error: Password reset failed. Please try again.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 