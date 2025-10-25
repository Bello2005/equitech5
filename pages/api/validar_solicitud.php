<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/validaciones_permisos.php';

// Log de depuración
error_log('[validar_solicitud] Request iniciada: ' . file_get_contents('php://input'));
error_log('[validar_solicitud] Session: ' . print_r($_SESSION, true));

// Verificar que el usuario esté autenticado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'error' => 'No autenticado',
        'code' => 'NOT_AUTHENTICATED',
        'mensaje' => 'Debe iniciar sesión para acceder a este recurso'
    ]);
    exit;
}

$usuario = getCurrentUser();
if (!$usuario || !$usuario['id']) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Sesión inválida',
        'code' => 'INVALID_SESSION',
        'mensaje' => 'La sesión no contiene información de usuario válida'
    ]);
    exit;
}

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Datos inválidos',
        'code' => 'INVALID_REQUEST_DATA',
        'mensaje' => 'El cuerpo de la petición debe ser JSON válido'
    ]);
    exit;
}

// Preparar datos para validación
$datos_solicitud = [
    'usuario_id' => $usuario['id'],
    'tipo_permiso' => $data['tipo'] ?? '',
    'fecha_inicio' => $data['fecha_inicio'] ?? '',
    'fecha_fin' => $data['fecha_fin'] ?? '',
    'motivo' => $data['motivo'] ?? '',
    'tiene_documento' => !empty($data['tiene_documento'])
];

try {
    error_log("[validar_solicitud] Conectando a BD...");
    $conn = getConnection();
    error_log("[validar_solicitud] Conexión exitosa");

    // Obtener información adicional del usuario
    error_log("[validar_solicitud] Buscando usuario_id: " . $usuario['id']);
    $sql_usuario = "SELECT u.*, te.nombre as tipo_empleado_nombre, u.departamento
                    FROM usuarios u
                    LEFT JOIN tipos_empleado te ON te.id = u.tipo_empleado_id
                    WHERE u.id = ?";
    $stmt = $conn->prepare($sql_usuario);
    $stmt->bind_param('i', $usuario['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario_completo = $result->fetch_assoc();

    if (!$usuario_completo) {
        // Si el usuario en sesión no se encuentra en la base de datos,
        // devolver 404 en vez de lanzar una excepción que produce 500.
        http_response_code(404);
        echo json_encode([
            'error' => 'Usuario no encontrado',
            'code' => 'USER_NOT_FOUND',
            'mensaje' => 'El usuario en sesión no existe en la base de datos'
        ]);
        $conn->close();
        exit;
    }

    // Agregar departamento a los datos
    $datos_solicitud['departamento'] = $usuario_completo['departamento'] ?? 'General';

    // Realizar validación completa
    $validacion = validarSolicitudCompleta($datos_solicitud);

    // Calcular días disponibles
    $dias_disponibles_info = validarDiasDisponibles($usuario['id'], 0);

    // Obtener información de disponibilidad del departamento si es vacaciones y hay fechas
    $disponibilidad_dept = null;
    if ($datos_solicitud['tipo_permiso'] === 'vacaciones') {
        if (empty($datos_solicitud['fecha_inicio']) || empty($datos_solicitud['fecha_fin'])) {
            $disponibilidad_dept = [
                'valido' => true,
                'mensaje' => 'Pendiente seleccionar fechas',
                'total_empleados' => 0,
                'disponibles' => 0,
                'porcentaje' => 0
            ];
        } else {
            $disponibilidad_dept = validarDisponibilidadDepartamento(
                $datos_solicitud['departamento'],
                $datos_solicitud['fecha_inicio'],
                $datos_solicitud['fecha_fin'],
                $usuario['id']
            );
        }
    }

    $conn->close();

    // Preparar respuesta
    $response = [
        'valido' => empty($validacion['errores']),
        'errores' => $validacion['errores'] ?? [],
        'warnings' => $validacion['warnings'] ?? [],
        'dias_habiles' => $validacion['dias_habiles'] ?? 0,
        'aprobador_rol' => $validacion['aprobador_rol'] ?? null,
        'requiere_documento' => $validacion['requiere_documento'] ?? false,
        'tipo_documento' => $validacion['tipo_documento'] ?? null,
        'dias_disponibles' => [
            'total' => $dias_disponibles_info['dias_disponibles'] ?? 0,
            'ganados' => $dias_disponibles_info['dias_ganados'] ?? 0,
            'usados' => $dias_disponibles_info['dias_usados'] ?? 0,
            'periodos_completos' => $dias_disponibles_info['periodos_completos'] ?? 0
        ],
        'disponibilidad_departamento' => $disponibilidad_dept
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Log para depuración en servidor
    error_log('[validar_solicitud] Exception: ' . $e->getMessage());
    if (method_exists($e, 'getTraceAsString')) {
        error_log('[validar_solicitud] Trace: ' . $e->getTraceAsString());
    }

    http_response_code(500);
    $payload = [
        'error' => 'Error en validación',
        'code' => 'VALIDATION_ERROR',
        'mensaje' => $e->getMessage()
    ];

    // Incluir traza en la respuesta únicamente en modo debug
    if (function_exists('env') && env('APP_DEBUG', false)) {
        $payload['trace'] = $e->getTraceAsString();
    }

    echo json_encode($payload, JSON_PRETTY_PRINT);
}
