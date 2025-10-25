<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/validaciones_permisos.php';

header('Content-Type: application/json; charset=utf-8');
requireLogin();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$tipo = $_POST['tipo'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? $fecha_inicio;
$motivo = $_POST['motivo'] ?? '';

if (empty($tipo) || empty($fecha_inicio) || empty($motivo)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo, fecha de inicio y motivo son requeridos']);
    exit;
}

try {
    $conn = getConnection();

    // Obtener departamento del usuario
    $sql_user = "SELECT departamento FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $departamento = $user_data['departamento'] ?? 'General';
    $stmt->close();

    // Validar solicitud completa
    $datos_validacion = [
        'usuario_id' => $user['id'],
        'tipo_permiso' => $tipo,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'motivo' => $motivo,
        'departamento' => $departamento,
        'tiene_documento' => isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK
    ];

    $validacion = validarSolicitudCompleta($datos_validacion);

    // Si hay errores, retornar
    if (!empty($validacion['errores'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Error de validación',
            'errores' => $validacion['errores']
        ]);
        $conn->close();
        exit;
    }

    // Procesar documento si existe
    $documento_path = null;
    $tipo_documento = $validacion['tipo_documento'] ?? null;

    if (!empty($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = dirname(dirname(__DIR__)) . '/uploads/documentos/';
     
        // Verificar y crear el directorio de uploads si no existe
        if (!is_dir($upload_dir)) {
            try {
                if (!mkdir($upload_dir, 0777, true)) {
                    error_log("Error al crear directorio: " . $upload_dir);
                    error_log("Permisos actuales: " . substr(sprintf('%o', fileperms(dirname($upload_dir))), -4));
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al crear directorio de uploads',
                        'debug' => [
                            'dir' => $upload_dir,
                            'exists' => file_exists($upload_dir),
                            'parent_writable' => is_writable(dirname($upload_dir)),
                            'error' => error_get_last()
                        ]
                    ]);
                    $conn->close();
                    exit;
                }
                chmod($upload_dir, 0777);
            } catch (Exception $e) {
                error_log("Excepción al crear directorio: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear directorio de uploads: ' . $e->getMessage()
                ]);
                $conn->close();
                exit;
            }
        }
        
        // Verificar permisos de escritura
        if (!is_writable($upload_dir)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error de permisos en directorio de uploads'
            ]);
            $conn->close();
            exit;
        }

        $file_extension = pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Tipo de archivo no permitido. Solo PDF, JPG, JPEG, PNG'
            ]);
            $conn->close();
            exit;
        }

        $filename = uniqid('doc_') . '_' . $user['id'] . '.' . $file_extension;
       
        if (!move_uploaded_file($_FILES['documento']['tmp_name'], $upload_dir . $filename)) {
            $error = error_get_last();
            error_log("Error al mover archivo: " . print_r($error, true));
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al subir el documento',
                'debug' => [
                    'upload_dir' => $upload_dir,
                    'filename' => $filename,
                    'tmp_name' => $_FILES['documento']['tmp_name'],
                    'error' => $error,
                    'dir_writable' => is_writable($upload_dir),
                    'dir_exists' => is_dir($upload_dir),
                    'file_uploaded' => is_uploaded_file($_FILES['documento']['tmp_name'])
                ]
            ]);
            $conn->close();
            exit;
        }
    }

    // Calcular días hábiles
    $dias_habiles = $validacion['dias_habiles'];
    $aprobador_rol = $validacion['aprobador_rol'];
    $dias_total = (int)$dias_habiles;

    // Insertar solicitud con los nuevos campos
    $sql = "INSERT INTO solicitudes
            (usuario_id, tipo, fecha_inicio, fecha_fin, dias, dias_habiles, motivo,
             estado, prioridad, aprobador_rol, documento_adjunto, tipo_documento,
             validacion_disponibilidad, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', 'media', ?, ?, ?, 1, NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al preparar consulta: ' . $conn->error
        ]);
        $conn->close();
        exit;
    }

    // i=integer, s=string, d=double
    // usuario_id(i), tipo(s), fecha_inicio(s), fecha_fin(s), dias(i), dias_habiles(i), motivo(s), aprobador_rol(s), documento_adjunto(s), tipo_documento(s)
    $stmt->bind_param('isssiissss',
        $user['id'],
        $tipo,
        $fecha_inicio,
        $fecha_fin,
        $dias_total,
        $dias_total,
        $motivo,
        $aprobador_rol,
        $documento_path,
        $tipo_documento
    );

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear solicitud: ' . $stmt->error
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $insertId = $stmt->insert_id;
    $stmt->close();

    // Insertar actividad
    $desc = "Envió nueva solicitud ID {$insertId}";
    $stmt = $conn->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion) VALUES (?, 'solicitud', ?)");
    if ($stmt) {
        $stmt->bind_param('is', $user['id'], $desc);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'id' => $insertId,
        'message' => 'Solicitud creada exitosamente',
        'warnings' => $validacion['warnings'] ?? []
    ]);
    exit;

} catch (Exception $e) {
    error_log("Error en solicitud_create.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor',
        'message' => $e->getMessage()
    ]);
    if (isset($conn)) {
        $conn->close();
    }
    exit;
}   
