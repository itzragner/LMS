<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin', 'prof']);

$user = currentUser();
$isAdmin = $user['role'] === 'admin';

if ($isAdmin) {
    $stmt = $pdo->query('
        SELECT c.*, COUNT(e.id) AS enrolled_count, u.name AS prof_name
        FROM courses c
        LEFT JOIN enrollments e ON e.course_id = c.id
        LEFT JOIN users u ON u.id = c.created_by
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ');
} else {
    $stmt = $pdo->prepare('
        SELECT c.*, COUNT(e.id) AS enrolled_count
        FROM courses c
        LEFT JOIN enrollments e ON e.course_id = c.id
        WHERE c.created_by = ?
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ');
    $stmt->execute([$user['id']]);
}

$courses = $stmt->fetchAll();
$totalCourses = count($courses);

if ($isAdmin) {
    $totalEnrollments = (int) $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
    $totalStudents    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
} else {
    $totalEnrollments = (int) array_sum(array_column($courses, 'enrolled_count'));
    $stmtS = $pdo->prepare('
        SELECT COUNT(DISTINCT e.user_id)
        FROM enrollments e
        INNER JOIN courses c ON c.id = e.course_id
        WHERE c.created_by = ?
    ');
    $stmtS->execute([$user['id']]);
    $totalStudents = (int) $stmtS->fetchColumn();
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Metrics -->
<div class="grid sm:grid-cols-3 gap-5">
    <article class="metric-box">
        <p class="eyebrow mb-1">Cours</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalCourses ?></p>
        <p class="text-sm text-slate-400"><?= $isAdmin ? 'Nombre total de cours publiés.' : 'Vos cours publiés.' ?></p>
    </article>
    <article class="metric-box">
        <p class="eyebrow mb-1">Étudiants</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalStudents ?></p>
        <p class="text-sm text-slate-400"><?= $isAdmin ? 'Utilisateurs étudiants enregistrés.' : 'Étudiants inscrits à vos cours.' ?></p>
    </article>
    <article class="metric-box">
        <p class="eyebrow mb-1">Inscriptions</p>
        <p class="text-4xl font-extrabold text-slate-100 my-1"><?= $totalEnrollments ?></p>
        <p class="text-sm text-slate-400"><?= $isAdmin ? 'Inscriptions cumulées sur la plateforme.' : 'Inscriptions à vos cours.' ?></p>
    </article>
</div>

<!-- Courses table -->
<div class="card">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1"><?= $isAdmin ? 'Administration' : 'Professeur' ?></p>
            <h2 class="text-xl font-bold text-slate-100">Gestion des cours</h2>
            <p class="text-sm text-slate-400 mt-1">Ajoutez, modifiez ou supprimez les cours depuis cet espace.</p>
        </div>
        <a class="btn shrink-0" href="/projet/admin/add_course.php">Ajouter un cours</a>
    </div>

    <?php if (!$courses): ?>
        <div class="text-center py-12">
            <p class="text-lg font-semibold text-slate-300 mb-1">Aucun cours créé</p>
            <p class="text-sm text-slate-500">Commencez par ajouter votre premier cours à la plateforme.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto -mx-6 px-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Titre</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Description</th>
                        <?php if ($isAdmin): ?>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Professeur</th>
                        <?php endif; ?>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Inscrits</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05]">
                    <?php foreach ($courses as $course): ?>
                        <tr class="hover:bg-white/[0.03] transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-200"><?= e($course['title']) ?></span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 max-w-xs truncate"><?= e($course['description']) ?></td>
                            <?php if ($isAdmin): ?>
                            <td class="py-3.5 px-4 text-slate-400">
                                <?= $course['prof_name'] ? e($course['prof_name']) : '<span class="text-slate-600">—</span>' ?>
                            </td>
                            <?php endif; ?>
                            <td class="py-3.5 px-4">
                                <span class="tag"><?= (int) $course['enrolled_count'] ?> inscrits</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <a class="btn-secondary btn-sm" href="/projet/admin/lessons.php?course_id=<?= (int) $course['id'] ?>">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Leçons
                                    </a>
                                    <a class="btn-secondary btn-sm" href="/projet/admin/course_students.php?id=<?= (int) $course['id'] ?>">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Inscrits
                                    </a>
                                    <a class="btn-secondary btn-sm" href="/projet/admin/edit_course.php?id=<?= (int) $course['id'] ?>">Modifier</a>
                                    <a class="btn-danger btn-sm" href="/projet/admin/delete_course.php?id=<?= (int) $course['id'] ?>"
                                       onclick="return confirm('Supprimer ce cours ?')">Supprimer</a>
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
