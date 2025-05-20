<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../../config/database.php';
$pdo = getDBConnection();

$message = '';
$messageType = '';

// Get teacher ID from URL
$teacher_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$teacher_id) {
    header("Location: teachers.php");
    exit();
}

// Get teacher data
try {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->execute([$teacher_id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacher) {
        header("Location: teachers.php");
        exit();
    }
} catch(PDOException $e) {
    $message = "Erreur: " . $e->getMessage();
    $messageType = 'error';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $cin = trim($_POST['cin']);
    $status = $_POST['status'];
    
    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address) || empty($cin)) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $messageType = 'error';
    } else {
        try {
            // Check if email or CIN already exists for other teachers
            $stmt = $pdo->prepare("SELECT id FROM teachers WHERE (email = ? OR cin = ?) AND id != ?");
            $stmt->execute([$email, $cin, $teacher_id]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Un enseignant avec cet email ou ce CIN existe déjà.";
                $messageType = 'error';
            } else {
                // Update teacher
                $stmt = $pdo->prepare("UPDATE teachers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, cin = ?, status = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $email, $phone, $address, $cin, $status, $teacher_id]);
                
                $message = "Enseignant modifié avec succès.";
                $messageType = 'success';
                
                // Refresh teacher data
                $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
                $stmt->execute([$teacher_id]);
                $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            $message = "Erreur: " . $e->getMessage();
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
    <title>Modifier un Enseignant - Administration</title>
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-color);
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1em;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-submit {
            background: var(--success-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        
        .btn-cancel {
            background: var(--danger-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
        }
        
        .required::after {
            content: " *";
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <header class="admin-header">
            <h1>Modifier un Enseignant</h1>
            <div class="admin-info">
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>
        </header>

        <nav class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="parents.php">Parents</a></li>
                <li><a href="teachers.php" class="active">Enseignants</a></li>
                <li><a href="children.php">Enfants</a></li>
                <li><a href="settings.php">Paramètres</a></li>
            </ul>
        </nav>

        <main class="admin-content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="first_name" class="required">Prénom</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name" class="required">Nom</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="required">Téléphone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher['phone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="address" class="required">Adresse</label>
                        <textarea id="address" name="address" required><?php echo htmlspecialchars($teacher['address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="cin" class="required">CIN</label>
                        <input type="text" id="cin" name="cin" value="<?php echo htmlspecialchars($teacher['cin']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="status" class="required">Statut</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo $teacher['status'] == 'active' ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactive" <?php echo $teacher['status'] == 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                        <a href="teachers.php" class="btn-cancel">Annuler</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 