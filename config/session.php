<?php
// Cargar variables de entorno
require_once __DIR__ . '/env.php';

// Configuración de sesión con variables de entorno
if (session_status() === PHP_SESSION_NONE) {
    // Configurar parámetros de sesión basados en ENV
    ini_set('session.cookie_httponly', env('SESSION_HTTP_ONLY', true) ? '1' : '0');
    ini_set('session.cookie_secure', env('SESSION_SECURE', false) ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');

    // Tiempo de vida de la sesión
    ini_set('session.gc_maxlifetime', env('SESSION_LIFETIME', 7200));

    session_start();

    // Regenerar ID de sesión periódicamente para seguridad
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Función para verificar si el usuario está autenticado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Función para requerir autenticación
function requireLogin() {
    if (!isLoggedIn()) {
        // Usar APP_URL de .env (si está configurado) o fallback con la ruta correcta
        $app_url = env('APP_URL', '/Comfachoco');
        header("Location: {$app_url}/index.php");
        exit();
    }
}

// Función para obtener datos del usuario actual
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nombre' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'rol' => $_SESSION['user_role'] ?? '',
        'avatar' => $_SESSION['user_avatar'] ?? '',
        'empresa' => $_SESSION['user_empresa'] ?? env('APP_NAME', 'ComfaChoco International')
    ];
}

// Función para cerrar sesión
function logout() {
    // Limpiar todas las variables de sesión
    $_SESSION = [];

    // Destruir la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destruir la sesión
    session_destroy();

    $app_url = env('APP_URL', '/equitech5');
    header("Location: {$app_url}/index.php");
    exit();
}
