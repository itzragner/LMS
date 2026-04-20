<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
requireRole('admin');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $errors = validateCourse(compact('title', 'description'));

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO courses (title, description) VALUES (?, ?)');
        $stmt->execute([$title, $description]);
        setFlash('success', 'Cours ajouté avec succès.');
        redirect('/projet/admin/courses.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <div>
            <span class="eyebrow">Nouveau cours</span>
            <h2>Ajouter un cours</h2>
            <p class="muted">Créez un nouveau contenu pour vos étudiants.</p>
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
            <input id="title" type="text" name="title" placeholder="Ex: Introduction à PHP" value="<?= e($_POST['title'] ?? '') ?>" required>
        </div>
        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Décrivez le contenu du cours..." required><?= e($_POST['description'] ?? '') ?></textarea>
        </div>
        <button type="submit">Ajouter le cours</button>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
