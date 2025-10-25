<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
requireLogin();
$user = getCurrentUser();

// Solo admin puede actualizar empleados
if ($user['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para actualizar empleados']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario o del JSON
$contentType = $_SERVER["CONTENT_TYPE"] ?? '';
if (strpos($contentType, "application/json") !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)($data['id'] ?? 0);
    $activo = isset($data['activo']) ? (int)$data['activo'] : null;
} else {
    $id = (int)($_POST['id'] ?? 0);
    $cedula = trim($_POST['cedula'] ?? '');
    $primer_nombre = trim($_POST['primer_nombre'] ?? '');
    $primer_apellido = trim($_POST['primer_apellido'] ?? '');
    $tipo_empleado_id = (int)($_POST['tipo_empleado_id'] ?? 0);
    $departamento = trim($_POST['departamento'] ?? '');
}

if ($id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de empleado requerido']);
    exit;
}

try {
    $conn = getConnection();

    // Si solo estamos actualizando el estado activo/inactivo
    if (isset($activo)) {
        $sql = "UPDATE usuarios SET activo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $activo, $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar estado: ' . $stmt->error);
        }

        $stmt->close();
        $conn->close();

        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado exitosamente'
        ]);
        exit;
    }

    // Validaciones para actualización completa
    if (empty($cedula) || empty($primer_nombre) || empty($primer_apellido)) {
        http_response_code(400);
        echo json_encode(['error' => 'Cédula, primer nombre y primer apellido son requeridos']);
        exit;
    }

    if ($tipo_empleado_id === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Debe seleccionar un tipo de empleado']);
        exit;
    }

    // Verificar que la cédula no esté en uso por otro empleado
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ? AND id != ?");
    $stmt->bind_param('si', $cedula, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'La cédula ya está registrada para otro empleado']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Formar nombre completo
    $nombre = $primer_nombre . ' ' . $primer_apellido;

    // Generar nuevo email institucional
    $email = strtolower($primer_nombre . '.' . $primer_apellido . '@comfachoco.com');

    // Actualizar empleado
    $sql = "UPDATE usuarios SET 
            cedula = ?, 
            nombre = ?, 
            primer_nombre = ?, 
            primer_apellido = ?, 
            email = ?,
            tipo_empleado_id = ?, 
            departamento = ?,
            fecha_actualizacion = NOW()
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conn->error);
    }

    $stmt->bind_param('sssssiss',
        $cedula,
        $nombre,
        $primer_nombre,
        $primer_apellido,
        $email,
        $tipo_empleado_id,
        $departamento,
        $id
    );

    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar empleado: ' . $stmt->error);
    }

    $stmt->close();

    // Registrar actividad
    $desc = "Actualizó empleado: {$nombre} ({$email})";
    $stmt = $conn->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion) VALUES (?, 'aprobacion', ?)");
    if ($stmt) {
        $stmt->bind_param('is', $user['id'], $desc);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Empleado actualizado exitosamente'
    ]);

} catch (Exception $e) {
    error_log("Error en empleado_update.php: " . $e->getMessage());
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