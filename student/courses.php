<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$userId = currentUser()['id'];
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare('
        SELECT c.*, EXISTS(SELECT 1 FROM enrollments e WHERE e.course_id = c.id AND e.user_id = ?) AS enrolled
        FROM courses c
        WHERE c.title LIKE ? OR c.description LIKE ?
        ORDER BY c.created_at DESC
    ');
    $like = '%' . $search . '%';
    $stmt->execute([$userId, $like, $like]);
} else {
    $stmt = $pdo->prepare('
        SELECT c.*, EXISTS(SELECT 1 FROM enrollments e WHERE e.course_id = c.id AND e.user_id = ?) AS enrolled
        FROM courses c
        ORDER BY c.created_at DESC
    ');
    $stmt->execute([$userId]);
}

$courses    = $stmt->fetchAll();
$totalAll   = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Catalogue</p>
            <h2 class="text-xl font-bold text-slate-100">Cours disponibles</h2>
            <p class="text-sm text-slate-400 mt-1">Explorez les cours et inscrivez-vous en un clic.</p>
        </div>
        <a class="btn-secondary shrink-0" href="/projet/student/dashboard.php">Retour dashboard</a>
    </div>

    <!-- Search bar -->
    <form method="GET" class="mb-6">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input class="form-input pl-11 pr-10"
                   type="text" name="q"
                   value="<?= e($search) ?>"
                   placeholder="Rechercher un cours par titre ou description…"
                   autofocus>
            <?php if ($search !== ''): ?>
                <a href="/projet/student/courses.php"
                   class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                   title="Effacer la recherche">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Results count -->
    <?php if ($search !== ''): ?>
        <p class="text-sm text-slate-400 mb-4">
            <?= count($courses) ?> résultat<?= count($courses) !== 1 ? 's' : '' ?> pour
            <span class="text-slate-200 font-medium">"<?= e($search) ?>"</span>
            <span class="text-slate-600"> — <?= $totalAll ?> cours au total</span>
        </p>
    <?php endif; ?>

    <?php if (!$courses): ?>
        <div class="text-center py-12">
            <?php if ($search !== ''): ?>
                <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <p class="text-lg font-semibold text-slate-300 mb-1">Aucun cours trouvé</p>
                <p class="text-sm text-slate-500 mb-4">Aucun cours ne correspond à "<span class="text-slate-400"><?= e($search) ?></span>".</p>
                <a href="/projet/student/courses.php" class="btn-secondary btn-sm">Voir tous les cours</a>
            <?php else: ?>
                <p class="text-lg font-semibold text-slate-300 mb-1">Aucun cours disponible</p>
                <p class="text-sm text-slate-500">L'administrateur n'a pas encore ajouté de cours.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 gap-4">
            <?php foreach ($courses as $course): ?>
                <?php $enrolled = (int) $course['enrolled'] === 1; ?>
                <article class="flex flex-col gap-3 p-5 rounded-xl border border-white/[0.08] bg-white/[0.03] hover:bg-white/[0.05] transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="eyebrow mb-0.5">Cours</p>
                            <h3 class="font-bold text-slate-200"><?= e($course['title']) ?></h3>
                        </div>
                        <?php if ($enrolled): ?>
                            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-violet-400/10 text-violet-300 border border-violet-400/20">Déjà inscrit</span>
                        <?php else: ?>
                            <span class="tag shrink-0">Disponible</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-400 flex-1"><?= e($course['description']) ?></p>
                    <div class="flex items-center gap-2">
                        <?php if ($enrolled): ?>
                            <a class="btn btn-sm" href="/projet/student/course.php?id=<?= (int) $course['id'] ?>">Voir les leçons</a>
                            <a class="btn-danger btn-sm" href="/projet/student/unenroll.php?id=<?= (int) $course['id'] ?>">Se désinscrire</a>
                        <?php else: ?>
                            <a class="btn btn-sm" href="/projet/student/enroll.php?id=<?= (int) $course['id'] ?>">S'inscrire</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
