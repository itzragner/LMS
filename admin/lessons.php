<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin', 'prof']);

$courseId = (int) ($_GET['course_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('danger', 'Cours introuvable.');
    redirect('/projet/admin/courses.php');
}

requireCourseOwner($course);

$stmt = $pdo->prepare('SELECT * FROM lessons WHERE course_id = ? ORDER BY position ASC');
$stmt->execute([$courseId]);
$lessons = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-slate-400 mb-2">
    <a href="/projet/admin/courses.php" class="hover:text-teal-300 transition-colors">Cours</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-300 truncate"><?= e($course['title']) ?></span>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-300">Leçons</span>
</div>

<!-- Header card -->
<div class="card">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="eyebrow mb-1">Contenu du cours</p>
            <h2 class="text-xl font-bold text-slate-100"><?= e($course['title']) ?></h2>
            <p class="text-sm text-slate-400 mt-1 max-w-xl"><?= e($course['description']) ?></p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="tag text-base px-4 py-1.5"><?= count($lessons) ?> leçon<?= count($lessons) !== 1 ? 's' : '' ?></span>
            <a class="btn" href="/projet/admin/add_lesson.php?course_id=<?= $courseId ?>">Ajouter une leçon</a>
            <a class="btn-secondary" href="/projet/admin/courses.php">Retour</a>
        </div>
    </div>
</div>

<!-- Lessons list -->
<div class="card">
    <p class="eyebrow mb-4">Liste des leçons</p>

    <?php if (!$lessons): ?>
        <div class="text-center py-12">
            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-lg font-semibold text-slate-300 mb-1">Aucune leçon créée</p>
            <p class="text-sm text-slate-500">Ajoutez votre première leçon pour ce cours.</p>
        </div>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($lessons as $i => $lesson): ?>
                <div class="flex items-center gap-4 p-4 rounded-xl border border-white/[0.07] bg-white/[0.03] hover:bg-white/[0.05] transition-colors">
                    <span class="w-8 h-8 rounded-lg bg-teal-300/10 text-teal-300 border border-teal-300/20 flex items-center justify-center text-xs font-bold shrink-0">
                        <?= $lesson['position'] ?>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-200"><?= e($lesson['title']) ?></p>
                        <p class="text-xs text-slate-500 mt-0.5 truncate"><?= e(mb_substr(strip_tags($lesson['content']), 0, 80)) ?>…</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a class="btn-secondary btn-sm" href="/projet/admin/edit_lesson.php?id=<?= (int) $lesson['id'] ?>">Modifier</a>
                        <a class="btn-danger btn-sm"
                           href="/projet/admin/delete_lesson.php?id=<?= (int) $lesson['id'] ?>"
                           onclick="return confirm('Supprimer cette leçon ?')">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
