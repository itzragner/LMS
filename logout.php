<?php
require_once __DIR__ . '/includes/functions.php';
session_unset();
session_destroy();
session_start();
setFlash('success', 'Déconnexion réussie.');
redirect('/projet/login.php');
