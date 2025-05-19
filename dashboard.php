<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['parent_id'])) {
    header("Location: login.php");
    exit();
}

// Get parent information
try {
    $stmt = $pdo->prepare("SELECT * FROM parents WHERE id = ?");
    $stmt->execute([$_SESSION['parent_id']]);
    $parent = $stmt->fetch();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - Mes premiers pas</title>
    <link rel="stylesheet" href="../css/emepage.css">
    <style>
        .dashboard {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .welcome-message {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .info-section {
            margin-bottom: 2rem;
        }
        .info-section h3 {
            color: #333;
            margin-bottom: 1rem;
        }
        .info-item {
            margin-bottom: 0.5rem;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c82333;
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
                    <li><a href="logout.php" class="logout-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="dashboard">
            <div class="welcome-message">
                <h2>Welcome, <?php echo htmlspecialchars($parent['first_name']); ?>!</h2>
            </div>

            <div class="info-section">
                <h3>Your Information</h3>
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <?php echo htmlspecialchars($parent['email']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <?php echo htmlspecialchars($parent['phone'] ?? 'Not provided'); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Address:</span>
                    <?php echo htmlspecialchars($parent['address'] ?? 'Not provided'); ?>
                </div>
            </div>

            <!-- Add more sections here for student information, grades, etc. -->
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