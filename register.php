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

    if (!in_array($role, ['student', 'admin', 'prof'], true)) {
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

<section class="min-h-[calc(100vh-80px)] flex items-center">
    <div class="w-full grid lg:grid-cols-2 gap-8 items-center max-w-5xl mx-auto">

        <!-- Left panel -->
        <div class="rounded-2xl p-10 border border-white/10 relative overflow-hidden hidden lg:block"
             style="background: linear-gradient(135deg, rgba(15,25,55,0.9) 0%, rgba(30,58,95,0.5) 100%); backdrop-filter: blur(18px); min-height: 520px;">
            <div class="absolute inset-0 opacity-20"
                 style="background: radial-gradient(circle at 70% 30%, #5eead4 0%, transparent 60%);"></div>
            <div class="relative flex flex-col h-full">
                <p class="eyebrow mb-4">Inscription</p>
                <h1 class="text-3xl font-extrabold text-slate-100 leading-tight mb-4">
                    Créez votre espace d'apprentissage.
                </h1>
                <p class="text-slate-400 mb-8">
                    Inscrivez-vous comme étudiant ou administrateur pour accéder à la plateforme LMS.
                </p>
                <div class="flex flex-wrap gap-2 mt-auto">
                    <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-300/10 text-teal-300 border border-teal-300/20">Catalogue de cours organisé</span>
                    <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-300/10 text-teal-300 border border-teal-300/20">Suivi d'inscription instantané</span>
                    <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-300/10 text-teal-300 border border-teal-300/20">Espace admin et étudiant séparés</span>
                </div>
            </div>
        </div>

        <!-- Form card -->
        <div class="card max-w-md w-full mx-auto">
            <p class="eyebrow mb-1">Nouveau compte</p>
            <h2 class="text-2xl font-bold text-slate-100 mb-6">S'inscrire</h2>

            <?php if ($errors): ?>
                <ul class="px-4 py-3 rounded-xl border bg-rose-400/10 border-rose-400/30 text-rose-300 text-sm mb-5 space-y-1 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="name">Nom complet</label>
                        <input class="form-input" id="name" type="text" name="name" placeholder="Votre nom"
                               value="<?= e($_POST['name'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="form-label" for="role">Rôle</label>
                        <select class="form-input" id="role" name="role" required>
                            <option value="student" <?= (($_POST['role'] ?? '') === 'student') ? 'selected' : '' ?>>Étudiant</option>
                            <option value="prof" <?= (($_POST['role'] ?? '') === 'prof') ? 'selected' : '' ?>>Professeur</option>
                            <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrateur</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-input" id="email" type="email" name="email" placeholder="votre@email.com"
                           value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="form-input" id="password" type="password" name="password" placeholder="Minimum 6 caractères" required>
                </div>
                <button type="submit" class="btn w-full justify-center mt-2">Créer mon compte</button>
            </form>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>