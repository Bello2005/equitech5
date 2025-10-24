<?php
require_once __DIR__ . '/config/session.php';

// Si el usuario ya está autenticado, redirigir al dashboard según su rol
if (isLoggedIn()) {
    $usuario = getCurrentUser();
    $rol = $usuario['rol'] ?? 'empleado';
    
    if ($rol === 'empleado') {
        header('Location: pages/empleado_dashboard.php');
    } else {
        header('Location: pages/dashboard.php');
    }
    exit();
}

// Si no está autenticado, redirigir al login
header('Location: pages/login.php');
exit();
