<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin', 'prof']);

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT l.*, c.title AS course_title, c.created_by FROM lessons l INNER JOIN courses c ON c.id = l.course_id WHERE l.id = ?');
$stmt->execute([$id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    setFlash('danger', 'Leçon introuvable.');
    redirect('/projet/admin/courses.php');
}

requireCourseOwner(['id' => $lesson['course_id'], 'created_by' => $lesson['created_by']]);

$courseId = (int) $lesson['course_id'];
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $position = (int) ($_POST['position'] ?? $lesson['position']);

    if (empty($title)) {
        $errors[] = 'Le titre est obligatoire.';
    }
    if (empty($content)) {
        $errors[] = 'Le contenu est obligatoire.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE lessons SET title = ?, content = ?, position = ? WHERE id = ?');
        $stmt->execute([$title, $content, $position, $id]);
        setFlash('success', 'Leçon modifiée avec succès.');
        redirect("/projet/admin/lessons.php?course_id=$courseId");
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-slate-400 mb-2">
    <a href="/projet/admin/courses.php" class="hover:text-teal-300 transition-colors">Cours</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="/projet/admin/lessons.php?course_id=<?= $courseId ?>" class="hover:text-teal-300 transition-colors truncate max-w-[200px]"><?= e($lesson['course_title']) ?></a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-300">Modifier la leçon</span>
</div>

<div class="card max-w-3xl">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Édition</p>
            <h2 class="text-xl font-bold text-slate-100">Modifier la leçon</h2>
            <p class="text-sm text-slate-400 mt-1">Cours : <span class="text-slate-300 font-medium"><?= e($lesson['course_title']) ?></span></p>
        </div>
        <a class="btn-secondary shrink-0" href="/projet/admin/lessons.php?course_id=<?= $courseId ?>">Retour</a>
    </div>

    <?php if ($errors): ?>
        <ul class="px-4 py-3 rounded-xl border bg-rose-400/10 border-rose-400/30 text-rose-300 text-sm mb-5 space-y-1 list-disc list-inside">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div class="grid sm:grid-cols-[1fr_120px] gap-5">
            <div>
                <label class="form-label" for="title">Titre de la leçon</label>
                <input class="form-input" id="title" type="text" name="title"
                       value="<?= e($_POST['title'] ?? $lesson['title']) ?>" required>
            </div>
            <div>
                <label class="form-label" for="position">Position</label>
                <input class="form-input" id="position" type="number" name="position" min="1"
                       value="<?= (int) ($_POST['position'] ?? $lesson['position']) ?>">
            </div>
        </div>
        <div>
            <label class="form-label" for="content">Contenu</label>
            <textarea class="form-input resize-y" id="content" name="content" rows="12" required><?= e($_POST['content'] ?? $lesson['content']) ?></textarea>
        </div>
        <div class="flex gap-3 pt-1">
            <button type="submit" class="btn">Enregistrer les modifications</button>
            <a class="btn-secondary" href="/projet/admin/lessons.php?course_id=<?= $courseId ?>">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
