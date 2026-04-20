<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$totalCourses = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalEnrollments = (int) $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
?>
<section class="hero-grid">
    <div class="hero-panel">
        <span class="eyebrow">Plateforme e-learning</span>
        <h1 class="display">Gérez vos cours avec une interface moderne et claire.</h1>
        <p>
            LMS Nova centralise l'inscription, la connexion, la gestion des cours et le suivi des inscriptions
            dans une plateforme simple en PHP avec PDO.
        </p>
        <div class="actions">
            <?php if (!isLoggedIn()): ?>
                <a class="btn" href="/projet/register.php">Créer un compte</a>
                <a class="btn btn-secondary" href="/projet/login.php">Se connecter</a>
            <?php elseif (currentUser()['role'] === 'admin'): ?>
                <a class="btn" href="/projet/admin/courses.php">Ouvrir le dashboard admin</a>
            <?php else: ?>
                <a class="btn" href="/projet/student/dashboard.php">Ouvrir mon dashboard</a>
                <a class="btn btn-secondary" href="/projet/student/courses.php">Explorer les cours</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="kpi-list">
        <article class="stat-card">
            <span>Catalogue disponible</span>
            <strong><?= $totalCourses ?></strong>
            <span>Cours publiés sur la plateforme</span>
        </article>
        <article class="stat-card">
            <span>Apprenants</span>
            <strong><?= $totalStudents ?></strong>
            <span>Étudiants inscrits</span>
        </article>
        <article class="stat-card">
            <span>Inscriptions</span>
            <strong><?= $totalEnrollments ?></strong>
            <span>Inscriptions enregistrées</span>
        </article>
    </div>
</section>

<section class="grid grid-3" style="margin-top: 22px;">
    <article class="card">
        <span class="eyebrow">Admin</span>
        <h2>Gestion complète des cours</h2>
        <p>Ajoutez, modifiez et supprimez les cours depuis un tableau de bord moderne.</p>
    </article>
    <article class="card">
        <span class="eyebrow">Étudiant</span>
        <h2>Inscription en un clic</h2>
        <p>Explorez les cours disponibles et suivez directement vos inscriptions.</p>
    </article>
    <article class="card">
        <span class="eyebrow">Sécurité</span>
        <h2>Accès selon le rôle</h2>
        <p>Chaque espace est protégé avec session utilisateur et restrictions selon le rôle.</p>
    </article>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
