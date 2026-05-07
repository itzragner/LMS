<?php
require_once 'functions.php';
$user = currentUser();
$flash = getFlash();
$currentPath = $_SERVER['PHP_SELF'] ?? '';

function navActive(string $needle, string $currentPath): string
{
    return str_contains($currentPath, $needle) ? 'active' : '';
}

function flashClass(string $type): string
{
    return match($type) {
        'success' => 'bg-green-400/10 border-green-400/30 text-green-300',
        'danger'  => 'bg-rose-400/10 border-rose-400/30 text-rose-300',
        'warning' => 'bg-amber-400/10 border-amber-400/30 text-amber-300',
        default   => 'bg-sky-400/10 border-sky-400/30 text-sky-300',
    };
}

$isAuthArea = $user && in_array($user['role'], ['admin', 'prof', 'student'], true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: { extend: { fontFamily: { sans: ['Inter', 'Arial', 'sans-serif'] } } }
    }
    </script>
    <style type="text/tailwindcss">
        <?php echo file_get_contents(__DIR__ . '/../assets/css/tailwind.css'); ?>
    </style>
</head>
<body class="min-h-screen font-sans antialiased text-slate-100"
      style="background: radial-gradient(ellipse 140% 60% at 50% -10%, #1e3a5f 0%, #02080f 55%); background-color: #02080f;">

<?php if ($isAuthArea): ?>
<div class="flex min-h-screen">


    <aside class="w-72 shrink-0 sticky top-0 h-screen flex flex-col bg-slate-950/80 backdrop-blur-xl border-r border-white/10 overflow-y-auto">

        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-300 to-cyan-400 flex items-center justify-center font-bold text-slate-900 text-base shrink-0">N</div>
            <div>
                <p class="eyebrow">Learning Platform</p>
                <p class="font-bold text-slate-100 text-sm">LMS</p>
            </div>
        </div>

        <!-- User card -->
        <div class="flex items-center gap-3 mx-3 my-3 px-3 py-3 rounded-xl bg-white/5 border border-white/10">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center font-bold text-white text-sm shrink-0">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-slate-100 text-sm truncate"><?= e($user['name']) ?></p>
                <p class="text-xs text-slate-400 truncate"><?= e($user['email']) ?></p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 flex flex-col gap-0.5 pb-4">
            <?php if ($user['role'] === 'admin'): ?>
                <a class="nav-link <?= navActive('/admin/courses.php', $currentPath) ?>" href="/projet/admin/courses.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Cours
                </a>
                <a class="nav-link <?= navActive('/admin/users.php', $currentPath) ?>" href="/projet/admin/users.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Utilisateurs
                </a>
                <a class="nav-link <?= navActive('/admin/add_course.php', $currentPath) ?>" href="/projet/admin/add_course.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ajouter un cours
                </a>
            <?php endif; ?>

            <?php if ($user['role'] === 'prof'): ?>
                <a class="nav-link <?= navActive('/admin/courses.php', $currentPath) ?>" href="/projet/admin/courses.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Mes cours
                </a>
                <a class="nav-link <?= navActive('/admin/add_course.php', $currentPath) ?>" href="/projet/admin/add_course.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ajouter un cours
                </a>
            <?php endif; ?>

            <?php if ($user['role'] === 'student'): ?>
                <a class="nav-link <?= navActive('/student/dashboard.php', $currentPath) ?>" href="/projet/student/dashboard.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Mon dashboard
                </a>
                <a class="nav-link <?= navActive('/student/courses.php', $currentPath) ?>" href="/projet/student/courses.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Explorer les cours
                </a>
            <?php endif; ?>

            <div class="mt-auto pt-3 border-t border-white/10">
                <a class="nav-link" href="/projet/index.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour accueil
                </a>
                <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-rose-400 hover:text-rose-300 hover:bg-rose-400/10 text-sm font-medium transition-all duration-200" href="/projet/logout.php">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Déconnexion
                </a>
            </div>
        </nav>
    </aside>

    <!-- Dashboard main -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar -->
        <header class="sticky top-0 z-10 flex items-center justify-between px-8 py-4 bg-slate-950/70 backdrop-blur-md border-b border-white/10">
            <div>
                <p class="eyebrow">Espace sécurisé</p>
                <h1 class="text-xl font-bold text-slate-100">
                    <?= match($user['role']) {
                        'admin' => 'Dashboard administrateur',
                        'prof'  => 'Dashboard professeur',
                        default => 'Dashboard étudiant',
                    } ?>
                </h1>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-widest bg-teal-300/10 text-teal-300 border border-teal-300/20">
                <?= e($user['role']) ?>
            </span>
        </header>

        <main class="flex-1 p-8 space-y-6">
            <?php if ($flash): ?>
                <div class="px-5 py-3.5 rounded-xl border text-sm font-medium <?= flashClass($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

<?php else: ?>

    <!-- Public header -->
    <header class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-xl border-b border-white/10">
        <div class="max-w-[1180px] mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/projet/index.php" class="flex items-center gap-2.5 no-underline">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-300 to-cyan-400 flex items-center justify-center font-bold text-slate-900 text-sm">N</div>
                <span class="font-bold text-lg text-slate-100">LMS</span>
            </a>
            <nav class="flex items-center gap-1">
                <a class="px-4 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-teal-300 hover:bg-white/[0.07] transition-all" href="/projet/index.php">Accueil</a>
                <a class="px-4 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-teal-300 hover:bg-white/[0.07] transition-all" href="/projet/login.php">Connexion</a>
                <a class="btn btn-sm ml-2" href="/projet/register.php">Créer un compte</a>
            </nav>
        </div>
    </header>

    <main class="max-w-[1180px] mx-auto px-6 py-10 space-y-8">
        <?php if ($flash): ?>
            <div class="px-5 py-3.5 rounded-xl border text-sm font-medium <?= flashClass($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
<?php endif; ?>