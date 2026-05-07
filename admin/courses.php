<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin', 'prof']);

$user    = currentUser();
$isAdmin = $user['role'] === 'admin';
$search  = trim($_GET['q'] ?? '');
$like    = "%$search%";

if ($isAdmin) {
    if ($search !== '') {
        $stmt = $pdo->prepare('
            SELECT c.*, COUNT(DISTINCT e.id) AS enrolled_count, u.name AS prof_name,
                   cat.name AS category_name, ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(DISTINCT r.id) AS rating_count
            FROM courses c
            LEFT JOIN enrollments e ON e.course_id = c.id
            LEFT JOIN users u ON u.id = c.created_by
            LEFT JOIN categories cat ON cat.id = c.category_id
            LEFT JOIN ratings r ON r.course_id = c.id
            WHERE c.title LIKE ? OR c.description LIKE ?
            GROUP BY c.id ORDER BY c.created_at DESC
        ');
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $pdo->query('
            SELECT c.*, COUNT(DISTINCT e.id) AS enrolled_count, u.name AS prof_name,
                   cat.name AS category_name, ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(DISTINCT r.id) AS rating_count
            FROM courses c
            LEFT JOIN enrollments e ON e.course_id = c.id
            LEFT JOIN users u ON u.id = c.created_by
            LEFT JOIN categories cat ON cat.id = c.category_id
            LEFT JOIN ratings r ON r.course_id = c.id
            GROUP BY c.id ORDER BY c.created_at DESC
        ');
    }
} else {
    if ($search !== '') {
        $stmt = $pdo->prepare('
            SELECT c.*, COUNT(DISTINCT e.id) AS enrolled_count,
                   cat.name AS category_name, ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(DISTINCT r.id) AS rating_count
            FROM courses c
            LEFT JOIN enrollments e ON e.course_id = c.id
            LEFT JOIN categories cat ON cat.id = c.category_id
            LEFT JOIN ratings r ON r.course_id = c.id
            WHERE c.created_by = ? AND (c.title LIKE ? OR c.description LIKE ?)
            GROUP BY c.id ORDER BY c.created_at DESC
        ');
        $stmt->execute([$user['id'], $like, $like]);
    } else {
        $stmt = $pdo->prepare('
            SELECT c.*, COUNT(DISTINCT e.id) AS enrolled_count,
                   cat.name AS category_name, ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(DISTINCT r.id) AS rating_count
            FROM courses c
            LEFT JOIN enrollments e ON e.course_id = c.id
            LEFT JOIN categories cat ON cat.id = c.category_id
            LEFT JOIN ratings r ON r.course_id = c.id
            WHERE c.created_by = ?
            GROUP BY c.id ORDER BY c.created_at DESC
        ');
        $stmt->execute([$user['id']]);
    }
}

$courses      = $stmt->fetchAll();
$totalCourses = count($courses);

if ($isAdmin) {
    $totalEnrollments = (int) $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
    $totalStudents    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
} else {
    $totalEnrollments = (int) array_sum(array_column($courses, 'enrolled_count'));
    $stmtS = $pdo->prepare('SELECT COUNT(DISTINCT e.user_id) FROM enrollments e INNER JOIN courses c ON c.id = e.course_id WHERE c.created_by = ?');
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

    <form method="GET" class="mb-6">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input class="form-input pl-11 pr-10" type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher par titre ou description…">
            <?php if ($search !== ''): ?>
                <a href="/projet/admin/courses.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($search !== ''): ?>
        <p class="text-sm text-slate-400 mb-4"><?= count($courses) ?> résultat<?= count($courses) !== 1 ? 's' : '' ?> pour <span class="text-slate-200 font-medium">"<?= e($search) ?>"</span></p>
    <?php endif; ?>

    <?php if (!$courses): ?>
        <div class="text-center py-12">
            <?php if ($search !== ''): ?>
                <p class="text-lg font-semibold text-slate-300 mb-1">Aucun cours trouvé</p>
                <p class="text-sm text-slate-500 mb-4">Aucun cours ne correspond à "<?= e($search) ?>".</p>
                <a href="/projet/admin/courses.php" class="btn-secondary btn-sm">Voir tous les cours</a>
            <?php else: ?>
                <p class="text-lg font-semibold text-slate-300 mb-1">Aucun cours créé</p>
                <p class="text-sm text-slate-500">Commencez par ajouter votre premier cours.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto -mx-6 px-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Titre</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Catégorie</th>
                        <?php if ($isAdmin): ?>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Professeur</th>
                        <?php endif; ?>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Note</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Inscrits</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05]">
                    <?php foreach ($courses as $course): ?>
                        <tr class="hover:bg-white/[0.03] transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-200"><?= e($course['title']) ?></span>
                                <p class="text-xs text-slate-500 truncate max-w-[180px] mt-0.5"><?= e($course['description']) ?></p>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php if ($course['category_name']): ?>
                                    <span class="tag"><?= e($course['category_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-slate-600 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($isAdmin): ?>
                            <td class="py-3.5 px-4 text-slate-400 text-xs"><?= $course['prof_name'] ? e($course['prof_name']) : '<span class="text-slate-600">—</span>' ?></td>
                            <?php endif; ?>
                            <td class="py-3.5 px-4">
                                <?php if ($course['avg_rating']): ?>
                                    <span class="flex items-center gap-1 text-yellow-400 text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <?= number_format((float) $course['avg_rating'], 1) ?>
                                        <span class="text-slate-500 font-normal">(<?= (int) $course['rating_count'] ?>)</span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-600 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4"><span class="tag"><?= (int) $course['enrolled_count'] ?> inscrits</span></td>
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
                                    <a class="btn-danger btn-sm" href="/projet/admin/delete_course.php?id=<?= (int) $course['id'] ?>" onclick="return confirm('Supprimer ce cours ?')">Supprimer</a>
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
