<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/projet/student/dashboard.php');
}

$userId   = currentUser()['id'];
$lessonId = (int) ($_POST['lesson_id'] ?? 0);

$stmt = $pdo->prepare('SELECT l.*, c.id AS course_id FROM lessons l INNER JOIN courses c ON c.id = l.course_id WHERE l.id = ?');
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    setFlash('danger', 'Leçon introuvable.');
    redirect('/projet/student/dashboard.php');
}

$stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE course_id = ? AND user_id = ?');
$stmt->execute([$lesson['course_id'], $userId]);
if (!$stmt->fetch()) {
    setFlash('danger', 'Accès non autorisé.');
    redirect('/projet/student/courses.php');
}

$stmt = $pdo->prepare('SELECT 1 FROM lesson_completions WHERE lesson_id = ? AND user_id = ?');
$stmt->execute([$lessonId, $userId]);

if ($stmt->fetch()) {
    $pdo->prepare('DELETE FROM lesson_completions WHERE lesson_id = ? AND user_id = ?')->execute([$lessonId, $userId]);
} else {
    $pdo->prepare('INSERT INTO lesson_completions (lesson_id, user_id) VALUES (?, ?)')->execute([$lessonId, $userId]);
}

redirect("/projet/student/lesson.php?id=$lessonId");
