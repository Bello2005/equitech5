<?php
/**
 * Archivo de cierre de sesión
 * Destruye la sesión del usuario y redirige al login
 */

session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: ../pages/login.php');
exit;
