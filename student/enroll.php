<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$courseId = (int) ($_GET['id'] ?? 0);
$userId = (int) currentUser()['id'];

$stmt = $pdo->prepare('SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?');
$stmt->execute([$userId, $courseId]);

if (!$stmt->fetch()) {
    $stmt = $pdo->prepare('INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)');
    $stmt->execute([$userId, $courseId]);
    setFlash('success', 'Inscription au cours réussie.');
} else {
    setFlash('warning', 'Vous êtes déjà inscrit à ce cours.');
}

redirect('/projet/student/courses.php');
