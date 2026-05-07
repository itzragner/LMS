<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin', 'prof']);

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT l.*, c.created_by FROM lessons l INNER JOIN courses c ON c.id = l.course_id WHERE l.id = ?');
$stmt->execute([$id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    setFlash('danger', 'Leçon introuvable.');
    redirect('/projet/admin/courses.php');
}

requireCourseOwner(['id' => $lesson['course_id'], 'created_by' => $lesson['created_by']]);

$courseId = (int) $lesson['course_id'];

$pdo->prepare('DELETE FROM lessons WHERE id = ?')->execute([$id]);

setFlash('success', 'Leçon supprimée avec succès.');
redirect("/projet/admin/lessons.php?course_id=$courseId");
