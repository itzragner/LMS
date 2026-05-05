<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$totalCourses = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalEnrollments = (int) $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
?>

<!-- Hero -->
<section class="grid lg:grid-cols-[1fr_360px] gap-6 items-start">

    <div class="rounded-2xl p-10 border border-white/10 relative overflow-hidden"
         style="background: linear-gradient(135deg, rgba(15,25,55,0.9) 0%, rgba(30,58,95,0.6) 100%); backdrop-filter: blur(18px);">
        <div class="absolute inset-0 opacity-20"
             style="background: radial-gradient(circle at 70% 50%, #5eead4 0%, transparent 60%);"></div>
        <div class="relative">
            <p class="eyebrow mb-3">Plateforme e-learning</p>
            <h1 class="text-4xl lg:text-5xl font-extrabold text-slate-100 leading-tight mb-4">
                Gérez vos cours avec une interface moderne et claire.
            </h1>
            <p class="text-slate-400 text-lg mb-8 max-w-xl">
                LMS centralise l'inscription, la connexion, la gestion des cours et le suivi des inscriptions
                dans une plateforme simple en PHP avec PDO.
            </p>
            <div class="flex flex-wrap gap-3">
                <?php if (!isLoggedIn()): ?>
                    <a class="btn" href="/projet/register.php">Créer un compte</a>
                    <a class="btn-secondary" href="/projet/login.php">Se connecter</a>
                <?php elseif (currentUser()['role'] === 'admin'): ?>
                    <a class="btn" href="/projet/admin/courses.php">Ouvrir le dashboard admin</a>
                <?php else: ?>
                    <a class="btn" href="/projet/student/dashboard.php">Ouvrir mon dashboard</a>
                    <a class="btn-secondary" href="/projet/student/courses.php">Explorer les cours</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        <article class="metric-box">
            <p class="eyebrow mb-1">Catalogue disponible</p>
            <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalCourses ?></p>
            <p class="text-sm text-slate-400">Cours publiés sur la plateforme</p>
        </article>
        <article class="metric-box">
            <p class="eyebrow mb-1">Apprenants</p>
            <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalStudents ?></p>
            <p class="text-sm text-slate-400">Étudiants inscrits</p>
        </article>
        <article class="metric-box">
            <p class="eyebrow mb-1">Inscriptions</p>
            <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalEnrollments ?></p>
            <p class="text-sm text-slate-400">Inscriptions enregistrées</p>
        </article>
    </div>
</section>

<!-- Feature cards -->
<section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <article class="card">
        <p class="eyebrow mb-2">Admin</p>
        <h2 class="text-lg font-bold text-slate-100 mb-2">Gestion complète des cours</h2>
        <p class="text-slate-400 text-sm">Ajoutez, modifiez et supprimez les cours depuis un tableau de bord moderne.</p>
    </article>
    <article class="card">
        <p class="eyebrow mb-2">Étudiant</p>
        <h2 class="text-lg font-bold text-slate-100 mb-2">Inscription en un clic</h2>
        <p class="text-slate-400 text-sm">Explorez les cours disponibles et suivez directement vos inscriptions.</p>
    </article>
    <article class="card">
        <p class="eyebrow mb-2">Sécurité</p>
        <h2 class="text-lg font-bold text-slate-100 mb-2">Accès selon le rôle</h2>
        <p class="text-slate-400 text-sm">Chaque espace est protégé avec session utilisateur et restrictions selon le rôle.</p>
    </article>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>