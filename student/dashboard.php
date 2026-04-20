<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$user = currentUser();
$stmt = $pdo->prepare('SELECT c.title, c.description, e.enrolled_at FROM enrollments e INNER JOIN courses c ON c.id = e.course_id WHERE e.user_id = ? ORDER BY e.enrolled_at DESC');
$stmt->execute([$user['id']]);
$enrollments = $stmt->fetchAll();
$totalCourses = count($enrollments);
$allCourses = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <span class="eyebrow">Bienvenue</span>
    <h2><?= e($user['name']) ?>, voici votre progression.</h2>
    <p>Consultez rapidement vos inscriptions et explorez de nouveaux cours depuis votre espace personnel.</p>
</section>

<section class="metric-row">
    <article class="metric-box">
        <span class="eyebrow">Mes cours</span>
        <strong><?= $totalCourses ?></strong>
        <p>Cours auxquels vous êtes inscrit.</p>
    </article>
    <article class="metric-box">
        <span class="eyebrow">Catalogue total</span>
        <strong><?= $allCourses ?></strong>
        <p>Nombre total de cours disponibles.</p>
    </article>
    <article class="metric-box">
        <span class="eyebrow">Action</span>
        <strong>Explorer</strong>
        <p>Découvrez de nouveaux contenus à rejoindre.</p>
    </article>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <span class="eyebrow">Mes inscriptions</span>
            <h2>Cours inscrits</h2>
        </div>
        <a class="btn" href="/projet/student/courses.php">Explorer les cours</a>
    </div>

    <?php if (!$enrollments): ?>
        <div class="empty-state">
            <h3>Aucune inscription pour le moment</h3>
            <p>Commencez par consulter le catalogue et inscrivez-vous à votre premier cours.</p>
        </div>
    <?php else: ?>
        <div class="course-list">
            <?php foreach ($enrollments as $course): ?>
                <article class="course-card">
                    <div class="course-card-top">
                        <div>
                            <h3><?= e($course['title']) ?></h3>
                            <p><?= e($course['description']) ?></p>
                        </div>
                        <span class="tag">Inscrit</span>
                    </div>
                    <p>Ajouté à votre tableau de bord le <?= e($course['enrolled_at']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
