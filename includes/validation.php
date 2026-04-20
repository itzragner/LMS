<?php
function validateRegistration(array $data): array
{
    $errors = [];

    if (empty(trim($data['name'] ?? ''))) {
        $errors[] = 'Le nom est obligatoire.';
    }

    if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    }

    if (strlen($data['password'] ?? '') < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }

    return $errors;
}

function validateCourse(array $data): array
{
    $errors = [];

    if (empty(trim($data['title'] ?? ''))) {
        $errors[] = 'Le titre du cours est obligatoire.';
    }

    if (empty(trim($data['description'] ?? ''))) {
        $errors[] = 'La description du cours est obligatoire.';
    }

    return $errors;
}
