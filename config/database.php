<?php
// Cargar variables de entorno
require_once __DIR__ . '/env.php';

// Configuración de la base de datos desde variables de entorno
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', 3306));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'comfachoco'));

// Crear conexión
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

        if ($conn->connect_error) {
            throw new Exception("Error de conexión: {$conn->connect_error}");
        }

        $conn->set_charset("utf8mb4");

        // Configurar zona horaria si está definida (formato offset: -05:00 para Colombia)
        $timezone = env('TIMEZONE', '-05:00');
        if (!empty($timezone)) {
            $conn->query("SET time_zone = '{$timezone}'");
        }

        return $conn;
    } catch (Exception $e) {
        // Log del error
        error_log("Database Error: {$e->getMessage()}");

        // Mostrar error solo en desarrollo
        if (env('APP_DEBUG', false)) {
            die("Error al conectar con la base de datos: {$e->getMessage()}");
        }

        die("Error al conectar con la base de datos. Por favor contacte al administrador.");
    }
}
