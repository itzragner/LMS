<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$stmt = $pdo->query('
    SELECT u.*, COUNT(e.id) AS enrollment_count
    FROM users u
    LEFT JOIN enrollments e ON e.user_id = u.id
    GROUP BY u.id
    ORDER BY u.id ASC
');
$users = $stmt->fetchAll();

$totalUsers    = count($users);
$totalStudents = count(array_filter($users, fn($u) => $u['role'] === 'student'));
$totalProfs    = count(array_filter($users, fn($u) => $u['role'] === 'prof'));

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
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalUsers ?></p>
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

    <?php if (!$users): ?>
        <div class="text-center py-12">
            <p class="text-lg font-semibold text-slate-300 mb-1">Aucun utilisateur</p>
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
