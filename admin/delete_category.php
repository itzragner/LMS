<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
$stmt->execute([$id]);

if (!$stmt->fetch()) {
    setFlash('danger', 'Catégorie introuvable.');
    redirect('/projet/admin/categories.php');
}

$pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);

setFlash('success', 'Catégorie supprimée. Les cours associés ont été désassignés.');
redirect('/projet/admin/categories.php');
