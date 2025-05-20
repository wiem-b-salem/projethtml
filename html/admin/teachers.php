<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../../config/database.php';
$pdo = getDBConnection();

// Handle actions (activate, deactivate, delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id'];
    $action = $_POST['action'];
    
    try {
        switch($action) {
            case 'activate':
                $stmt = $pdo->prepare("UPDATE teachers SET status = 'active' WHERE id = ?");
                $stmt->execute([$teacher_id]);
                $message = "Teacher account activated successfully.";
                break;
                
            case 'deactivate':
                $stmt = $pdo->prepare("UPDATE teachers SET status = 'inactive' WHERE id = ?");
                $stmt->execute([$teacher_id]);
                $message = "Teacher account deactivated.";
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
                $stmt->execute([$teacher_id]);
                $message = "Teacher account deleted.";
                break;
        }
        $messageType = 'success';
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all teachers
try {
    $stmt = $pdo->query("SELECT * FROM teachers ORDER BY created_at DESC");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching teachers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Enseignants - Administration</title>
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <style>
        .teachers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            table-layout: fixed;
        }
        
        .teachers-table th,
        .teachers-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            word-wrap: break-word;
            vertical-align: top;
        }
        
        /* Column widths */
        .teachers-table th:nth-child(1), /* ID */
        .teachers-table td:nth-child(1) {
            width: 4%;
        }
        
        .teachers-table th:nth-child(2), /* Name */
        .teachers-table td:nth-child(2) {
            width: 15%;
        }
        
        .teachers-table th:nth-child(3), /* Email */
        .teachers-table td:nth-child(3) {
            width: 15%;
        }
        
        .teachers-table th:nth-child(4), /* Phone */
        .teachers-table td:nth-child(4) {
            width: 10%;
        }
        
        .teachers-table th:nth-child(5), /* Address */
        .teachers-table td:nth-child(5) {
            width: 20%;
        }
        
        .teachers-table th:nth-child(6), /* CIN */
        .teachers-table td:nth-child(6) {
            width: 10%;
        }
        
        .teachers-table th:nth-child(7), /* Status */
        .teachers-table td:nth-child(7) {
            width: 8%;
        }
        
        .teachers-table th:nth-child(8), /* Actions */
        .teachers-table td:nth-child(8) {
            width: 18%;
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            min-width: 200px;
        }
        
        .btn-action {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            transition: background 0.3s;
            white-space: nowrap;
            text-align: center;
            min-width: 80px;
        }
        
        .btn-add {
            background: var(--success-color);
            color: white;
            padding: 8px 16px;
            margin-bottom: 20px;
        }
        
        .btn-edit {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-delete {
            background: var(--danger-color);
            color: white;
        }
        
        .btn-activate {
            background: var(--success-color);
            color: white;
        }
        
        .btn-deactivate {
            background: var(--warning-color);
            color: white;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-active {
            background: #2ecc71;
            color: white;
        }
        
        .status-inactive {
            background: #95a5a6;
            color: white;
        }
        
        /* Responsive table */
        @media screen and (max-width: 1400px) {
            .teachers-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .teachers-table th,
            .teachers-table td {
                white-space: normal;
                min-width: 100px;
            }
            
            .teachers-table th:nth-child(1),
            .teachers-table td:nth-child(1) {
                min-width: 50px;
            }
            
            .teachers-table th:nth-child(2),
            .teachers-table td:nth-child(2) {
                min-width: 120px;
            }
            
            .teachers-table th:nth-child(3),
            .teachers-table td:nth-child(3) {
                min-width: 180px;
            }
            
            .teachers-table th:nth-child(4),
            .teachers-table td:nth-child(4) {
                min-width: 100px;
            }
            
            .teachers-table th:nth-child(5),
            .teachers-table td:nth-child(5) {
                min-width: 200px;
            }
            
            .teachers-table th:nth-child(6),
            .teachers-table td:nth-child(6) {
                min-width: 100px;
            }
            
            .teachers-table th:nth-child(7),
            .teachers-table td:nth-child(7) {
                min-width: 100px;
            }
            
            .teachers-table th:nth-child(8),
            .teachers-table td:nth-child(8) {
                min-width: 250px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }
            
            .btn-action {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <header class="admin-header">
            <h1>Gestion des Enseignants</h1>
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
            <?php if (isset($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="action-bar">
                <a href="add_teacher.php" class="btn-action btn-add">+ Ajouter un enseignant</a>
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Rechercher un enseignant...">
                    <button onclick="searchTeachers()">Rechercher</button>
                </div>
            </div>

            <table class="teachers-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>CIN</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($teacher['id']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['address']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['cin']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($teacher['status']); ?>">
                                    <?php echo ucfirst($teacher['status']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <?php if ($teacher['status'] == 'active'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="btn-action btn-deactivate">Désactiver</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn-action btn-activate">Activer</button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn-action btn-edit">Modifier</a>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?');">
                                    <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-action btn-delete">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        function searchTeachers() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.teachers-table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        }

        // Add event listener for Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchTeachers();
            }
        });
    </script>
</body>
</html> 