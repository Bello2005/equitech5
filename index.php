<?php
require_once __DIR__ . '/config/session.php';

// Si el usuario ya está autenticado, redirigir al dashboard
if (isLoggedIn()) {
    header('Location: /comfachoco/pages/dashboard.php');
    exit();
}

// Si no está autenticado, redirigir al login
header('Location: /comfachoco/pages/login.php');
exit();
