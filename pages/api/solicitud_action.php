<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');
requireLogin();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id = isset($input['id']) ? (int)$input['id'] : 0;
$action = $input['action'] ?? '';

if (!$id || !in_array($action, ['approve','reject'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

try {
    $conn = getConnection();

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE solicitudes SET estado = 'aprobado', aprobador_id = ?, fecha_aprobacion = NOW() WHERE id = ?");
        $stmt->bind_param('ii', $user['id'], $id);
        $stmt->execute();
        $stmt->close();

        // Insertar actividad
        $desc = 'Aprobó solicitud ID ' . $id;
        $stmt = $conn->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion) VALUES (?, 'aprobacion', ?) ");
        $stmt->bind_param('is', $user['id'], $desc);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Solicitud aprobada']);
        exit;
    }

    if ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE solicitudes SET estado = 'rechazado', aprobador_id = ?, fecha_aprobacion = NOW() WHERE id = ?");
        $stmt->bind_param('ii', $user['id'], $id);
        $stmt->execute();
        $stmt->close();

        $desc = 'Rechazó solicitud ID ' . $id;
        $stmt = $conn->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion) VALUES (?, 'aprobacion', ?) ");
        $stmt->bind_param('is', $user['id'], $desc);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Solicitud rechazada']);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

?>
