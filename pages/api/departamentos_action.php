<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Lista completa de departamentos
        $sql = "SELECT id, nombre, descripcion, fecha_creacion FROM departamentos ORDER BY nombre ASC";
        $res = $conn->query($sql);
        if (!$res) throw new Exception('Error al consultar departamentos');

        $departamentos = [];
        while ($row = $res->fetch_assoc()) {
            $departamentos[] = $row;
        }

        echo json_encode(['success' => true, 'departamentos' => $departamentos]);
        exit;
    }

    // Para crear/editar/eliminar departamentos necesitas estar logueado
    requireLogin();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        switch ($action) {
            case 'create':
                if (empty($input['nombre'])) throw new Exception('Nombre requerido');
                
                $stmt = $conn->prepare("INSERT INTO departamentos (nombre, descripcion) VALUES (?, ?)");
                if (!$stmt) throw new Exception('Error preparando consulta');
                
                $stmt->bind_param('ss', $input['nombre'], $input['descripcion']);
                if (!$stmt->execute()) throw new Exception('Error al crear departamento');
                
                echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
                break;

            case 'update':
                if (empty($input['id'])) throw new Exception('ID requerido');
                if (empty($input['nombre'])) throw new Exception('Nombre requerido');
                
                $stmt = $conn->prepare("UPDATE departamentos SET nombre = ?, descripcion = ? WHERE id = ?");
                if (!$stmt) throw new Exception('Error preparando consulta');
                
                $stmt->bind_param('ssi', $input['nombre'], $input['descripcion'], $input['id']);
                if (!$stmt->execute()) throw new Exception('Error al actualizar departamento');
                
                echo json_encode(['success' => true]);
                break;

            case 'delete':
                if (empty($input['id'])) throw new Exception('ID requerido');
                
                // Primero verificar que no tenga tipos de empleo asociados
                $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM job_types WHERE departamento_id = ?");
                if ($stmt) {
                    $stmt->bind_param('i', $input['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    if ((int)$row['cnt'] > 0) {
                        throw new Exception('No se puede eliminar: hay tipos de empleo asociados');
                    }
                }
                
                $stmt = $conn->prepare("DELETE FROM departamentos WHERE id = ?");
                if (!$stmt) throw new Exception('Error preparando consulta');
                
                $stmt->bind_param('i', $input['id']);
                if (!$stmt->execute()) throw new Exception('Error al eliminar departamento');
                
                echo json_encode(['success' => true]);
                break;

            default:
                throw new Exception('Acción no válida');
        }
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}