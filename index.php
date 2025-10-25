<?php
require_once __DIR__ . '/config/session.php';

// Si el usuario ya está autenticado, redirigir al dashboard según su rol
if (isLoggedIn()) {
    // Usar la ruta correcta (respeta mayúsculas del folder)
    header('Location: /Comfachoco/pages/dashboard.php');
    exit();
}

// Si no está autenticado, redirigir al login
header('Location: pages/login.php');
exit();
