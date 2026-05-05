<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$courseId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('danger', 'Cours introuvable.');
    redirect('/projet/admin/courses.php');
}

$stmt = $pdo->prepare('
    SELECT u.id, u.name, u.email, e.enrolled_at
    FROM enrollments e
    INNER JOIN users u ON u.id = e.user_id
    WHERE e.course_id = ?
    ORDER BY e.enrolled_at DESC
');
$stmt->execute([$courseId]);
$students = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb / back -->
<div class="flex items-center gap-2 text-sm text-slate-400 mb-2">
    <a href="/projet/admin/courses.php" class="hover:text-teal-300 transition-colors">Gestion des cours</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-300 truncate"><?= e($course['title']) ?></span>
</div>

<!-- Header card -->
<div class="card">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="eyebrow mb-1">Étudiants inscrits</p>
            <h2 class="text-xl font-bold text-slate-100"><?= e($course['title']) ?></h2>
            <p class="text-sm text-slate-400 mt-1 max-w-xl"><?= e($course['description']) ?></p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="tag text-base px-4 py-1.5"><?= count($students) ?> inscrit<?= count($students) !== 1 ? 's' : '' ?></span>
            <a class="btn-secondary" href="/projet/admin/courses.php">Retour</a>
        </div>
    </div>
</div>

<!-- Students list -->
<div class="card">
    <p class="eyebrow mb-4">Liste des étudiants</p>

    <?php if (!$students): ?>
        <div class="text-center py-12">
            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-lg font-semibold text-slate-300 mb-1">Aucun étudiant inscrit</p>
            <p class="text-sm text-slate-500">Personne ne s'est encore inscrit à ce cours.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto -mx-6 px-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Étudiant</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Email</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Inscrit le</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05]">
                    <?php foreach ($students as $student): ?>
                        <tr class="hover:bg-white/[0.03] transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center font-bold text-white text-xs shrink-0">
                                        <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                    </div>
                                    <span class="font-semibold text-slate-200"><?= e($student['name']) ?></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400"><?= e($student['email']) ?></td>
                            <td class="py-3.5 px-4 text-slate-400"><?= e($student['enrolled_at']) ?></td>
                            <td class="py-3.5 px-4">
                                <a class="btn-danger btn-sm"
                                   href="/projet/admin/unenroll_student.php?course_id=<?= $courseId ?>&user_id=<?= (int) $student['id'] ?>"
                                   onclick="return confirm('Désinscrire <?= e(addslashes($student['name'])) ?> de ce cours ?')">
                                    Désinscrire
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
