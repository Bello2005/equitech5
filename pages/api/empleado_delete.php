<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
requireLogin();
$user = getCurrentUser();

// Solo admin puede eliminar empleados
if ($user['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para eliminar empleados']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)($data['id'] ?? 0);

if ($id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de empleado requerido']);
    exit;
}

try {
    $conn = getConnection();

    // Obtener información del empleado antes de eliminarlo para el registro
    $stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $empleado = $result->fetch_assoc();
    $stmt->close();

    if (!$empleado) {
        http_response_code(404);
        echo json_encode(['error' => 'Empleado no encontrado']);
        $conn->close();
        exit;
    }

    // Eliminar empleado (en este caso, lo marcamos como inactivo)
    $stmt = $conn->prepare("UPDATE usuarios SET activo = 0, fecha_actualizacion = NOW() WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conn->error);
    }

    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        throw new Exception('Error al eliminar empleado: ' . $stmt->error);
    }

    $stmt->close();

    // Registrar actividad
    $desc = "Eliminó empleado: {$empleado['nombre']} ({$empleado['email']})";
    $stmt = $conn->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion) VALUES (?, 'aprobacion', ?)");
    if ($stmt) {
        $stmt->bind_param('is', $user['id'], $desc);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Empleado eliminado exitosamente'
    ]);

} catch (Exception $e) {
    error_log("Error en empleado_delete.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor',
        'message' => $e->getMessage()
    ]);
    if (isset($conn)) {
        $conn->close();
    }
}