<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
requireLogin();
$user = getCurrentUser();

// Solo admin puede crear empleados
if ($user['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para crear empleados']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$rol = $_POST['rol'] ?? 'empleado';
$tipo_empleado_id = (int)($_POST['tipo_empleado_id'] ?? 0);
$departamento = trim($_POST['departamento'] ?? 'General');
$fecha_ingreso = $_POST['fecha_ingreso'] ?? date('Y-m-d');
$password = $_POST['password'] ?? '';

// Validaciones
if (empty($nombre) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre, email y contraseña son requeridos']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email inválido']);
    exit;
}

if ($tipo_empleado_id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Debe seleccionar un tipo de empleado']);
    exit;
}

try {
    $conn = getConnection();

    // Verificar si el email ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'El email ya está registrado']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar nuevo empleado
    $sql = "INSERT INTO usuarios
            (nombre, email, password, rol, tipo_empleado_id, departamento, fecha_ingreso,
             dias_vacaciones_acumulados, periodos_acumulados, activo, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, 1, NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conn->error);
    }

    $stmt->bind_param('ssssis',
        $nombre,
        $email,
        $password_hash,
        $rol,
        $tipo_empleado_id,
        $departamento,
        $fecha_ingreso
    );

    if (!$stmt->execute()) {
        throw new Exception('Error al crear empleado: ' . $stmt->error);
    }

    $empleado_id = $stmt->insert_id;
    $stmt->close();

    // Registrar actividad
    $desc = "Creó nuevo empleado: {$nombre} ({$email})";
    $stmt = $conn->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion) VALUES (?, 'empleado', ?)");
    if ($stmt) {
        $stmt->bind_param('is', $user['id'], $desc);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'id' => $empleado_id,
        'message' => 'Empleado creado exitosamente'
    ]);

} catch (Exception $e) {
    error_log("Error en empleado_create.php: " . $e->getMessage());
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
