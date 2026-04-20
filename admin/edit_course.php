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
<section class="card">
    <div class="card-header">
        <div>
            <span class="eyebrow">Édition</span>
            <h2>Modifier le cours</h2>
            <p class="muted">Mettez à jour le titre ou la description du cours.</p>
        </div>
        <a class="btn btn-secondary" href="/projet/admin/courses.php">Retour</a>
    </div>

    <?php if ($errors): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label for="title">Titre du cours</label>
            <input id="title" type="text" name="title" value="<?= e($_POST['title'] ?? $course['title']) ?>" required>
        </div>
        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description" required><?= e($_POST['description'] ?? $course['description']) ?></textarea>
        </div>
        <button type="submit">Enregistrer les modifications</button>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
