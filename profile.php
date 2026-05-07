<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user   = currentUser();
$userId = (int) $user['id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (empty($name)) {
        $errors[] = 'Le nom est obligatoire.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
    if ($password !== '' && $password !== $confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'Cet email est déjà utilisé par un autre compte.';
        }
    }

    if (!$errors) {
        if ($password !== '') {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $userId]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $stmt->execute([$name, $email, $userId]);
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $_SESSION['user'] = $stmt->fetch();

        setFlash('success', 'Profil mis à jour avec succès.');
        redirect('/projet/profile.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-2xl space-y-6">

    <!-- Avatar card -->
    <div class="card flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center font-bold text-white text-2xl shrink-0">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <div>
            <p class="eyebrow mb-0.5">Mon compte</p>
            <h2 class="text-xl font-bold text-slate-100"><?= e($user['name']) ?></h2>
            <p class="text-sm text-slate-400"><?= e($user['email']) ?> &mdash;
                <span class="<?= match($user['role']) {
                    'admin'   => 'text-rose-300',
                    'prof'    => 'text-violet-300',
                    default   => 'text-teal-300',
                } ?> font-medium capitalize"><?= e($user['role']) ?></span>
            </p>
        </div>
    </div>

    <!-- Edit form -->
    <div class="card">
        <p class="eyebrow mb-1">Informations</p>
        <h3 class="text-lg font-bold text-slate-100 mb-5">Modifier mon profil</h3>

        <?php if ($errors): ?>
            <ul class="px-4 py-3 rounded-xl border bg-rose-400/10 border-rose-400/30 text-rose-300 text-sm mb-5 space-y-1 list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="form-label" for="name">Nom complet</label>
                <input class="form-input" id="name" type="text" name="name"
                       value="<?= e($_POST['name'] ?? $user['name']) ?>" required>
            </div>
            <div>
                <label class="form-label" for="email">Adresse email</label>
                <input class="form-input" id="email" type="email" name="email"
                       value="<?= e($_POST['email'] ?? $user['email']) ?>" required>
            </div>

            <div class="border-t border-white/10 pt-5">
                <p class="text-sm font-medium text-slate-300 mb-4">Changer le mot de passe <span class="text-slate-500 font-normal">(laisser vide pour conserver l'actuel)</span></p>
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label" for="password">Nouveau mot de passe</label>
                        <input class="form-input" id="password" type="password" name="password"
                               placeholder="Minimum 6 caractères">
                    </div>
                    <div>
                        <label class="form-label" for="confirm">Confirmer le mot de passe</label>
                        <input class="form-input" id="confirm" type="password" name="confirm"
                               placeholder="Répétez le mot de passe">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit" class="btn">Enregistrer les modifications</button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
