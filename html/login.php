<?php
session_start();
require_once '../config/database.php';

// Set up custom error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_debug.log');

$message = '';
$messageType = '';
$warningMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Log the login attempt
    error_log("Login attempt for email: " . $email);

    if (empty($email) || empty($password)) {
        $message = "Veuillez remplir tous les champs.";
        $messageType = 'error';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Get user from database
            $stmt = $pdo->prepare("SELECT * FROM parents WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log the database query result
            error_log("User found: " . ($user ? "Yes" : "No"));
            if ($user) {
                error_log("Stored password hash: " . $user['password']);
                error_log("Attempting to verify password");
            }

            if ($user && password_verify($password, $user['password'])) {
                error_log("Password verification successful");
                
                // Check account status
                if ($user['status'] === 'pending') {
                    // Calculate days remaining until deletion (7 days from creation)
                    $created_at = new DateTime($user['created_at']);
                    $now = new DateTime();
                    $days_remaining = 7 - $now->diff($created_at)->days;
                    
                    if ($days_remaining <= 0) {
                        // Account should be deleted
                        $stmt = $pdo->prepare("DELETE FROM parents WHERE id = ?");
                        $stmt->execute([$user['id']]);
                        $message = "Votre compte a été supprimé car vous n'avez pas fourni les documents requis dans le délai imparti.";
                        $messageType = 'error';
                    } else {
                        // Show warning message
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['is_admin'] = false;
                        $_SESSION['account_status'] = 'pending';
                        $_SESSION['days_remaining'] = $days_remaining;
                        
                        header("Location: dashboard.php");
                        exit();
                    }
                } else if ($user['status'] === 'approved') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['is_admin'] = false;
                    $_SESSION['account_status'] = 'approved';
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $message = "Votre compte a été rejeté ou désactivé. Veuillez contacter l'administration.";
                    $messageType = 'error';
                }
            } else {
                error_log("Password verification failed");
                $message = "Email ou mot de passe incorrect";
                $messageType = 'error';
            }
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $message = "Une erreur est survenue. Veuillez réessayer.";
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
    <title>Connexion - École Maternelle</title>
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <style>
        .message {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
            animation: fadeIn 0.5s ease-in;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 2px solid #ffeeba;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header>
        <h1>Bienvenue à l'École des Petits Génies! 🌟</h1>
        <p>Nous sommes ravis de vous revoir ! 🎨</p>
        <nav>
            <a href="homepage.html">← Retour à la page d'accueil</a>
        </nav>
    </header>

    <div class="container">
        <div class="login-form">
            <h2>Connexion Parent</h2>
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" placeholder="Entrez votre email" required>
                </div>

                <div class="form-group">
                    <label for="password">🔒 Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                </div>

                <button type="submit" class="btn-submit">Se connecter 🚀</button>
            </form>
            
            <div class="register-link">
                <p>Pas encore de compte ? <a href="regestration.php">Inscrivez-vous ici</a></p>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-bottom">
            <p>🏫 École des Petits Génies</p>
            <p>🌟 Où chaque enfant est une étoile qui brille</p>
            <p>📞 Contactez-nous : support@petitsgenies.fr</p>
            <p>© 2024 Tous droits réservés</p>
        </div>
    </footer>
</body>
</html> 