<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../../config/database.php';
$pdo = getDBConnection();

$parent_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$messageType = '';

// Get parent information
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               GROUP_CONCAT(c.id) as child_ids,
               GROUP_CONCAT(c.first_name) as child_first_names,
               GROUP_CONCAT(c.last_name) as child_last_names,
               GROUP_CONCAT(c.age) as child_ages,
               GROUP_CONCAT(c.level) as child_levels
        FROM parents p
        LEFT JOIN children c ON p.id = c.parent_id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    $stmt->execute([$parent_id]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$parent) {
        header("Location: parents.php");
        exit();
    }

    // Parse children data
    $children = [];
    if ($parent['child_ids']) {
        $child_ids = explode(',', $parent['child_ids']);
        $child_first_names = explode(',', $parent['child_first_names']);
        $child_last_names = explode(',', $parent['child_last_names']);
        $child_ages = explode(',', $parent['child_ages']);
        $child_levels = explode(',', $parent['child_levels']);

        for ($i = 0; $i < count($child_ids); $i++) {
            $children[] = [
                'id' => $child_ids[$i],
                'first_name' => $child_first_names[$i],
                'last_name' => $child_last_names[$i],
                'age' => $child_ages[$i],
                'level' => $child_levels[$i]
            ];
        }
    }

} catch(PDOException $e) {
    $message = "Error fetching parent data: " . $e->getMessage();
    $messageType = 'error';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Update parent information
        $stmt = $pdo->prepare("
            UPDATE parents 
            SET first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                phone_secondary = ?,
                status = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['phone_secondary'],
            $_POST['status'],
            $parent_id
        ]);

        // Update children information
        foreach ($_POST['children'] as $index => $child) {
            if (isset($child['id']) && $child['id']) {
                // Update existing child
                $stmt = $pdo->prepare("
                    UPDATE children 
                    SET first_name = ?, 
                        last_name = ?, 
                        age = ?, 
                        level = ?
                    WHERE id = ? AND parent_id = ?
                ");
                $stmt->execute([
                    $child['first_name'],
                    $child['last_name'],
                    $child['age'],
                    $child['level'],
                    $child['id'],
                    $parent_id
                ]);
            } else {
                // Insert new child
                $stmt = $pdo->prepare("
                    INSERT INTO children 
                    (parent_id, first_name, last_name, age, level) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $parent_id,
                    $child['first_name'],
                    $child['last_name'],
                    $child['age'],
                    $child['level']
                ]);
            }
        }

        $pdo->commit();
        $message = "Parent information updated successfully.";
        $messageType = 'success';

        // Refresh parent data
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   GROUP_CONCAT(c.id) as child_ids,
                   GROUP_CONCAT(c.first_name) as child_first_names,
                   GROUP_CONCAT(c.last_name) as child_last_names,
                   GROUP_CONCAT(c.age) as child_ages,
                   GROUP_CONCAT(c.level) as child_levels
            FROM parents p
            LEFT JOIN children c ON p.id = c.parent_id
            WHERE p.id = ?
            GROUP BY p.id
        ");
        $stmt->execute([$parent_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        $pdo->rollBack();
        $message = "Error updating parent: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Parent - Administration</title>
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <style>
        .edit-form {
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .children-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .child-form {
            background: var(--light-bg);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .btn-add-child {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .btn-remove-child {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn-save {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel {
            background: var(--border-color);
            color: var(--text-color);
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <header class="admin-header">
            <h1>Modifier Parent</h1>
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
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="edit-form">
                <div class="form-group">
                    <label for="first_name">Prénom</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($parent['first_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Nom</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($parent['last_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($parent['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($parent['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_secondary">Téléphone secondaire</label>
                    <input type="tel" id="phone_secondary" name="phone_secondary" value="<?php echo htmlspecialchars($parent['phone_secondary']); ?>">
                </div>

                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" required>
                        <option value="pending" <?php echo $parent['status'] == 'pending' ? 'selected' : ''; ?>>En attente</option>
                        <option value="approved" <?php echo $parent['status'] == 'approved' ? 'selected' : ''; ?>>Approuvé</option>
                        <option value="rejected" <?php echo $parent['status'] == 'rejected' ? 'selected' : ''; ?>>Rejeté</option>
                        <option value="active" <?php echo $parent['status'] == 'active' ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactive" <?php echo $parent['status'] == 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                    </select>
                </div>

                <div class="children-section">
                    <h2>Enfants</h2>
                    <button type="button" class="btn-add-child" onclick="addChild()">Ajouter un enfant</button>

                    <div id="children-container">
                        <?php foreach ($children as $index => $child): ?>
                            <div class="child-form">
                                <input type="hidden" name="children[<?php echo $index; ?>][id]" value="<?php echo $child['id']; ?>">
                                
                                <div class="form-group">
                                    <label>Prénom</label>
                                    <input type="text" name="children[<?php echo $index; ?>][first_name]" value="<?php echo htmlspecialchars($child['first_name']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Nom</label>
                                    <input type="text" name="children[<?php echo $index; ?>][last_name]" value="<?php echo htmlspecialchars($child['last_name']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Âge</label>
                                    <select name="children[<?php echo $index; ?>][age]" required>
                                        <?php for($i = 3; $i <= 5; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $child['age'] == $i ? 'selected' : ''; ?>><?php echo $i; ?> ans</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Niveau</label>
                                    <select name="children[<?php echo $index; ?>][level]" required>
                                        <option value="3rd Grade" <?php echo $child['level'] == '3rd Grade' ? 'selected' : ''; ?>>3ème année</option>
                                        <option value="4th Grade" <?php echo $child['level'] == '4th Grade' ? 'selected' : ''; ?>>4ème année</option>
                                        <option value="5th Grade" <?php echo $child['level'] == '5th Grade' ? 'selected' : ''; ?>>5ème année</option>
                                    </select>
                                </div>

                                <button type="button" class="btn-remove-child" onclick="removeChild(this)">Supprimer</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Enregistrer</button>
                    <a href="parents.php" class="btn-cancel">Annuler</a>
                </div>
            </form>
        </main>
    </div>

    <script>
        let childIndex = <?php echo count($children); ?>;

        function addChild() {
            const container = document.getElementById('children-container');
            const childForm = document.createElement('div');
            childForm.className = 'child-form';
            childForm.innerHTML = `
                <input type="hidden" name="children[${childIndex}][id]" value="">
                
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="children[${childIndex}][first_name]" required>
                </div>

                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="children[${childIndex}][last_name]" required>
                </div>

                <div class="form-group">
                    <label>Âge</label>
                    <select name="children[${childIndex}][age]" required>
                        <option value="3">3 ans</option>
                        <option value="4">4 ans</option>
                        <option value="5">5 ans</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Niveau</label>
                    <select name="children[${childIndex}][level]" required>
                        <option value="3rd Grade">3ème année</option>
                        <option value="4th Grade">4ème année</option>
                        <option value="5th Grade">5ème année</option>
                    </select>
                </div>

                <button type="button" class="btn-remove-child" onclick="removeChild(this)">Supprimer</button>
            `;
            container.appendChild(childForm);
            childIndex++;
        }

        function removeChild(button) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet enfant ?')) {
                button.closest('.child-form').remove();
            }
        }
    </script>
</body>
</html> 