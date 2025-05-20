<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['parent_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$pdo = getDBConnection();

// Get parent information
$stmt = $pdo->prepare("SELECT * FROM parents WHERE id = ?");
$stmt->execute([$_SESSION['parent_id']]);
$parent = $stmt->fetch(PDO::FETCH_ASSOC);

// Get children information
$stmt = $pdo->prepare("SELECT * FROM children WHERE parent_id = ?");
$stmt->execute([$_SESSION['parent_id']]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Parent - École Maternelle</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <h1>Tableau de Bord Parent 🌟</h1>
        <p>Bienvenue dans votre espace personnel</p>
    </header>

    <!-- Navigation -->
    <nav>
        <a href="homepage.html">← Retour à l'accueil</a>
        <div class="user-info">
            <span id="parentName"><?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></span>
            <?php if (!empty($children)): ?>
                <span id="childName"><?php echo htmlspecialchars($children[0]['first_name']); ?></span>
            <?php endif; ?>
        </div>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="dashboard-grid">
            <!-- Children Information -->
            <section class="dashboard-card">
                <h2>👶 Informations des enfants</h2>
                <?php foreach ($children as $child): ?>
                    <div class="child-info">
                        <h3><?php echo htmlspecialchars($child['first_name']); ?></h3>
                        <p>Âge: <?php echo htmlspecialchars($child['age']); ?> ans</p>
                        <p>Niveau: <?php echo htmlspecialchars($child['level']); ?></p>
                        <?php if ($child['allergies']): ?>
                            <p class="health-info">⚠️ Allergies: <?php echo htmlspecialchars($child['allergies_details']); ?></p>
                        <?php endif; ?>
                        <?php if ($child['disability']): ?>
                            <p class="health-info">⭐ Besoins particuliers: <?php echo htmlspecialchars($child['disability_details']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- Contact Information -->
            <section class="dashboard-card">
                <h2>📞 Informations de contact</h2>
                <p>Email: <?php echo htmlspecialchars($parent['email']); ?></p>
                <p>Téléphone: <?php echo htmlspecialchars($parent['phone']); ?></p>
                <?php if ($parent['phone_secondary']): ?>
                    <p>Téléphone secondaire: <?php echo htmlspecialchars($parent['phone_secondary']); ?></p>
                <?php endif; ?>
            </section>

            <!-- Quick Actions -->
            <section class="dashboard-card">
                <h2>⚡ Actions rapides</h2>
                <div class="quick-actions">
                    <a href="schedule.html" class="action-btn">📅 Voir l'emploi du temps</a>
                    <a href="calendar.html" class="action-btn">📆 Voir le calendrier</a>
                    <a href="contact.html" class="action-btn">📧 Contacter l'école</a>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
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