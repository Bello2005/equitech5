<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

// Función de utilidad para error 500
function error500($msg = '') {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => $msg ?: 'Error interno del servidor']));
}

// Función de utilidad para error 400
function error400($msg = '') {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => $msg ?: 'Solicitud inválida']));
}

// Función para validar que la tabla existe
function asegurarTabla($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS job_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        error500('Error al crear tabla job_types');
    }
}

header('Content-Type: application/json');

try {
    $conn = getConnection();
    asegurarTabla($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!empty($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM job_types WHERE id = ? LIMIT 1");
            if (!$stmt) error500();
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) error500();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if (!$row) error400('Tipo de empleo no encontrado');
            die(json_encode(['success' => true, 'job_type' => $row]));
        }

        $res = $conn->query("SELECT * FROM job_types ORDER BY nombre ASC");
        if (!$res) error500();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        die(json_encode(['success' => true, 'job_types' => $rows]));
    }

    // POST actions (crear/actualizar/eliminar)
    requireLogin();
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'create':
            if (empty($input['nombre'])) error400('Nombre es requerido');
            
            $stmt = $conn->prepare("INSERT INTO job_types (nombre, descripcion) VALUES (?, ?)");
            if (!$stmt) error500();
            
            $nombre = trim($input['nombre']);
            $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : null;
            $stmt->bind_param('ss', $nombre, $descripcion);
            
            if (!$stmt->execute()) error500();
            $id = $stmt->insert_id;
            $stmt->close();
            
            die(json_encode(['success' => true, 'id' => $id]));
            break;
            
        case 'update':
            if (empty($input['id'])) error400('ID es requerido');
            if (empty($input['nombre'])) error400('Nombre es requerido');
            
            $stmt = $conn->prepare("UPDATE job_types SET nombre = ?, descripcion = ?, fecha_actualizacion = NOW() WHERE id = ?");
            if (!$stmt) error500();
            
            $id = (int)$input['id'];
            $nombre = trim($input['nombre']);
            $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : null;
            $stmt->bind_param('ssi', $nombre, $descripcion, $id);
            
            if (!$stmt->execute()) error500();
            if ($stmt->affected_rows === 0) error400('Tipo de empleo no encontrado');
            $stmt->close();
            
            die(json_encode(['success' => true]));
            break;
            
        case 'delete':
            if (empty($input['id'])) error400('ID es requerido');
            
            // Primero verificar que no esté en uso por empleados
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM usuarios WHERE job_type_id = ?");
            if ($stmt) {
                $id = (int)$input['id'];
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                
                if ((int)$row['cnt'] > 0) {
                    error400('No se puede eliminar: hay empleados usando este tipo');
                }
            }
            
            $stmt = $conn->prepare("DELETE FROM job_types WHERE id = ?");
            if (!$stmt) error500();
            
            $id = (int)$input['id'];
            $stmt->bind_param('i', $id);
            
            if (!$stmt->execute()) error500();
            if ($stmt->affected_rows === 0) error400('Tipo de empleo no encontrado');
            $stmt->close();
            
            die(json_encode(['success' => true]));
            break;
            
        default:
            error400('Acción no válida');
    }

    $conn->close();
} catch (Exception $e) {
    error500($e->getMessage());
}
