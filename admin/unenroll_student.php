<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$courseId = (int) ($_GET['course_id'] ?? 0);
$userId   = (int) ($_GET['user_id']   ?? 0);

if (!$courseId || !$userId) {
    setFlash('danger', 'Paramètres invalides.');
    redirect('/projet/admin/courses.php');
}

$stmt = $pdo->prepare('DELETE FROM enrollments WHERE course_id = ? AND user_id = ?');
$stmt->execute([$courseId, $userId]);

if ($stmt->rowCount() > 0) {
    setFlash('success', 'Étudiant désinscrit avec succès.');
} else {
    setFlash('warning', 'Inscription introuvable.');
}

redirect('/projet/admin/course_students.php?id=' . $courseId);
