<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$userId = currentUser()['id'];
$stmt = $pdo->prepare('SELECT c.*, EXISTS(SELECT 1 FROM enrollments e WHERE e.course_id = c.id AND e.user_id = ?) AS enrolled FROM courses c ORDER BY c.created_at DESC');
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <p class="eyebrow mb-1">Catalogue</p>
            <h2 class="text-xl font-bold text-slate-100">Cours disponibles</h2>
            <p class="text-sm text-slate-400 mt-1">Explorez les cours et inscrivez-vous en un clic.</p>
        </div>
        <a class="btn-secondary shrink-0" href="/projet/student/dashboard.php">Retour dashboard</a>
    </div>

    <?php if (!$courses): ?>
        <div class="text-center py-12">
            <p class="text-lg font-semibold text-slate-300 mb-1">Aucun cours disponible</p>
            <p class="text-sm text-slate-500">L'administrateur n'a pas encore ajouté de cours.</p>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 gap-4">
            <?php foreach ($courses as $course): ?>
                <?php $enrolled = (int) $course['enrolled'] === 1; ?>
                <article class="flex flex-col gap-3 p-5 rounded-xl border border-white/[0.08] bg-white/[0.03] hover:bg-white/[0.05] transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="eyebrow mb-0.5">Cours</p>
                            <h3 class="font-bold text-slate-200"><?= e($course['title']) ?></h3>
                        </div>
                        <?php if ($enrolled): ?>
                            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-violet-400/10 text-violet-300 border border-violet-400/20">Déjà inscrit</span>
                        <?php else: ?>
                            <span class="tag shrink-0">Disponible</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-400 flex-1"><?= e($course['description']) ?></p>
                    <div>
                        <?php if ($enrolled): ?>
                            <a class="btn-danger btn-sm" href="/projet/student/unenroll.php?id=<?= (int) $course['id'] ?>">Se désinscrire</a>
                        <?php else: ?>
                            <a class="btn btn-sm" href="/projet/student/enroll.php?id=<?= (int) $course['id'] ?>">S'inscrire</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
