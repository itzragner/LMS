<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    setFlash('danger', 'Catégorie introuvable.');
    redirect('/projet/admin/categories.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        $errors[] = 'Le nom est obligatoire.';
    }
    if (!$errors) {
        try {
            $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?')->execute([$name, $id]);
            setFlash('success', 'Catégorie modifiée.');
            redirect('/projet/admin/categories.php');
        } catch (\PDOException) {
            $errors[] = 'Ce nom de catégorie existe déjà.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card max-w-md">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Édition</p>
            <h2 class="text-xl font-bold text-slate-100">Modifier la catégorie</h2>
        </div>
        <a class="btn-secondary shrink-0" href="/projet/admin/categories.php">Retour</a>
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
            <label class="form-label" for="name">Nom de la catégorie</label>
            <input class="form-input" id="name" type="text" name="name"
                   value="<?= e($_POST['name'] ?? $category['name']) ?>" required>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn">Enregistrer</button>
            <a class="btn-secondary" href="/projet/admin/categories.php">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
