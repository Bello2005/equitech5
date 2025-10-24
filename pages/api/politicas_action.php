<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

// Permitir CORS solo para desarrollo local (si es necesario) - comentar en producción
// header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Soportar GET para obtener lista o detalle y POST para acciones CRUD
try {
    $conn = getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Si se solicita un ID, devolver detalle
        if (!empty($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT id, nombre, categoria, descripcion, fecha_creacion, fecha_actualizacion FROM politicas WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            echo json_encode(['success' => true, 'politica' => $row]);
            $conn->close();
            exit;
        }

        // Devolver lista completa
        $res = $conn->query("SELECT id, nombre, categoria, descripcion, fecha_creacion, fecha_actualizacion FROM politicas ORDER BY categoria, nombre");
        $list = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) $list[] = $r;
        }
        echo json_encode(['success' => true, 'politicas' => $list]);
        $conn->close();
        exit;
    }

    // Para POST/PUT/DELETE se requiere autenticación
    requireLogin();

    // Obtener datos de la petición JSON
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $response = ['success' => false];

    switch ($input['action'] ?? '') {
        case 'create':
            if (empty($input['nombre']) || empty($input['descripcion']) || empty($input['categoria'])) {
                throw new Exception('Faltan datos requeridos');
            }
            $sql = "INSERT INTO politicas (nombre, descripcion, categoria, fecha_creacion) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $input['nombre'], $input['descripcion'], $input['categoria']);
            if ($stmt->execute()) $response = ['success' => true, 'id' => $conn->insert_id];
            else throw new Exception('Error al crear la política');
            break;

        case 'update':
            if (empty($input['id'])) throw new Exception('ID de política no proporcionado');
            $sql = "UPDATE politicas SET nombre = ?, descripcion = ?, categoria = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $input['nombre'], $input['descripcion'], $input['categoria'], $input['id']);
            if ($stmt->execute()) $response = ['success' => true];
            else throw new Exception('Error al actualizar la política');
            break;

        case 'delete':
            if (empty($input['id'])) throw new Exception('ID de política no proporcionado');
            $sql = "DELETE FROM politicas WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $input['id']);
            if ($stmt->execute()) $response = ['success' => true];
            else throw new Exception('Error al eliminar la política');
            break;

        default:
            throw new Exception('Acción no válida');
    }

    $conn->close();
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}