<?php
require_once __DIR__ . '/config/session.php';

// Si el usuario ya está autenticado, redirigir al dashboard
if (isLoggedIn()) {
    header('Location: /Comfachoco/pages/dashboard.php');
    exit();
}

// Si no está autenticado, redirigir al login
header('Location: /Comfachoco/pages/login.php');
exit();
