<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
requireRole('admin');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    $errors = validateRegistration(compact('name', 'email', 'password'));

    if (!in_array($role, ['admin', 'prof', 'student'], true)) {
        $errors[] = 'Rôle invalide.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        setFlash('success', 'Utilisateur créé avec succès.');
        redirect('/projet/admin/users.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card max-w-2xl">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Nouvel utilisateur</p>
            <h2 class="text-xl font-bold text-slate-100">Ajouter un utilisateur</h2>
            <p class="text-sm text-slate-400 mt-1">Créez un nouveau compte sur la plateforme.</p>
        </div>
        <a class="btn-secondary shrink-0" href="/projet/admin/users.php">Retour</a>
    </div>

    <?php if ($errors): ?>
        <ul class="px-4 py-3 rounded-xl border bg-rose-400/10 border-rose-400/30 text-rose-300 text-sm mb-5 space-y-1 list-disc list-inside">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="form-label" for="name">Nom complet</label>
                <input class="form-input" id="name" type="text" name="name"
                       placeholder="Ex : Marie Dupont"
                       value="<?= e($_POST['name'] ?? '') ?>" required>
            </div>
            <div>
                <label class="form-label" for="role">Rôle</label>
                <select class="form-input" id="role" name="role" required>
                    <option value="student" <?= (($_POST['role'] ?? 'student') === 'student') ? 'selected' : '' ?>>Étudiant</option>
                    <option value="prof"    <?= (($_POST['role'] ?? '') === 'prof')    ? 'selected' : '' ?>>Professeur</option>
                    <option value="admin"   <?= (($_POST['role'] ?? '') === 'admin')   ? 'selected' : '' ?>>Administrateur</option>
                </select>
            </div>
        </div>
        <div>
            <label class="form-label" for="email">Email</label>
            <input class="form-input" id="email" type="email" name="email"
                   placeholder="utilisateur@email.com"
                   value="<?= e($_POST['email'] ?? '') ?>" required>
        </div>
        <div>
            <label class="form-label" for="password">Mot de passe</label>
            <input class="form-input" id="password" type="password" name="password"
                   placeholder="Minimum 6 caractères" required>
        </div>
        <div class="flex gap-3 pt-1">
            <button type="submit" class="btn">Créer l'utilisateur</button>
            <a class="btn-secondary" href="/projet/admin/users.php">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
