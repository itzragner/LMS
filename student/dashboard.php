<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$user   = currentUser();
$userId = (int) $user['id'];

$stmt = $pdo->prepare('
    SELECT c.id, c.title, c.description, e.enrolled_at,
        (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) AS total_lessons,
        (SELECT COUNT(*) FROM lesson_completions lc
         INNER JOIN lessons l ON l.id = lc.lesson_id
         WHERE l.course_id = c.id AND lc.user_id = ?) AS completed_lessons
    FROM enrollments e
    INNER JOIN courses c ON c.id = e.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
');
$stmt->execute([$userId, $userId]);
$enrollments = $stmt->fetchAll();

$totalCourses = count($enrollments);
$allCourses   = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome banner -->
<div class="card">
    <p class="eyebrow mb-1">Bienvenue</p>
    <h2 class="text-xl font-bold text-slate-100 mb-1"><?= e($user['name']) ?>, voici votre progression.</h2>
    <p class="text-slate-400 text-sm">Consultez rapidement vos inscriptions et explorez de nouveaux cours.</p>
</div>

<!-- Metrics -->
<div class="grid sm:grid-cols-3 gap-5">
    <article class="metric-box">
        <p class="eyebrow mb-1">Mes cours</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalCourses ?></p>
        <p class="text-sm text-slate-400">Cours auxquels vous êtes inscrit.</p>
    </article>
    <article class="metric-box">
        <p class="eyebrow mb-1">Catalogue total</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $allCourses ?></p>
        <p class="text-sm text-slate-400">Nombre total de cours disponibles.</p>
    </article>
    <article class="metric-box">
        <p class="eyebrow mb-1">Leçons terminées</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1">
            <?= array_sum(array_column($enrollments, 'completed_lessons')) ?>
        </p>
        <p class="text-sm text-slate-400">Leçons complétées au total.</p>
    </article>
</div>

<!-- Enrolled courses -->
<div class="card">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Mes inscriptions</p>
            <h2 class="text-xl font-bold text-slate-100">Cours inscrits</h2>
        </div>
        <a class="btn shrink-0" href="/projet/student/courses.php">Explorer les cours</a>
    </div>

    <?php if (!$enrollments): ?>
        <div class="text-center py-12">
            <p class="text-lg font-semibold text-slate-300 mb-1">Aucune inscription pour le moment</p>
            <p class="text-sm text-slate-500">Consultez le catalogue et inscrivez-vous à votre premier cours.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($enrollments as $course): ?>
                <?php
                $total     = (int) $course['total_lessons'];
                $completed = (int) $course['completed_lessons'];
                $pct       = $total > 0 ? round($completed / $total * 100) : 0;
                ?>
                <article class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.03] hover:bg-white/[0.05] transition-colors">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-200 mb-0.5"><?= e($course['title']) ?></h3>
                            <p class="text-sm text-slate-400 truncate"><?= e($course['description']) ?></p>
                        </div>
                        <a href="/projet/student/course.php?id=<?= (int) $course['id'] ?>" class="btn btn-sm shrink-0">Voir les leçons</a>
                    </div>
                    <?php if ($total > 0): ?>
                        <div>
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span><?= $completed ?>/<?= $total ?> leçons</span>
                                <span><?= $pct ?>%</span>
                            </div>
                            <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all <?= $pct === 100 ? 'bg-teal-400' : 'bg-gradient-to-r from-teal-400 to-cyan-400' ?>"
                                     style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-xs text-slate-600">Aucune leçon disponible pour l'instant.</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
