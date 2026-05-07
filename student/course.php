<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$userId   = currentUser()['id'];
$courseId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT c.*, cat.name AS category_name
    FROM courses c
    LEFT JOIN categories cat ON cat.id = c.category_id
    WHERE c.id = ?
');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('danger', 'Cours introuvable.');
    redirect('/projet/student/courses.php');
}

$stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?');
$stmt->execute([$courseId, $userId]);
if (!$stmt->fetch()) {
    setFlash('danger', 'Vous devez être inscrit à ce cours pour accéder à son contenu.');
    redirect('/projet/student/courses.php');
}

$stmt = $pdo->prepare('
    SELECT l.id, l.title, l.content, l.position,
           (SELECT COUNT(*) FROM lesson_completions lc WHERE lc.lesson_id = l.id AND lc.user_id = ?) AS is_completed
    FROM lessons l
    WHERE l.course_id = ?
    ORDER BY l.position ASC
');
$stmt->execute([$userId, $courseId]);
$lessons = $stmt->fetchAll();

$totalLessons    = count($lessons);
$completedLessons = count(array_filter($lessons, fn($l) => (int) $l['is_completed']));
$pct             = $totalLessons > 0 ? round($completedLessons / $totalLessons * 100) : 0;

$stmtRating = $pdo->prepare('
    SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS rating_count,
           (SELECT rating FROM ratings WHERE course_id = ? AND user_id = ?) AS my_rating
    FROM ratings WHERE course_id = ?
');
$stmtRating->execute([$courseId, $userId, $courseId]);
$ratingData = $stmtRating->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-slate-400 mb-2">
    <a href="/projet/student/dashboard.php" class="hover:text-teal-300 transition-colors">Dashboard</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="/projet/student/courses.php" class="hover:text-teal-300 transition-colors">Cours</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-300 truncate"><?= e($course['title']) ?></span>
</div>

<!-- Header card -->
<div class="card">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <?php if ($course['category_name']): ?>
                    <span class="tag"><?= e($course['category_name']) ?></span>
                <?php endif; ?>
                <?php if ($ratingData['avg_rating']): ?>
                    <span class="flex items-center gap-1 text-yellow-400 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?= number_format((float) $ratingData['avg_rating'], 1) ?>
                        <span class="text-slate-500 font-normal">(<?= (int) $ratingData['rating_count'] ?> avis)</span>
                    </span>
                <?php endif; ?>
            </div>
            <h2 class="text-xl font-bold text-slate-100"><?= e($course['title']) ?></h2>
            <p class="text-sm text-slate-400 mt-1 max-w-xl"><?= e($course['description']) ?></p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="tag text-base px-4 py-1.5"><?= $totalLessons ?> leçon<?= $totalLessons !== 1 ? 's' : '' ?></span>
            <a class="btn-secondary" href="/projet/student/courses.php">Retour</a>
        </div>
    </div>

    <!-- Progress bar -->
    <?php if ($totalLessons > 0): ?>
        <div class="mt-5 pt-5 border-t border-white/10">
            <div class="flex justify-between text-xs text-slate-400 mb-2">
                <span class="font-medium text-slate-300">Progression</span>
                <span><?= $completedLessons ?>/<?= $totalLessons ?> leçons terminées</span>
            </div>
            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 <?= $pct === 100 ? 'bg-teal-400' : 'bg-gradient-to-r from-teal-400 to-cyan-400' ?>"
                     style="width: <?= $pct ?>%"></div>
            </div>
            <?php if ($pct === 100): ?>
                <p class="text-xs text-teal-300 font-semibold mt-1.5">Cours terminé !</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Lessons list -->
<div class="card">
    <p class="eyebrow mb-4">Programme</p>

    <?php if (!$lessons): ?>
        <div class="text-center py-12">
            <p class="text-lg font-semibold text-slate-300 mb-1">Aucune leçon disponible</p>
            <p class="text-sm text-slate-500">Le professeur n'a pas encore ajouté de contenu.</p>
        </div>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($lessons as $lesson): ?>
                <?php $done = (int) $lesson['is_completed']; ?>
                <a href="/projet/student/lesson.php?id=<?= (int) $lesson['id'] ?>"
                   class="flex items-center gap-4 p-4 rounded-xl border transition-colors group <?= $done ? 'border-teal-300/15 bg-teal-300/[0.04] hover:bg-teal-300/[0.07]' : 'border-white/[0.07] bg-white/[0.03] hover:bg-white/[0.06] hover:border-teal-300/20' ?>">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 <?= $done ? 'bg-teal-400/20 text-teal-300 border border-teal-400/30' : 'bg-teal-300/10 text-teal-300 border border-teal-300/20' ?>">
                        <?php if ($done): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <?php else: ?>
                            <?= $lesson['position'] ?>
                        <?php endif; ?>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-200 group-hover:text-teal-300 transition-colors <?= $done ? 'line-through decoration-teal-300/40' : '' ?>"><?= e($lesson['title']) ?></p>
                        <p class="text-xs text-slate-500 mt-0.5 truncate"><?= e(mb_substr(strip_tags($lesson['content']), 0, 80)) ?>…</p>
                    </div>
                    <?php if ($done): ?>
                        <span class="text-xs text-teal-300 font-semibold shrink-0">Terminé</span>
                    <?php else: ?>
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-teal-300 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Rating -->
<div class="card">
    <p class="eyebrow mb-3">Votre avis</p>
    <p class="text-sm text-slate-400 mb-4">Notez ce cours pour aider les autres étudiants.</p>
    <div class="flex items-center gap-1">
        <?php $myRating = (int) ($ratingData['my_rating'] ?? 0); ?>
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <form method="POST" action="/projet/student/rate_course.php" class="inline">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <input type="hidden" name="rating" value="<?= $i ?>">
                <button type="submit"
                        class="<?= $i <= $myRating ? 'text-yellow-400' : 'text-slate-600' ?> hover:text-yellow-400 transition-colors"
                        title="<?= $i ?> étoile<?= $i > 1 ? 's' : '' ?>">
                    <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </button>
            </form>
        <?php endfor; ?>
        <?php if ($myRating > 0): ?>
            <span class="text-sm text-slate-400 ml-2">Votre note : <span class="text-yellow-400 font-semibold"><?= $myRating ?>/5</span></span>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
