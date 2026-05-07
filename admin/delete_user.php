<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);

if ($id === (int) currentUser()['id']) {
    setFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
    redirect('/projet/admin/users.php');
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    setFlash('danger', 'Utilisateur introuvable.');
    redirect('/projet/admin/users.php');
}

$pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

setFlash('success', 'Utilisateur supprimé avec succès.');
redirect('/projet/admin/users.php');
