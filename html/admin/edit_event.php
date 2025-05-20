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
$event = null;

// Get event ID from URL
$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    header("Location: events.php");
    exit();
}

// Fetch event data
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        header("Location: events.php");
        exit();
    }
} catch(PDOException $e) {
    $message = "Erreur lors de la récupération de l'événement: " . $e->getMessage();
    $messageType = 'error';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = trim($_POST['location'] ?? '');

    // Validate required fields
    if (empty($title) || empty($event_date) || empty($event_time) || empty($location)) {
        $message = "Tous les champs obligatoires doivent être remplis.";
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE events 
                                  SET title = ?, description = ?, event_date = ?, event_time = ?, 
                                      location = ? 
                                  WHERE id = ?");
            $stmt->execute([
                $title,
                $description,
                $event_date,
                $event_time,
                $location,
                $event_id
            ]);
            
            $message = "Événement modifié avec succès.";
            $messageType = 'success';
            
            // Update event data after successful submission
            $event['title'] = $title;
            $event['description'] = $description;
            $event['event_date'] = $event_date;
            $event['event_time'] = $event_time;
            $event['location'] = $location;
        } catch(PDOException $e) {
            $message = "Erreur lors de la modification de l'événement: " . $e->getMessage();
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
    <title>Modifier un Événement - Administration</title>
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <style>
        .event-form {
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
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="time"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .btn-submit {
            background: var(--success-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-cancel {
            background: var(--danger-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-left: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <header class="admin-header">
            <h1>Modifier un Événement</h1>
            <div class="admin-info">
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>
        </header>

        <nav class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="parents.php">Parents</a></li>
                <li><a href="teachers.php">Enseignants</a></li>
                <li><a href="events.php" class="active">Événements</a></li>
                <li><a href="settings.php">Paramètres</a></li>
            </ul>
        </nav>

        <main class="admin-content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="event-form">
                <div class="form-group">
                    <label for="title">Titre *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="event_date">Date *</label>
                    <input type="date" id="event_date" name="event_date" 
                           value="<?php echo date('Y-m-d', strtotime($event['event_date'])); ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_time">Heure *</label>
                    <input type="time" id="event_time" name="event_time" 
                           value="<?php echo date('H:i', strtotime($event['event_time'])); ?>" required>
                </div>

                <div class="form-group">
                    <label for="location">Lieu *</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                    <a href="events.php" class="btn-cancel">Annuler</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html> 