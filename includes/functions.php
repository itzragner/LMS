<?php
require_once 'session.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('/projet/login.php');
    }
}

function requireRole(string|array $roles): void
{
    requireLogin();
    $userRole = currentUser()['role'] ?? null;
    if (!in_array($userRole, (array) $roles, true)) {
        redirect('/projet/index.php');
    }
}

function requireCourseOwner(array $course): void
{
    $user = currentUser();
    if ($user['role'] === 'prof' && (int) $course['created_by'] !== (int) $user['id']) {
        setFlash('danger', 'Vous n\'êtes pas autorisé à accéder à ce cours.');
        redirect('/projet/admin/courses.php');
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
