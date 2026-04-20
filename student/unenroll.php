<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$courseId = (int) ($_GET['id'] ?? 0);
$userId = (int) currentUser()['id'];

$stmt = $pdo->prepare('DELETE FROM enrollments WHERE user_id = ? AND course_id = ?');
$stmt->execute([$userId, $courseId]);

setFlash('success', 'Désinscription effectuée.');
redirect('/projet/student/courses.php');
