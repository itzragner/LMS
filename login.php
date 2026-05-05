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

<section class="min-h-[calc(100vh-80px)] flex items-center">
    <div class="w-full grid lg:grid-cols-2 gap-8 items-center max-w-5xl mx-auto">

        <!-- Left panel -->
        <div class="rounded-2xl p-10 border border-white/10 relative overflow-hidden hidden lg:block"
             style="background: linear-gradient(135deg, rgba(15,25,55,0.9) 0%, rgba(30,58,95,0.5) 100%); backdrop-filter: blur(18px); min-height: 480px;">
            <div class="absolute inset-0 opacity-20"
                 style="background: radial-gradient(circle at 30% 70%, #8b5cf6 0%, transparent 60%);"></div>
            <div class="relative flex flex-col h-full">
                <p class="eyebrow mb-4">Connexion</p>
                <h1 class="text-3xl font-extrabold text-slate-100 leading-tight mb-4">
                    Reprenez votre apprentissage.
                </h1>
                <p class="text-slate-400 mb-8">
                    Accédez à votre espace étudiant ou administrateur avec une interface moderne et rapide.
                </p>
                <div class="flex flex-wrap gap-2 mt-auto">
                    <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-300/10 text-teal-300 border border-teal-300/20">Suivi des cours inscrits</span>
                    <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-300/10 text-teal-300 border border-teal-300/20">Gestion admin centralisée</span>
                    <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-teal-300/10 text-teal-300 border border-teal-300/20">Sessions sécurisées par rôle</span>
                </div>
            </div>
        </div>

        <!-- Form card -->
        <div class="card max-w-md w-full mx-auto">
            <p class="eyebrow mb-1">Bienvenue</p>
            <h2 class="text-2xl font-bold text-slate-100 mb-6">Se connecter</h2>

            <?php if ($error): ?>
                <div class="px-4 py-3 rounded-xl border bg-rose-400/10 border-rose-400/30 text-rose-300 text-sm mb-5">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-input" id="email" type="email" name="email" placeholder="votre@email.com" required>
                </div>
                <div>
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="form-input" id="password" type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn w-full justify-center mt-2">Se connecter</button>
            </form>

            <p class="text-sm text-slate-400 mt-5 text-center">
                Pas encore de compte ?
                <a class="text-teal-300 hover:text-teal-200 font-medium transition-colors" href="/projet/register.php">Créer un compte</a>
            </p>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
