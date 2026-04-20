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
<section class="card">
    <div class="card-header">
        <div>
            <span class="eyebrow">Catalogue</span>
            <h2>Cours disponibles</h2>
            <p class="muted">Explorez les cours et inscrivez-vous en un clic.</p>
        </div>
        <a class="btn btn-secondary" href="/projet/student/dashboard.php">Retour dashboard</a>
    </div>

    <?php if (!$courses): ?>
        <div class="empty-state">
            <h3>Aucun cours disponible</h3>
            <p>L'administrateur n'a pas encore ajouté de cours.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($courses as $course): ?>
                <article class="course-card">
                    <div class="course-card-top">
                        <div>
                            <span class="eyebrow">Cours</span>
                            <h3><?= e($course['title']) ?></h3>
                        </div>
                        <span class="tag"><?= (int) $course['enrolled'] === 1 ? 'Déjà inscrit' : 'Disponible' ?></span>
                    </div>
                    <p><?= e($course['description']) ?></p>
                    <div class="actions">
                        <?php if ((int) $course['enrolled'] === 1): ?>
                            <a class="btn btn-danger" href="/projet/student/unenroll.php?id=<?= (int) $course['id'] ?>">Se désinscrire</a>
                        <?php else: ?>
                            <a class="btn" href="/projet/student/enroll.php?id=<?= (int) $course['id'] ?>">S'inscrire</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
