<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$stmt = $pdo->query('SELECT c.*, COUNT(e.id) AS enrolled_count FROM courses c LEFT JOIN enrollments e ON e.course_id = c.id GROUP BY c.id ORDER BY c.created_at DESC');
$courses = $stmt->fetchAll();
$totalCourses = count($courses);
$totalEnrollments = (int) $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="metric-row">
    <article class="metric-box">
        <span class="eyebrow">Cours</span>
        <strong><?= $totalCourses ?></strong>
        <p>Nombre total de cours publiés.</p>
    </article>
    <article class="metric-box">
        <span class="eyebrow">Étudiants</span>
        <strong><?= $totalStudents ?></strong>
        <p>Utilisateurs étudiants enregistrés.</p>
    </article>
    <article class="metric-box">
        <span class="eyebrow">Inscriptions</span>
        <strong><?= $totalEnrollments ?></strong>
        <p>Inscriptions cumulées sur la plateforme.</p>
    </article>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <span class="eyebrow">Administration</span>
            <h2>Gestion des cours</h2>
            <p class="muted">Ajoutez, modifiez ou supprimez les cours depuis cet espace.</p>
        </div>
        <a class="btn" href="/projet/admin/add_course.php">Ajouter un cours</a>
    </div>

    <?php if (!$courses): ?>
        <div class="empty-state">
            <h3>Aucun cours créé</h3>
            <p>Commencez par ajouter votre premier cours à la plateforme.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Description</th>
                        <th>Inscrits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><strong><?= e($course['title']) ?></strong></td>
                            <td><?= e($course['description']) ?></td>
                            <td><span class="tag"><?= (int) $course['enrolled_count'] ?> inscrits</span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-secondary" href="/projet/admin/edit_course.php?id=<?= (int) $course['id'] ?>">Modifier</a>
                                    <a class="btn btn-danger" href="/projet/admin/delete_course.php?id=<?= (int) $course['id'] ?>" onclick="return confirm('Supprimer ce cours ?')">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
