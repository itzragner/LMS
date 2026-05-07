<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/projet/student/dashboard.php');
}

$userId   = currentUser()['id'];
$courseId = (int) ($_POST['course_id'] ?? 0);
$rating   = (int) ($_POST['rating'] ?? 0);

if ($rating < 1 || $rating > 5) {
    setFlash('danger', 'Note invalide.');
    redirect("/projet/student/course.php?id=$courseId");
}

$stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?');
$stmt->execute([$courseId, $userId]);
if (!$stmt->fetch()) {
    setFlash('danger', 'Vous devez être inscrit pour noter ce cours.');
    redirect('/projet/student/courses.php');
}

$stmt = $pdo->prepare('INSERT INTO ratings (course_id, user_id, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating)');
$stmt->execute([$courseId, $userId, $rating]);

setFlash('success', 'Votre note a été enregistrée.');
redirect("/projet/student/course.php?id=$courseId");
