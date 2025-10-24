<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';

/**
 * Actualizar perfil de usuario
 */
function updateUserProfile($userId, $nombre, $email) {
    $conn = getConnection();

    try {
        // Verificar si el email ya existe para otro usuario
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'El email ya está en uso por otro usuario'];
        }
        $stmt->close();

        // Actualizar usuario
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $email, $userId);

        if ($stmt->execute()) {
            // Actualizar sesión
            $_SESSION['user_name'] = $nombre;
            $_SESSION['user_email'] = $email;

            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Perfil actualizado exitosamente'];
        }

        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Error al actualizar el perfil'];

    } catch (Exception $e) {
        error_log("Error updating profile: {$e->getMessage()}");
        if (isset($conn)) $conn->close();
        return ['success' => false, 'message' => 'Error al actualizar el perfil'];
    }
}

/**
 * Cambiar contraseña de usuario
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $conn = getConnection();

    try {
        // Verificar contraseña actual
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verificar contraseña actual
        if (!password_verify($currentPassword, $user['password'])) {
            $conn->close();
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
        }

        // Actualizar contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
        }

        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Error al actualizar la contraseña'];

    } catch (Exception $e) {
        error_log("Error changing password: {$e->getMessage()}");
        if (isset($conn)) $conn->close();
        return ['success' => false, 'message' => 'Error al cambiar la contraseña'];
    }
}

/**
 * Actualizar avatar de usuario
 */
function updateUserAvatar($userId, $avatarUrl) {
    $conn = getConnection();

    try {
        $stmt = $conn->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatarUrl, $userId);

        if ($stmt->execute()) {
            // Actualizar sesión
            $_SESSION['user_avatar'] = $avatarUrl;

            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Avatar actualizado exitosamente'];
        }

        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Error al actualizar el avatar'];

    } catch (Exception $e) {
        error_log("Error updating avatar: {$e->getMessage()}");
        if (isset($conn)) $conn->close();
        return ['success' => false, 'message' => 'Error al actualizar el avatar'];
    }
}

/**
 * Validar contraseña segura
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres'];
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'La contraseña debe contener al menos una mayúscula'];
    }

    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'La contraseña debe contener al menos una minúscula'];
    }

    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'La contraseña debe contener al menos un número'];
    }

    return ['valid' => true];
}
?>
