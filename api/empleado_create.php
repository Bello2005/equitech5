<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

try {
    // Verificar si es una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener y validar datos
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }

    $required_fields = ['cedula', 'nombre', 'email', 'password', 'rol', 'tipo_empleado_id', 'departamento'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo {$field} es requerido");
        }
    }

    // Validar que el email no exista
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("El email ya está registrado");
    }

    // Validar que la cédula no exista
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ?");
    $stmt->bind_param("s", $data['cedula']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("La cédula ya está registrada");
    }

    // Separar nombre en primer nombre y primer apellido
    $nombres = explode(' ', trim($data['nombre']));
    $primer_nombre = $nombres[0];
    $primer_apellido = isset($nombres[1]) ? $nombres[1] : '';

    // Hash de la contraseña
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

    // Preparar la consulta
    $sql = "INSERT INTO usuarios (
        cedula, nombre, email, password, rol, 
        tipo_empleado_id, departamento, fecha_ingreso,
        primer_nombre, primer_apellido, activo
    ) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, 1)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssss",
        $data['cedula'],
        $data['nombre'],
        $data['email'],
        $password_hash,
        $data['rol'],
        $data['tipo_empleado_id'],
        $data['departamento'],
        $primer_nombre,
        $primer_apellido
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al crear el empleado: " . $stmt->error);
    }

    $empleado_id = $conn->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'Empleado creado exitosamente',
        'empleado_id' => $empleado_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}