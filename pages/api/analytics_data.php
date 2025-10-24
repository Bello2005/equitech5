<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

try {
    $conn = getConnection();

    // Total usuarios
    $res = $conn->query("SELECT COUNT(*) AS total_usuarios FROM usuarios");
    $totalUsuarios = $res->fetch_assoc()['total_usuarios'] ?? 0;

    // Solicitudes por estado
    $res = $conn->query("SELECT estado, COUNT(*) AS cnt FROM solicitudes GROUP BY estado");
    $solicitudes = ['pendiente' => 0, 'aprobado' => 0, 'rechazado' => 0];
    while ($row = $res->fetch_assoc()) {
        $solicitudes[$row['estado']] = (int)$row['cnt'];
    }

    $totalSolicitudes = array_sum($solicitudes);

    // Productividad simple: porcentaje de solicitudes aprobadas
    $productividad = $totalSolicitudes > 0 ? round(($solicitudes['aprobado'] / $totalSolicitudes) * 100, 1) : null;

    // Próximos eventos
    $res = $conn->query("SELECT titulo, fecha, tipo FROM eventos WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 5");
    $eventos = [];
    while ($row = $res->fetch_assoc()) {
        $eventos[] = $row;
    }

    // Actividad reciente
    $res = $conn->query("SELECT a.descripcion AS accion, u.nombre AS usuario, a.tipo, a.fecha_creacion FROM actividad a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.fecha_creacion DESC LIMIT 5");
    $actividad = [];
    while ($row = $res->fetch_assoc()) {
        $actividad[] = $row;
    }

    $payload = [
        'timestamp' => date('c'),
        'total_usuarios' => (int)$totalUsuarios,
        'solicitudes' => $solicitudes,
        'total_solicitudes' => (int)$totalSolicitudes,
        'productividad' => $productividad,
        'eventos_proximos' => $eventos,
        'actividad_reciente' => $actividad
    ];

    echo json_encode($payload);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>
