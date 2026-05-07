<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin', 'prof']);

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('danger', 'Cours introuvable.');
    redirect('/projet/admin/courses.php');
}

requireCourseOwner($course);

$stmt = $pdo->prepare('DELETE FROM courses WHERE id = ?');
$stmt->execute([$id]);

setFlash('success', 'Cours supprimé avec succès.');
redirect('/projet/admin/courses.php');
