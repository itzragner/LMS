<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('danger', 'Cours introuvable.');
    redirect('/projet/admin/courses.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $errors = validateCourse(compact('title', 'description'));

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE courses SET title = ?, description = ? WHERE id = ?');
        $stmt->execute([$title, $description, $id]);
        setFlash('success', 'Cours modifié avec succès.');
        redirect('/projet/admin/courses.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card max-w-2xl">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Édition</p>
            <h2 class="text-xl font-bold text-slate-100">Modifier le cours</h2>
            <p class="text-sm text-slate-400 mt-1">Mettez à jour le titre ou la description du cours.</p>
        </div>
        <a class="btn-secondary shrink-0" href="/projet/admin/courses.php">Retour</a>
    </div>

    <?php if ($errors): ?>
        <ul class="px-4 py-3 rounded-xl border bg-rose-400/10 border-rose-400/30 text-rose-300 text-sm mb-5 space-y-1 list-disc list-inside">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="form-label" for="title">Titre du cours</label>
            <input class="form-input" id="title" type="text" name="title"
                   value="<?= e($_POST['title'] ?? $course['title']) ?>" required>
        </div>
        <div>
            <label class="form-label" for="description">Description</label>
            <textarea class="form-input resize-y" id="description" name="description" rows="4" required><?= e($_POST['description'] ?? $course['description']) ?></textarea>
        </div>
        <div class="flex gap-3 pt-1">
            <button type="submit" class="btn">Enregistrer les modifications</button>
            <a class="btn-secondary" href="/projet/admin/courses.php">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
