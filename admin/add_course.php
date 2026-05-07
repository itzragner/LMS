<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
requireRole(['admin', 'prof']);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $errors = validateCourse(compact('title', 'description'));

    if (!$errors) {
        $userId = currentUser()['id'];
        $stmt = $pdo->prepare('INSERT INTO courses (title, description, created_by) VALUES (?, ?, ?)');
        $stmt->execute([$title, $description, $userId]);
        setFlash('success', 'Cours ajouté avec succès.');
        redirect('/projet/admin/courses.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card max-w-2xl">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Nouveau cours</p>
            <h2 class="text-xl font-bold text-slate-100">Ajouter un cours</h2>
            <p class="text-sm text-slate-400 mt-1">Créez un nouveau contenu pour vos étudiants.</p>
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
                   placeholder="Ex : Introduction à PHP"
                   value="<?= e($_POST['title'] ?? '') ?>" required>
        </div>
        <div>
            <label class="form-label" for="description">Description</label>
            <textarea class="form-input resize-y" id="description" name="description" rows="4"
                      placeholder="Décrivez le contenu du cours..." required><?= e($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="flex gap-3 pt-1">
            <button type="submit" class="btn">Ajouter le cours</button>
            <a class="btn-secondary" href="/projet/admin/courses.php">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>