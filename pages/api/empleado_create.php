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

$cedula = trim($_POST['cedula'] ?? '');
$primer_nombre = trim($_POST['primer_nombre'] ?? '');
$primer_apellido = trim($_POST['primer_apellido'] ?? '');
$rol = 'empleado'; // Por defecto será empleado
$tipo_empleado_id = (int)($_POST['tipo_empleado_id'] ?? 0);
$departamento = trim($_POST['departamento'] ?? 'General');
$fecha_ingreso = date('Y-m-d'); // Por defecto la fecha actual

// Validaciones básicas
if (empty($cedula) || empty($primer_nombre) || empty($primer_apellido)) {
    http_response_code(400);
    echo json_encode(['error' => 'Cédula, primer nombre y primer apellido son requeridos']);
    exit;
}

if ($tipo_empleado_id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Debe seleccionar un tipo de empleado']);
    exit;
}

try {
    $conn = getConnection();

    // Verificar si la cédula ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ?");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'La cédula ya está registrada']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Generar email institucional
    $email = strtolower($primer_nombre . '.' . $primer_apellido . '@comfachoco.com');

    // Generar contraseña automática
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Formar nombre completo
    $nombre = $primer_nombre . ' ' . $primer_apellido;

    // Insertar nuevo empleado
    $sql = "INSERT INTO usuarios
            (cedula, nombre, primer_nombre, primer_apellido, email, password, rol, tipo_empleado_id, departamento, fecha_ingreso,
             dias_vacaciones_acumulados, periodos_acumulados, activo, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 1, NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conn->error);
    }

    $stmt->bind_param('sssssssiss',
        $cedula,
        $nombre,
        $primer_nombre,
        $primer_apellido,
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
    $stmt = $conn->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion) VALUES (?, 'aprobacion', ?)");
    if ($stmt) {
        $stmt->bind_param('is', $user['id'], $desc);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'id' => $empleado_id,
        'message' => "Empleado creado exitosamente.\nEmail: {$email}\nContraseña temporal: {$password}\nPor favor, comparta estas credenciales con el empleado de forma segura."
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
