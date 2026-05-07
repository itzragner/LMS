<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$userId    = currentUser()['id'];
$search    = trim($_GET['q'] ?? '');
$catFilter = (int) ($_GET['cat'] ?? 0);
$like      = "%$search%";

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

$where  = ['1=1'];
$params = [$userId];

if ($search !== '') {
    $where[]  = '(c.title LIKE ? OR c.description LIKE ?)';
    $params[] = $like;
    $params[] = $like;
}
if ($catFilter > 0) {
    $where[]  = 'c.category_id = ?';
    $params[] = $catFilter;
}

$whereSQL = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT c.*,
           EXISTS(SELECT 1 FROM enrollments e WHERE e.course_id = c.id AND e.user_id = ?) AS enrolled,
           cat.name AS category_name,
           ROUND(AVG(r.rating), 1) AS avg_rating,
           COUNT(DISTINCT r.id) AS rating_count
    FROM courses c
    LEFT JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN ratings r ON r.course_id = c.id
    WHERE $whereSQL
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute($params);
$courses  = $stmt->fetchAll();
$totalAll = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();

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
    <form method="GET" class="mb-4">
        <?php if ($catFilter > 0): ?>
            <input type="hidden" name="cat" value="<?= $catFilter ?>">
        <?php endif; ?>
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input class="form-input pl-11 pr-10" type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher un cours…" autofocus>
            <?php if ($search !== ''): ?>
                <a href="?<?= $catFilter > 0 ? "cat=$catFilter" : '' ?>"
                   class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Category filters -->
    <?php if ($categories): ?>
        <div class="flex flex-wrap gap-2 mb-5">
            <a href="<?= $search !== '' ? "?q=" . urlencode($search) : '/projet/student/courses.php' ?>"
               class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors <?= $catFilter === 0 ? 'bg-teal-300/15 text-teal-300 border-teal-300/30' : 'text-slate-400 border-white/10 hover:border-white/20 hover:text-slate-200' ?>">
                Tous
            </a>
            <?php foreach ($categories as $cat): ?>
                <?php $url = '?cat=' . (int) $cat['id'] . ($search !== '' ? '&q=' . urlencode($search) : ''); ?>
                <a href="<?= $url ?>"
                   class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors <?= $catFilter === (int) $cat['id'] ? 'bg-teal-300/15 text-teal-300 border-teal-300/30' : 'text-slate-400 border-white/10 hover:border-white/20 hover:text-slate-200' ?>">
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($search !== '' || $catFilter > 0): ?>
        <p class="text-sm text-slate-400 mb-4">
            <?= count($courses) ?> résultat<?= count($courses) !== 1 ? 's' : '' ?>
            <?= $search !== '' ? ' pour <span class="text-slate-200 font-medium">"' . e($search) . '"</span>' : '' ?>
            <span class="text-slate-600"> — <?= $totalAll ?> cours au total</span>
        </p>
    <?php endif; ?>

    <?php if (!$courses): ?>
        <div class="text-center py-12">
            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <p class="text-lg font-semibold text-slate-300 mb-1">Aucun cours trouvé</p>
            <p class="text-sm text-slate-500 mb-4">Essayez d'autres termes ou supprimez les filtres.</p>
            <a href="/projet/student/courses.php" class="btn-secondary btn-sm">Voir tous les cours</a>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 gap-4">
            <?php foreach ($courses as $course): ?>
                <?php $enrolled = (int) $course['enrolled'] === 1; ?>
                <article class="flex flex-col gap-3 p-5 rounded-xl border border-white/[0.08] bg-white/[0.03] hover:bg-white/[0.05] transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <?php if ($course['category_name']): ?>
                                <span class="inline-block tag mb-1"><?= e($course['category_name']) ?></span>
                            <?php else: ?>
                                <p class="eyebrow mb-0.5">Cours</p>
                            <?php endif; ?>
                            <h3 class="font-bold text-slate-200"><?= e($course['title']) ?></h3>
                        </div>
                        <?php if ($enrolled): ?>
                            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-violet-400/10 text-violet-300 border border-violet-400/20">Inscrit</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-400 flex-1"><?= e($course['description']) ?></p>
                    <?php if ($course['avg_rating']): ?>
                        <div class="flex items-center gap-1.5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-3.5 h-3.5 <?= $i <= round((float) $course['avg_rating']) ? 'text-yellow-400' : 'text-slate-700' ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <?php endfor; ?>
                            <span class="text-xs text-slate-400"><?= number_format((float) $course['avg_rating'], 1) ?> (<?= (int) $course['rating_count'] ?>)</span>
                        </div>
                    <?php endif; ?>
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
