<?php
require_once __DIR__ . '/../config/database.php';

// Cambia esta contraseña si prefieres otra
$newPassword = 'admin123';

try {
    $conn = getConnection();

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = 'admin@comfachoco.com'");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('s', $hashed);
    $ok = $stmt->execute();

    if ($ok) {
        echo "Contraseña del admin actualizada correctamente. Email: admin@comfachoco.com\n";
    } else {
        echo "Error al ejecutar la actualización: " . $stmt->error . "\n";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage() . "\n";
    exit(1);
}

?>