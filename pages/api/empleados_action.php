<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar autenticación
requireLogin();

// Obtener datos de la petición
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$response = ['success' => false];

try {
    $conn = getConnection();
    
    // Verificar la acción solicitada
    switch ($input['action'] ?? '') {
        case 'create':
            // Validar datos requeridos
            if (empty($input['nombre']) || empty($input['email']) || empty($input['rol']) || empty($input['departamento_id'])) {
                throw new Exception('Faltan datos requeridos');
            }
            
            $sql = "INSERT INTO usuarios (nombre, email, rol, departamento_id, estado, fecha_creacion) VALUES (?, ?, ?, ?, 'activo', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $input['nombre'], $input['email'], $input['rol'], $input['departamento_id']);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'id' => $conn->insert_id];
            } else {
                throw new Exception('Error al crear el empleado');
            }
            break;
            
        case 'update':
            // Validar ID
            if (empty($input['id'])) {
                throw new Exception('ID de empleado no proporcionado');
            }
            
            $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ?, departamento_id = ?, telefono = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssisi', $input['nombre'], $input['email'], $input['rol'], $input['departamento_id'], $input['telefono'], $input['id']);
            
            if ($stmt->execute()) {
                $response = ['success' => true];
            } else {
                throw new Exception('Error al actualizar el empleado');
            }
            break;
            
        case 'toggle_estado':
            // Validar ID
            if (empty($input['id'])) {
                throw new Exception('ID de empleado no proporcionado');
            }
            
            $sql = "UPDATE usuarios SET estado = IF(estado = 'activo', 'inactivo', 'activo') WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $input['id']);
            
            if ($stmt->execute()) {
                $response = ['success' => true];
            } else {
                throw new Exception('Error al cambiar el estado del empleado');
            }
            break;
            
        case 'update_permisos':
            // Validar datos requeridos
            if (empty($input['id']) || !isset($input['permisos'])) {
                throw new Exception('Datos de permisos incompletos');
            }
            
            // Primero eliminar permisos existentes
            $sql = "DELETE FROM usuario_permisos WHERE usuario_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $input['id']);
            $stmt->execute();
            
            // Insertar nuevos permisos
            if (!empty($input['permisos'])) {
                $sql = "INSERT INTO usuario_permisos (usuario_id, permiso_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                foreach ($input['permisos'] as $permiso_id) {
                    $stmt->bind_param('ii', $input['id'], $permiso_id);
                    $stmt->execute();
                }
            }
            
            $response = ['success' => true];
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
    $conn->close();
} catch (Exception $e) {
    http_response_code(400);
    $response = ['error' => $e->getMessage()];
}

// Enviar respuesta
header('Content-Type: application/json');
echo json_encode($response);