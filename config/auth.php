<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';

// Función para autenticar usuario
function authenticate($email, $password) {
    $conn = getConnection();

    // Preparar consulta
    $stmt = $conn->prepare("SELECT id, nombre, email, password, rol, avatar FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            // Crear sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['user_avatar'] = $user['avatar'];
            $_SESSION['user_empresa'] = 'ComfaChoco International';

            $stmt->close();
            $conn->close();
            return true;
        }
    }

    $stmt->close();
    $conn->close();
    return false;
}

// Función para registrar usuario (opcional)
function registerUser($nombre, $email, $password, $rol = 'empleado') {
    $conn = getConnection();

    // Verificar si el email ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return false;
    }

    // Encriptar contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol, activo, fecha_creacion) VALUES (?, ?, ?, ?, 1, NOW())");
    $stmt->bind_param("ssss", $nombre, $email, $hashedPassword, $rol);

    $success = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $success;
}
?>
