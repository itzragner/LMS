<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$search = trim($_GET['q'] ?? '');
$like   = "%$search%";

if ($search !== '') {
    $stmt = $pdo->prepare('
        SELECT u.*, COUNT(e.id) AS enrollment_count
        FROM users u
        LEFT JOIN enrollments e ON e.user_id = u.id
        WHERE u.name LIKE ? OR u.email LIKE ?
        GROUP BY u.id
        ORDER BY u.id ASC
    ');
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query('
        SELECT u.*, COUNT(e.id) AS enrollment_count
        FROM users u
        LEFT JOIN enrollments e ON e.user_id = u.id
        GROUP BY u.id
        ORDER BY u.id ASC
    ');
}

$users = $stmt->fetchAll();

$allUsers      = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalProfs    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'prof'")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';

function roleBadge(string $role): string
{
    return match($role) {
        'admin'   => '<span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-400/10 text-rose-300 border border-rose-400/20">Admin</span>',
        'prof'    => '<span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-400/10 text-violet-300 border border-violet-400/20">Professeur</span>',
        'student' => '<span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-300/10 text-teal-300 border border-teal-300/20">Étudiant</span>',
        default   => '<span class="tag">' . htmlspecialchars($role, ENT_QUOTES, 'UTF-8') . '</span>',
    };
}
?>

<!-- Metrics -->
<div class="grid sm:grid-cols-3 gap-5">
    <article class="metric-box">
        <p class="eyebrow mb-1">Utilisateurs</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $allUsers ?></p>
        <p class="text-sm text-slate-400">Comptes enregistrés au total.</p>
    </article>
    <article class="metric-box">
        <p class="eyebrow mb-1">Étudiants</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalStudents ?></p>
        <p class="text-sm text-slate-400">Comptes étudiants actifs.</p>
    </article>
    <article class="metric-box">
        <p class="eyebrow mb-1">Professeurs</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalProfs ?></p>
        <p class="text-sm text-slate-400">Comptes professeurs actifs.</p>
    </article>
</div>

<!-- Users table -->
<div class="card">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Administration</p>
            <h2 class="text-xl font-bold text-slate-100">Gestion des utilisateurs</h2>
            <p class="text-sm text-slate-400 mt-1">Ajoutez, modifiez ou supprimez les comptes utilisateurs.</p>
        </div>
        <a class="btn shrink-0" href="/projet/admin/add_user.php">Ajouter un utilisateur</a>
    </div>

    <!-- Search bar -->
    <form method="GET" class="mb-6">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input class="form-input pl-11 pr-10"
                   type="text" name="q"
                   value="<?= e($search) ?>"
                   placeholder="Rechercher par nom ou email…">
            <?php if ($search !== ''): ?>
                <a href="/projet/admin/users.php"
                   class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                   title="Effacer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($search !== ''): ?>
        <p class="text-sm text-slate-400 mb-4">
            <?= count($users) ?> résultat<?= count($users) !== 1 ? 's' : '' ?> pour
            <span class="text-slate-200 font-medium">"<?= e($search) ?>"</span>
        </p>
    <?php endif; ?>

    <?php if (!$users): ?>
        <div class="text-center py-12">
            <?php if ($search !== ''): ?>
                <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <p class="text-lg font-semibold text-slate-300 mb-1">Aucun utilisateur trouvé</p>
                <p class="text-sm text-slate-500 mb-4">Aucun compte ne correspond à "<span class="text-slate-400"><?= e($search) ?></span>".</p>
                <a href="/projet/admin/users.php" class="btn-secondary btn-sm">Voir tous les utilisateurs</a>
            <?php else: ?>
                <p class="text-lg font-semibold text-slate-300 mb-1">Aucun utilisateur</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto -mx-6 px-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Utilisateur</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Email</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Rôle</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Inscriptions</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05]">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-white/[0.03] transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center font-bold text-white text-xs shrink-0">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <span class="font-semibold text-slate-200"><?= e($u['name']) ?></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400"><?= e($u['email']) ?></td>
                            <td class="py-3.5 px-4"><?= roleBadge($u['role']) ?></td>
                            <td class="py-3.5 px-4 text-slate-400"><?= (int) $u['enrollment_count'] ?></td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <a class="btn-secondary btn-sm" href="/projet/admin/edit_user.php?id=<?= (int) $u['id'] ?>">Modifier</a>
                                    <?php if ((int) $u['id'] !== (int) currentUser()['id']): ?>
                                        <a class="btn-danger btn-sm"
                                           href="/projet/admin/delete_user.php?id=<?= (int) $u['id'] ?>"
                                           onclick="return confirm('Supprimer <?= e(addslashes($u['name'])) ?> ?')">Supprimer</a>
                                    <?php else: ?>
                                        <span class="btn-sm text-slate-600 cursor-default select-none">Vous</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
