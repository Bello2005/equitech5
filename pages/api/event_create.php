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

$title = trim($_POST['title'] ?? '');
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? $fecha_inicio;
$tipo = $_POST['tipo'] ?? 'otro';

if (empty($title) || empty($fecha_inicio)) {
    http_response_code(400);
    echo json_encode(['error' => 'Título y fecha de inicio son requeridos']);
    exit;
}

try {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO eventos (titulo, descripcion, fecha_inicio, fecha_fin, tipo, usuario_id, fecha_creacion) VALUES (?, '', ?, ?, ?, ?, NOW())");
    $stmt->bind_param('ssssi', $title, $fecha_inicio, $fecha_fin, $tipo, $user['id']);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    echo json_encode(['success' => true, 'id' => $id]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

?>
