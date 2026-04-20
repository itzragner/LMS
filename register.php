<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/validation.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    $errors = validateRegistration(compact('name', 'email', 'password'));

    if (!in_array($role, ['student', 'admin'], true)) {
        $errors[] = 'Rôle invalide.';
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Cet email existe déjà.';
    }

    if (!$errors) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $hashedPassword, $role]);
        setFlash('success', 'Compte créé avec succès. Vous pouvez maintenant vous connecter.');
        redirect('/projet/login.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-shell">
    <div class="glass-card auth-side">
        <div>
            <span class="eyebrow">Inscription</span>
            <h1 class="display" style="font-size: clamp(1.9rem, 3vw, 3rem);">Créez votre espace d'apprentissage.</h1>
            <p>Inscrivez-vous comme étudiant ou administrateur pour accéder à la plateforme LMS Nova.</p>
        </div>

        <div class="auth-feature-list">
            <div class="feature-pill">Catalogue de cours organisé</div>
            <div class="feature-pill">Suivi d'inscription instantané</div>
            <div class="feature-pill">Espace admin et étudiant séparés</div>
        </div>
    </div>

    <div class="card auth-card">
        <div class="card-header">
            <div>
                <span class="eyebrow">Nouveau compte</span>
                <h2>S'inscrire</h2>
            </div>
        </div>

        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label for="name">Nom complet</label>
                    <input id="name" type="text" name="name" placeholder="Votre nom" value="<?= e($_POST['name'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="role">Rôle</label>
                    <select id="role" name="role" required>
                        <option value="student" <?= (($_POST['role'] ?? '') === 'student') ? 'selected' : '' ?>>Étudiant</option>
                        <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrateur</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="votre@email.com" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div>
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password" placeholder="Minimum 6 caractères" required>
            </div>
            <button type="submit">Créer mon compte</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
