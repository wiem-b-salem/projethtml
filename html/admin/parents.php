<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../../config/database.php';
$pdo = getDBConnection();

// Handle actions (approve, reject, deactivate)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['parent_id'])) {
    $parent_id = $_POST['parent_id'];
    $action = $_POST['action'];
    
    try {
        switch($action) {
            case 'approve':
                $stmt = $pdo->prepare("UPDATE parents SET status = 'approved' WHERE id = ?");
                $stmt->execute([$parent_id]);
                $message = "Parent account approved successfully.";
                break;
                
            case 'reject':
                $stmt = $pdo->prepare("UPDATE parents SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$parent_id]);
                $message = "Parent account rejected.";
                break;
                
            case 'deactivate':
                $stmt = $pdo->prepare("UPDATE parents SET status = 'inactive' WHERE id = ?");
                $stmt->execute([$parent_id]);
                $message = "Parent account deactivated.";
                break;
                
            case 'activate':
                $stmt = $pdo->prepare("UPDATE parents SET status = 'active' WHERE id = ?");
                $stmt->execute([$parent_id]);
                $message = "Parent account activated.";
                break;
        }
        $messageType = 'success';
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all parents with their children
try {
    $stmt = $pdo->query("
        SELECT 
            p.*,
            COUNT(c.id) as children_count,
            GROUP_CONCAT(CONCAT(c.first_name, ' ', c.last_name) SEPARATOR ', ') as children_names
        FROM parents p
        LEFT JOIN children c ON p.id = c.parent_id
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching parents: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Parents - Administration</title>
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <style>
        .parents-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            table-layout: fixed;
        }
        
        .parents-table th,
        .parents-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            word-wrap: break-word;
            vertical-align: top;
        }
        
        /* Column widths */
        .parents-table th:nth-child(1), /* ID */
        .parents-table td:nth-child(1) {
            width: 4%;
        }
        
        .parents-table th:nth-child(2), /* Name */
        .parents-table td:nth-child(2) {
            width: 12%;
        }
        
        .parents-table th:nth-child(3), /* Email */
        .parents-table td:nth-child(3) {
            width: 18%;
        }
        
        .parents-table th:nth-child(4), /* Phone */
        .parents-table td:nth-child(4) {
            width: 8%;
        }
        
        .parents-table th:nth-child(5), /* Children */
        .parents-table td:nth-child(5) {
            width: 15%;
        }
        
        .parents-table th:nth-child(6), /* Status */
        .parents-table td:nth-child(6) {
            width: 8%;
        }
        
        .parents-table th:nth-child(7), /* Registration Date */
        .parents-table td:nth-child(7) {
            width: 10%;
        }
        
        .parents-table th:nth-child(8), /* Actions */
        .parents-table td:nth-child(8) {
            width: 25%;
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
        
        .btn-approve {
            background: var(--success-color);
            color: white;
        }
        
        .btn-reject {
            background: var(--danger-color);
            color: white;
        }
        
        .btn-deactivate {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-activate {
            background: var(--success-color);
            color: white;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-pending {
            background: #f1c40f;
            color: #000;
        }
        
        .status-approved {
            background: #2ecc71;
            color: white;
        }
        
        .status-rejected {
            background: #e74c3c;
            color: white;
        }
        
        .status-inactive {
            background: #95a5a6;
            color: white;
        }
        
        .search-bar {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        
        .search-bar input {
            flex: 1;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        .search-bar button {
            padding: 8px 16px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        /* Responsive table */
        @media screen and (max-width: 1400px) {
            .parents-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .parents-table th,
            .parents-table td {
                white-space: normal;
                min-width: 100px;
            }
            
            .parents-table th:nth-child(1),
            .parents-table td:nth-child(1) {
                min-width: 50px;
            }
            
            .parents-table th:nth-child(2),
            .parents-table td:nth-child(2) {
                min-width: 120px;
            }
            
            .parents-table th:nth-child(3),
            .parents-table td:nth-child(3) {
                min-width: 180px;
            }
            
            .parents-table th:nth-child(4),
            .parents-table td:nth-child(4) {
                min-width: 100px;
            }
            
            .parents-table th:nth-child(5),
            .parents-table td:nth-child(5) {
                min-width: 150px;
            }
            
            .parents-table th:nth-child(6),
            .parents-table td:nth-child(6) {
                min-width: 100px;
            }
            
            .parents-table th:nth-child(7),
            .parents-table td:nth-child(7) {
                min-width: 100px;
            }
            
            .parents-table th:nth-child(8),
            .parents-table td:nth-child(8) {
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
            <h1>Gestion des Parents</h1>
            <div class="admin-info">
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>
        </header>

        <nav class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="parents.php" class="active">Parents</a></li>
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

            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Rechercher un parent...">
                <button onclick="searchParents()">Rechercher</button>
            </div>

            <table class="parents-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Enfants</th>
                        <th>Statut</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parents as $parent): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($parent['id']); ?></td>
                            <td><?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($parent['email']); ?></td>
                            <td><?php echo htmlspecialchars($parent['phone']); ?></td>
                            <td>
                                <?php 
                                echo htmlspecialchars($parent['children_count'] . ' enfant(s)');
                                if ($parent['children_names']) {
                                    echo '<br><small>' . htmlspecialchars($parent['children_names']) . '</small>';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($parent['status'] ?? 'pending'); ?>">
                                    <?php echo ucfirst($parent['status'] ?? 'pending'); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($parent['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if ($parent['status'] == 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-action btn-approve">Approuver</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-action btn-reject">Rejeter</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($parent['status'] == 'active'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="btn-action btn-deactivate">Désactiver</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($parent['status'] == 'inactive'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn-action btn-activate">Activer</button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="edit_parent.php?id=<?php echo $parent['id']; ?>" class="btn-action">Modifier</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        function searchParents() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.parents-table');
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
                searchParents();
            }
        });
    </script>
</body>
</html> 