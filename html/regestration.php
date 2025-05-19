<?php
require_once '../config/database.php';

$message = '';
$messageType = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $parent_name = trim($_POST['parent_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $phone_secondary = trim($_POST['phone_secondary']);
    $child_name = trim($_POST['child_name']);
    $age = $_POST['age'];
    $level = $_POST['level'];
    $allergies = isset($_POST['allergies']) ? 1 : 0;
    $allergies_details = trim($_POST['allergies_details']);
    $disability = isset($_POST['disability']) ? 1 : 0;
    $disability_details = trim($_POST['disability_details']);
    $other_health_info = isset($_POST['other_health_info']) ? 1 : 0;
    $other_details = trim($_POST['other_details']);

    // Basic validation
    if (empty($parent_name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($child_name) || empty($age) || empty($level)) {
        $message = "Please fill in all required fields.";
        $messageType = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = 'error';
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $messageType = 'error';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM parents WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Email already registered.";
                $messageType = 'error';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new parent and child
                $pdo->beginTransaction();

                // Insert parent
                $stmt = $pdo->prepare("INSERT INTO parents (first_name, email, password, phone, phone_secondary) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$parent_name, $email, $hashed_password, $phone, $phone_secondary]);
                $parent_id = $pdo->lastInsertId();

                // Insert child
                $stmt = $pdo->prepare("INSERT INTO children (parent_id, name, age, level, allergies, allergies_details, disability, disability_details, other_health_info, other_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$parent_id, $child_name, $age, $level, $allergies, $allergies_details, $disability, $disability_details, $other_health_info, $other_details]);

                $pdo->commit();
                
                $message = "🎉 Inscription réussie ! Nous vous contacterons bientôt pour planifier une réunion. Redirection vers la page de connexion...";
                $messageType = 'success';
                
                // Redirect to login page after 3 seconds
                header("refresh:3;url=login.php");
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            $message = "Registration failed: " . $e->getMessage();
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
    <title>Inscription - École Maternelle</title>
    <link rel="stylesheet" href="../css/regestration.css">
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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <h1>Bienvenue à l'École des Petits Génies! 🌟</h1>
        <p>Nous avons hâte d'accueillir votre petit bout de chou ! 🎨</p>
    </header>

    <!-- Navbar -->
    <nav>
        <a href="homepage.html">← Retour à la page d'accueil</a>
    </nav>

    <!-- Main Content -->
    <main>
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="contact-form-container fade-in">
            <h2>Formulaire d'inscription</h2>
            <form id="registration-form" method="POST" action="">
                <label for="parent-name">👤 Nom complet du parent</label>
                <input type="text" id="parent-name" name="parent_name" placeholder="Entrez votre nom" required>

                <label for="email">📧 Email du parent</label>
                <input type="email" id="email" name="email" 
                    placeholder="Entrez votre email" 
                    pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                    title="Veuillez entrer une adresse email valide (exemple: nom@domaine.com)"
                    oninput="validateEmailOnInput(this)"
                    onblur="validateEmailOnBlur(this)"
                    required>
                <small class="input-hint">Format: nom@domaine.com</small>

                <label for="password">🔒 Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                <small class="input-hint">Le mot de passe doit contenir au moins 8 caractères</small>

                <label for="confirm_password">🔒 Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>

                <label for="phone">📞 Numéro de téléphone (8 chiffres requis)</label>
                <input type="tel" id="phone" name="phone" placeholder="Exemple: 12345678" pattern="[0-9]{8}" maxlength="8" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <small class="input-hint">Entrez exactement 8 chiffres</small>

                <label for="phone-secondary">📱 Numéro de téléphone secondaire</label>
                <input type="tel" id="phone-secondary" name="phone_secondary" placeholder="Exemple: 12345678" pattern="[0-9]{8}" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <small class="input-hint">Optionnel - 8 chiffres si renseigné</small>

                <label for="child-name">👶 Nom complet de l'enfant</label>
                <input type="text" id="child-name" name="child_name" placeholder="Entrez le nom de votre enfant" required>

                <label for="age">🎂 Âge de l'enfant</label>
                <select id="age" name="age" required onchange="updateLevelOptions()">
                    <option value="" disabled selected>Sélectionnez l'âge</option>
                    <option value="3">3 ans</option>
                    <option value="4">4 ans</option>
                    <option value="5">5 ans</option>
                </select>

                <label for="level">📚 Niveau d'éducation</label>
                <select id="level" name="level" required>
                    <option value="" disabled selected>Sélectionnez le niveau</option>
                    <option value="3rd Grade">3ème année</option>
                    <option value="4th Grade">4ème année</option>
                    <option value="5th Grade">5ème année</option>
                </select>

            </form>
        </div>

        <div class="contact-form-container fade-in">
            <h2>🏥 Informations de santé</h2>
            <form id="health-form" method="POST" action="">
                <label for="allergies">
                    🌿 Votre enfant a-t-il des allergies ?
                    <input type="checkbox" id="allergies" name="allergies" onchange="toggleTextarea('allergies-details')">
                </label>
                <textarea id="allergies-details" name="allergies_details" placeholder="Décrivez les allergies de votre enfant" style="display: none;"></textarea>

                <label for="disability">
                    ⭐ Votre enfant a-t-il des besoins particuliers ?
                    <input type="checkbox" id="disability" name="disability" onchange="toggleTextarea('disability-details')">
                </label>
                <textarea id="disability-details" name="disability_details" placeholder="Décrivez les besoins particuliers" style="display: none;"></textarea>

                <label for="other-health-info">
                    ℹ️ Autres informations de santé ?
                    <input type="checkbox" id="other-health-info" name="other_health_info" onchange="toggleTextarea('other-details')">
                </label>
                <textarea id="other-details" name="other_details" placeholder="Autres informations importantes" style="display: none;"></textarea>

                <div class="terms">
                    <label for="terms">
                        📜 J'accepte les <a href="terms-and-conditions.html" target="_blank">termes et conditions</a>
                    </label>
                    <input type="checkbox" id="terms" name="terms" required>
                </div>

                <button type="submit" class="submit-btn">S'inscrire 🎉</button>
            </form>
        </div>

        <div class="notice fade-in">
            <h2>📢 Informations importantes</h2>
            <p>
                En cliquant sur "S'inscrire", vous reconnaissez que les informations fournies seront transmises en toute sécurité. 
                Nous apprécions votre confiance et assurons la confidentialité de tous les détails partagés. 
                Après votre soumission, nous vous contacterons par email et téléphone pour planifier une réunion. 
                Veuillez nous informer rapidement si vous prévoyez d'être en retard ou absent. Lors de cette réunion, 
                un contrat définissant la coopération entre notre institution et les parents sera signé. 
                Notre priorité absolue est la sécurité, la santé et le bien-être de chaque enfant sous notre garde.
            </p>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>🏫 École des Petits Génies</p>
        <p>🌟 Où chaque enfant est une étoile qui brille</p>
        <p>📞 Contactez-nous : support@petitsgenies.fr</p>
        <p>© 2024 Tous droits réservés</p>
    </footer>

    <script>
        function validateEmailOnInput(input) {
            // Convert to lowercase
            input.value = input.value.toLowerCase();
            
            // Basic validation while typing
            const email = input.value;
            const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
            
            if (!emailRegex.test(email)) {
                input.setCustomValidity('Format email invalide');
            } else {
                validateEmailOnBlur(input);
            }
        }

        function validateEmailOnBlur(input) {
            const email = input.value;
            const validDomains = ['.com', '.fr', '.org', '.net', '.edu', '.gov'];
            const hasValidDomain = validDomains.some(domain => email.endsWith(domain));
            
            if (!hasValidDomain) {
                input.setCustomValidity('Veuillez entrer une adresse email avec un domaine valide (.com, .fr, .org, etc.)');
            } else {
                input.setCustomValidity('');
            }
        }

        function updateLevelOptions() {
            const ageSelect = document.getElementById('age');
            const levelSelect = document.getElementById('level');
            const selectedAge = ageSelect.value;
            
            // Get all level options
            const options = levelSelect.options;
            
            // Reset all options to enabled
            for(let i = 0; i < options.length; i++) {
                options[i].disabled = false;
            }
            
            // Disable inappropriate options based on age
            if (selectedAge === "3") {
                options[3].disabled = true; // Disable 5th Grade
            } else if (selectedAge === "4") {
                options[1].disabled = true; // Disable 3rd Grade
            } else if (selectedAge === "5") {
                options[1].disabled = true; // Disable 3rd Grade
                options[2].disabled = true; // Disable 4th Grade
            }
        }

        function toggleTextarea(textareaId) {
            const textarea = document.getElementById(textareaId);
            let checkboxId;
            
            // Handle the special case for 'other-details'
            if (textareaId === 'other-details') {
                checkboxId = 'other-health-info';
            } else {
                checkboxId = textareaId.split('-')[0];
            }
            
            const checkbox = document.getElementById(checkboxId);
            textarea.style.display = checkbox.checked ? 'block' : 'none';
        }
    </script>
</body>
</html>