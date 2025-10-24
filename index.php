<?php
require_once __DIR__ . '/config/session.php';

// Si el usuario ya está autenticado, redirigir al dashboard
if (isLoggedIn()) {
    // Usar la ruta correcta (respeta mayúsculas del folder)
    header('Location: /Comfachoco/pages/dashboard.php');
    exit();
}

// Si no está autenticado, redirigir al login
header('Location: /Comfachoco/pages/login.php');
exit();
