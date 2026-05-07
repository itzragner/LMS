<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$userId   = currentUser()['id'];
$lessonId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT l.*, c.title AS course_title, c.id AS course_id
    FROM lessons l
    INNER JOIN courses c ON c.id = l.course_id
    WHERE l.id = ?
');
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    setFlash('danger', 'Leçon introuvable.');
    redirect('/projet/student/courses.php');
}

$stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?');
$stmt->execute([$lesson['course_id'], $userId]);
if (!$stmt->fetch()) {
    setFlash('danger', 'Vous devez être inscrit à ce cours pour accéder à ses leçons.');
    redirect('/projet/student/courses.php');
}

$courseId = (int) $lesson['course_id'];

$stmt = $pdo->prepare('SELECT 1 FROM lesson_completions WHERE lesson_id = ? AND user_id = ?');
$stmt->execute([$lessonId, $userId]);
$isCompleted = (bool) $stmt->fetch();

$stmtPrev = $pdo->prepare('SELECT id, title FROM lessons WHERE course_id = ? AND position < ? ORDER BY position DESC LIMIT 1');
$stmtPrev->execute([$courseId, $lesson['position']]);
$prev = $stmtPrev->fetch();

$stmtNext = $pdo->prepare('SELECT id, title FROM lessons WHERE course_id = ? AND position > ? ORDER BY position ASC LIMIT 1');
$stmtNext->execute([$courseId, $lesson['position']]);
$next = $stmtNext->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-slate-400 mb-2 flex-wrap">
    <a href="/projet/student/dashboard.php" class="hover:text-teal-300 transition-colors">Dashboard</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="/projet/student/course.php?id=<?= $courseId ?>" class="hover:text-teal-300 transition-colors truncate max-w-[160px]"><?= e($lesson['course_title']) ?></a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-300 truncate"><?= e($lesson['title']) ?></span>
</div>

<!-- Lesson content -->
<div class="card max-w-3xl">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg <?= $isCompleted ? 'bg-teal-400/20 text-teal-300 border border-teal-400/30' : 'bg-teal-300/10 text-teal-300 border border-teal-300/20' ?> flex items-center justify-center text-xs font-bold shrink-0">
                <?php if ($isCompleted): ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <?php else: ?>
                    <?= $lesson['position'] ?>
                <?php endif; ?>
            </span>
            <div>
                <p class="eyebrow"><?= e($lesson['course_title']) ?></p>
                <h2 class="text-xl font-bold text-slate-100"><?= e($lesson['title']) ?></h2>
            </div>
        </div>
        <!-- Complete toggle -->
        <form method="POST" action="/projet/student/complete_lesson.php" class="shrink-0">
            <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
            <?php if ($isCompleted): ?>
                <button type="submit" class="btn-secondary btn-sm flex items-center gap-1.5 text-teal-300 border-teal-300/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Terminée
                </button>
            <?php else: ?>
                <button type="submit" class="btn btn-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Marquer comme terminée
                </button>
            <?php endif; ?>
        </form>
    </div>

    <div class="text-slate-300 leading-relaxed whitespace-pre-wrap border-t border-white/10 pt-6">
        <?= nl2br(e($lesson['content'])) ?>
    </div>
</div>

<!-- Navigation prev / next -->
<div class="flex items-center justify-between gap-4 max-w-3xl">
    <div>
        <?php if ($prev): ?>
            <a href="/projet/student/lesson.php?id=<?= (int) $prev['id'] ?>"
               class="flex items-center gap-2 text-sm text-slate-400 hover:text-teal-300 transition-colors group">
                <svg class="w-4 h-4 rotate-180 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="truncate max-w-[200px]"><?= e($prev['title']) ?></span>
            </a>
        <?php endif; ?>
    </div>
    <a href="/projet/student/course.php?id=<?= $courseId ?>" class="text-xs text-slate-500 hover:text-slate-300 transition-colors shrink-0">Sommaire</a>
    <div class="text-right">
        <?php if ($next): ?>
            <a href="/projet/student/lesson.php?id=<?= (int) $next['id'] ?>"
               class="flex items-center gap-2 text-sm text-slate-400 hover:text-teal-300 transition-colors group">
                <span class="truncate max-w-[200px]"><?= e($next['title']) ?></span>
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        <?php else: ?>
            <span class="text-xs text-teal-300 font-semibold">Dernière leçon</span>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
