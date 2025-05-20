<?php
session_start();
require_once '../config/database.php';

// Set up custom error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_debug.log');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Log the login attempt
    error_log("Login attempt - Email: " . $email);

    if (empty($email) || empty($password)) {
        $message = "Veuillez remplir tous les champs.";
        $messageType = 'error';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Get user from database
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, password FROM parents WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log the database query result
            error_log("Database query result: " . ($user ? "User found" : "No user found"));
            if ($user) {
                error_log("Stored password hash: " . $user['password']);
                error_log("Attempting to verify password");
            }

            if ($user && password_verify($password, $user['password'])) {
                error_log("Password verified successfully");
                // Set session variables
                $_SESSION['parent_id'] = $user['id'];
                $_SESSION['parent_name'] = $user['first_name'];
                $_SESSION['parent_lastname'] = $user['last_name'];
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                error_log("Login failed - Password verification failed or user not found");
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
    <title>Connexion - École Maternelle</title>
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
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