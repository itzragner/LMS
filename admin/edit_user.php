<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$target = $stmt->fetch();

if (!$target) {
    setFlash('danger', 'Utilisateur introuvable.');
    redirect('/projet/admin/users.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    if (empty($name)) {
        $errors[] = 'Le nom est obligatoire.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    }
    if (!in_array($role, ['admin', 'prof', 'student'], true)) {
        $errors[] = 'Rôle invalide.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $errors[] = 'Cet email est déjà utilisé par un autre compte.';
        }
    }

    if (!$errors) {
        if ($password !== '') {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?');
            $stmt->execute([$name, $email, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
            $stmt->execute([$name, $email, $role, $id]);
        }
        setFlash('success', 'Utilisateur modifié avec succès.');
        redirect('/projet/admin/users.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card max-w-2xl">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Édition</p>
            <h2 class="text-xl font-bold text-slate-100">Modifier l'utilisateur</h2>
            <p class="text-sm text-slate-400 mt-1">Laissez le mot de passe vide pour le conserver.</p>
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
                       value="<?= e($_POST['name'] ?? $target['name']) ?>" required>
            </div>
            <div>
                <label class="form-label" for="role">Rôle</label>
                <select class="form-input" id="role" name="role" required>
                    <option value="student" <?= (($_POST['role'] ?? $target['role']) === 'student') ? 'selected' : '' ?>>Étudiant</option>
                    <option value="prof"    <?= (($_POST['role'] ?? $target['role']) === 'prof')    ? 'selected' : '' ?>>Professeur</option>
                    <option value="admin"   <?= (($_POST['role'] ?? $target['role']) === 'admin')   ? 'selected' : '' ?>>Administrateur</option>
                </select>
            </div>
        </div>
        <div>
            <label class="form-label" for="email">Email</label>
            <input class="form-input" id="email" type="email" name="email"
                   value="<?= e($_POST['email'] ?? $target['email']) ?>" required>
        </div>
        <div>
            <label class="form-label" for="password">Nouveau mot de passe <span class="text-slate-500 font-normal">(optionnel)</span></label>
            <input class="form-input" id="password" type="password" name="password"
                   placeholder="Laisser vide pour ne pas changer">
        </div>
        <div class="flex gap-3 pt-1">
            <button type="submit" class="btn">Enregistrer les modifications</button>
            <a class="btn-secondary" href="/projet/admin/users.php">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
