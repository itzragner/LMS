<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        $errors[] = 'Le nom est obligatoire.';
    }
    if (!$errors) {
        try {
            $pdo->prepare('INSERT INTO categories (name) VALUES (?)')->execute([$name]);
            setFlash('success', 'Catégorie ajoutée.');
        } catch (\PDOException) {
            $errors[] = 'Ce nom de catégorie existe déjà.';
        }
        if (!$errors) redirect('/projet/admin/categories.php');
    }
}

$categories = $pdo->query('
    SELECT cat.*, COUNT(c.id) AS course_count
    FROM categories cat
    LEFT JOIN courses c ON c.category_id = cat.id
    GROUP BY cat.id
    ORDER BY cat.name ASC
')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card max-w-2xl">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Administration</p>
            <h2 class="text-xl font-bold text-slate-100">Catégories de cours</h2>
            <p class="text-sm text-slate-400 mt-1">Gérez les catégories assignables aux cours.</p>
        </div>
    </div>

    <!-- Add form -->
    <form method="POST" class="flex gap-3 mb-6">
        <?php if ($errors): ?>
            <div class="w-full mb-3 px-4 py-2.5 rounded-xl border bg-rose-400/10 border-rose-400/30 text-rose-300 text-sm"><?= e($errors[0]) ?></div>
        <?php endif; ?>
        <input class="form-input flex-1" type="text" name="name"
               placeholder="Ex : Informatique, Maths, Langues…"
               value="<?= e($_POST['name'] ?? '') ?>" required>
        <button type="submit" class="btn shrink-0">Ajouter</button>
    </form>

    <?php if (!$categories): ?>
        <div class="text-center py-10">
            <p class="text-slate-400 text-sm">Aucune catégorie créée. Ajoutez-en une ci-dessus.</p>
        </div>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($categories as $cat): ?>
                <div class="flex items-center justify-between gap-4 px-4 py-3 rounded-xl border border-white/[0.07] bg-white/[0.03]">
                    <div class="flex items-center gap-3">
                        <span class="tag"><?= e($cat['name']) ?></span>
                        <span class="text-xs text-slate-500"><?= (int) $cat['course_count'] ?> cours</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a class="btn-secondary btn-sm" href="/projet/admin/edit_category.php?id=<?= (int) $cat['id'] ?>">Modifier</a>
                        <a class="btn-danger btn-sm"
                           href="/projet/admin/delete_category.php?id=<?= (int) $cat['id'] ?>"
                           onclick="return confirm('Supprimer la catégorie <?= e(addslashes($cat['name'])) ?> ?')">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
