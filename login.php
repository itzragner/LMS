<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        setFlash('success', 'Connexion réussie.');

        if ($user['role'] === 'admin') {
            redirect('/projet/admin/courses.php');
        }

        redirect('/projet/student/dashboard.php');
    }

    $error = 'Email ou mot de passe incorrect.';
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-shell">
    <div class="glass-card auth-side">
        <div>
            <span class="eyebrow">Connexion</span>
            <h1 class="display" style="font-size: clamp(1.9rem, 3vw, 3rem);">Reprenez votre apprentissage.</h1>
            <p>Accédez à votre espace étudiant ou administrateur avec une interface moderne et rapide.</p>
        </div>

        <div class="auth-feature-list">
            <div class="feature-pill">Suivi des cours inscrits</div>
            <div class="feature-pill">Gestion admin centralisée</div>
            <div class="feature-pill">Sessions sécurisées par rôle</div>
        </div>
    </div>

    <div class="card auth-card">
        <div class="card-header">
            <div>
                <span class="eyebrow">Bienvenue</span>
                <h2>Se connecter</h2>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" placeholder="votre@email.com" required>
            </div>
            <div>
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>

        <p style="margin-top: 18px;">Pas encore de compte ? <a style="color: var(--primary);" href="/projet/register.php">Créer un compte</a></p>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
