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

$tipo = $_POST['tipo'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? $fecha_inicio;
$dias = isset($_POST['dias']) ? (int)$_POST['dias'] : 1;
$motivo = $_POST['motivo'] ?? '';

if (empty($tipo) || empty($fecha_inicio)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo y fecha de inicio son requeridos']);
    exit;
}

try {
    $conn = getConnection();

    // Check if column tipo_permiso_id exists
    $dbName = DB_NAME;
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $conn->real_escape_string($dbName) . "' AND TABLE_NAME = 'solicitudes' AND COLUMN_NAME = 'tipo_permiso_id'");
    $row = $res->fetch_assoc();
    $hasTipoId = $row['cnt'] > 0;

    if ($hasTipoId) {
        // find or create tipos_permisos
        $stmt = $conn->prepare("SELECT id FROM tipos_permisos WHERE nombre = ? LIMIT 1");
        $stmt->bind_param('s', $tipo);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($r) {
            $tipo_id = (int)$r['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO tipos_permisos (nombre, descripcion, activo, fecha_creacion) VALUES (?, '', 1, NOW())");
            $stmt->bind_param('s', $tipo);
            $stmt->execute();
            $tipo_id = $stmt->insert_id;
            $stmt->close();
        }

        $stmt = $conn->prepare("INSERT INTO solicitudes (usuario_id, tipo_permiso_id, fecha_inicio, fecha_fin, dias, motivo, estado, prioridad, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', 'media', NOW())");
        $stmt->bind_param('iissis', $user['id'], $tipo_id, $fecha_inicio, $fecha_fin, $dias, $motivo);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();

    } else {
        // insert using tipo text column
    $stmt = $conn->prepare("INSERT INTO solicitudes (usuario_id, tipo, fecha_inicio, fecha_fin, dias, motivo, estado, prioridad, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', 'media', NOW())");
    $stmt->bind_param('isssis', $user['id'], $tipo, $fecha_inicio, $fecha_fin, $dias, $motivo);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();
    }

    // insert activity
    $desc = 'Envió nueva solicitud ID ' . $insertId;
    $stmt = $conn->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion) VALUES (?, 'solicitud', ?) ");
    $stmt->bind_param('is', $user['id'], $desc);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'id' => $insertId]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

?>
