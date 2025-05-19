<?php
session_start();
require_once 'config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM parents WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() == 1) {
                $parent = $stmt->fetch();
                if (password_verify($password, $parent['password'])) {
                    $_SESSION['parent_id'] = $parent['id'];
                    $_SESSION['parent_name'] = $parent['first_name'] . ' ' . $parent['last_name'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $message = "Invalid password.";
                }
            } else {
                $message = "Email not found.";
            }
        } catch(PDOException $e) {
            $message = "Login failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Login - Mes premiers pas</title>
    <link rel="stylesheet" href="../css/emepage.css">
    <style>
        .login-form {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-submit {
            background: #4CAF50;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .btn-submit:hover {
            background: #45a049;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            background: #f8f9fa;
        }
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-logo">
                <img src="../images/logo.png" alt="Logo" class="header-image">
            </div>
            <h1>Mes premiers pas - L'école des futurs élites</h1>
            <nav>
                <ul>
                    <li><a href="homepage.html">Home</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="login-form">
            <h2>Parent Login</h2>
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-submit">Login</button>
            </form>
            
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <small>&copy; 2024 Mes premiers pas - Jardin d'enfants. Tous droits réservés.</small>
            </div>
        </div>
    </footer>
</body>
</html> 