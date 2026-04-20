<?php
require_once __DIR__ . '/functions.php';
$user = currentUser();
$flash = getFlash();
$currentPath = $_SERVER['PHP_SELF'] ?? '';

function navActive(string $needle, string $currentPath): string
{
    return str_contains($currentPath, $needle) ? 'active' : '';
}

$isAuthArea = $user && ($user['role'] === 'admin' || $user['role'] === 'student');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Nova</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/projet/assets/css/style.css">
</head>
<body>
<?php if ($isAuthArea): ?>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-mark">N</div>
                <div>
                    <span class="eyebrow">Learning Platform</span>
                    <strong>LMS Nova</strong>
                </div>
            </div>

            <div class="sidebar-user-card">
                <span class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                <div>
                    <strong><?= e($user['name']) ?></strong>
                    <p><?= e($user['email']) ?></p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <?php if ($user['role'] === 'admin'): ?>
                    <a class="<?= navActive('/admin/courses.php', $currentPath) ?>" href="/projet/admin/courses.php">Tableau de bord admin</a>
                    <a class="<?= navActive('/admin/add_course.php', $currentPath) ?>" href="/projet/admin/add_course.php">Ajouter un cours</a>
                <?php endif; ?>

                <?php if ($user['role'] === 'student'): ?>
                    <a class="<?= navActive('/student/dashboard.php', $currentPath) ?>" href="/projet/student/dashboard.php">Mon dashboard</a>
                    <a class="<?= navActive('/student/courses.php', $currentPath) ?>" href="/projet/student/courses.php">Explorer les cours</a>
                <?php endif; ?>

                <a href="/projet/index.php">Retour accueil</a>
                <a href="/projet/logout.php">Déconnexion</a>
            </nav>
        </aside>

        <div class="dashboard-main">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Espace sécurisé</span>
                    <h1><?= $user['role'] === 'admin' ? 'Dashboard administrateur' : 'Dashboard étudiant' ?></h1>
                </div>
                <div class="topbar-actions">
                    <span class="role-pill"><?= e($user['role']) ?></span>
                </div>
            </header>

            <main class="dashboard-content">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endif; ?>
<?php else: ?>
    <header class="public-header">
        <div class="container public-nav">
            <a href="/projet/index.php" class="brand brand-public">
                <span class="brand-mark">N</span>
                <span>LMS Nova</span>
            </a>
            <nav class="public-links">
                <a href="/projet/index.php">Accueil</a>
                <a href="/projet/login.php">Connexion</a>
                <a class="btn btn-sm" href="/projet/register.php">Créer un compte</a>
            </nav>
        </div>
    </header>
    <main class="container public-main">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
<?php endif; ?>
