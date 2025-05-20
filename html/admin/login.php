<?php
session_start();
require_once '../../config/database.php';

// Set up custom error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/admin_login_debug.log');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Log the login attempt (without password)
    error_log("Admin login attempt - Email: " . $email);
    error_log("Admin login attempt - Password length: " . strlen($password));

    if (empty($email) || empty($password)) {
        $message = "Veuillez remplir tous les champs.";
        $messageType = 'error';
        error_log("Login failed - Empty fields");
    } else {
        try {
            $pdo = getDBConnection();
            
            // Get admin from database
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, password FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log the database query result
            error_log("Admin database query result: " . ($admin ? "Admin found" : "No admin found"));
            if ($admin) {
                error_log("Stored password hash: " . $admin['password']);
                error_log("Attempting to verify password");
                
                // Debug password verification
                $verify_result = password_verify($password, $admin['password']);
                error_log("Password verification result: " . ($verify_result ? "Success" : "Failed"));
                
                if ($verify_result) {
                    error_log("Admin password verified successfully");
                    
                    // Update last login
                    $stmt = $pdo->prepare("UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$admin['id']]);
                    
                    // Set session variables
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['first_name'];
                    $_SESSION['admin_lastname'] = $admin['last_name'];
                    $_SESSION['is_admin'] = true;
                    
                    error_log("Session variables set - Redirecting to dashboard");
                    
                    // Redirect to admin dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    error_log("Login failed - Password verification failed");
                    $message = "Email ou mot de passe incorrect.";
                    $messageType = 'error';
                }
            } else {
                error_log("Login failed - Admin not found");
                $message = "Email ou mot de passe incorrect.";
                $messageType = 'error';
            }
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $message = "Erreur de connexion: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - École Maternelle</title>
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="admin-login-form">
            <h2>Administration</h2>
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">📧 Email Administrateur</label>
                    <input type="email" id="email" name="email" placeholder="Entrez votre email" required>
                </div>

                <div class="form-group">
                    <label for="password">🔒 Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                </div>

                <button type="submit" class="btn-submit">Se connecter 🚀</button>
            </form>
            
            <div class="back-link">
                <a href="../homepage.html">← Retour au site</a>
            </div>
        </div>
    </div>
</body>
</html> 