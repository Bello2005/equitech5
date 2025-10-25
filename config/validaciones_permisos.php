<?php
/**
 * Funciones de validación para permisos y vacaciones
 * Sistema ComfaChoco
 */

require_once __DIR__ . '/database.php';

/**
 * Calcula días hábiles entre dos fechas (sábados cuentan como hábiles)
 * Solo domingos y festivos NO cuentan
 */
function calcularDiasHabiles($fecha_inicio, $fecha_fin) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $dias_habiles = 0;

    $festivos = [
        // Agregar días festivos colombianos aquí
        '2024-01-01', '2024-12-25', // Año nuevo, navidad
    ];

    while ($inicio <= $fin) {
        $dia_semana = $inicio->format('N'); // 1 = lunes, 7 = domingo
        $fecha_str = $inicio->format('Y-m-d');

        // Solo domingo (7) no cuenta como hábil
        if ($dia_semana != 7 && !in_array($fecha_str, $festivos)) {
            $dias_habiles++;
        }

        $inicio->modify('+1 day');
    }

    return $dias_habiles;
}

/**
 * Obtiene la regla de aprobación aplicable para un tipo de permiso y duración
 */
function obtenerReglaAprobacion($tipo_permiso, $dias) {
    $conn = getConnection();

    $sql = "SELECT * FROM reglas_aprobacion
            WHERE tipo_permiso = ?
            AND activo = 1
            AND (dias_minimos IS NULL OR ? >= dias_minimos)
            AND (dias_maximos IS NULL OR ? <= dias_maximos)
            ORDER BY dias_minimos DESC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $tipo_permiso, $dias, $dias);
    $stmt->execute();
    $result = $stmt->get_result();
    $regla = $result->fetch_assoc();

    $conn->close();
    return $regla;
}

/**
 * Valida si un usuario tiene días de vacaciones disponibles
 */
function validarDiasDisponibles($usuario_id, $dias_solicitados) {
    $conn = getConnection();

    // Obtener información del usuario
    $sql_user = "SELECT u.*, te.nombre as tipo_empleado_nombre,
                 pv.dias_por_periodo, pv.max_periodos_acumulables
                 FROM usuarios u
                 LEFT JOIN tipos_empleado te ON te.id = u.tipo_empleado_id
                 LEFT JOIN politicas_vacaciones pv ON pv.tipo_empleado_id = u.tipo_empleado_id
                 WHERE u.id = ?";

    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    if (!$usuario) {
        return [
            'valido' => false,
            'mensaje' => 'Usuario no encontrado',
            'dias_disponibles' => 0
        ];
    }

    // Calcular días acumulados según antigüedad
    if ($usuario['fecha_ingreso']) {
        $fecha_ingreso = new DateTime($usuario['fecha_ingreso']);
        $hoy = new DateTime();
        $diff = $fecha_ingreso->diff($hoy);
        $dias_trabajados = $diff->days;

        // Calcular períodos completos (cada 360 días = 1 período)
        $periodos_completos = floor($dias_trabajados / 360);
        $dias_por_periodo = $usuario['dias_por_periodo'] ?? 15.00;
        $max_periodos = $usuario['max_periodos_acumulables'] ?? 2;

        // Limitar a máximo de períodos acumulables
        $periodos_disponibles = min($periodos_completos, $max_periodos);
        $dias_ganados = $periodos_disponibles * $dias_por_periodo;

        // Restar días ya usados
        $sql_usados = "SELECT COALESCE(SUM(dias_habiles), 0) as dias_usados
                       FROM solicitudes
                       WHERE usuario_id = ?
                       AND tipo = 'vacaciones'
                       AND estado IN ('aprobado', 'pendiente')";

        $stmt2 = $conn->prepare($sql_usados);
        $stmt2->bind_param('i', $usuario_id);
        $stmt2->execute();
        $usados = $stmt2->get_result()->fetch_assoc();
        $dias_usados = $usados['dias_usados'] ?? 0;

        $dias_disponibles = $dias_ganados - $dias_usados;

        $conn->close();

        return [
            'valido' => $dias_disponibles >= $dias_solicitados,
            'mensaje' => $dias_disponibles >= $dias_solicitados
                ? 'Días disponibles suficientes'
                : "Solo tiene $dias_disponibles días disponibles",
            'dias_disponibles' => $dias_disponibles,
            'dias_ganados' => $dias_ganados,
            'dias_usados' => $dias_usados,
            'periodos_completos' => $periodos_completos
        ];
    }

    $conn->close();
    return [
        'valido' => false,
        'mensaje' => 'Fecha de ingreso no registrada',
        'dias_disponibles' => 0
    ];
}

/**
 * Valida disponibilidad del departamento (70% mínimo disponible)
 */
function validarDisponibilidadDepartamento($departamento, $fecha_inicio, $fecha_fin, $usuario_id_excluir = null) {
    $conn = getConnection();

    // Contar total de empleados del departamento
    $sql_total = "SELECT COUNT(*) as total FROM usuarios WHERE departamento = ? AND activo = 1";
    $stmt = $conn->prepare($sql_total);
    $stmt->bind_param('s', $departamento);
    $stmt->execute();
    $total_result = $stmt->get_result()->fetch_assoc();
    $total_empleados = $total_result['total'];

    // Contar empleados en vacaciones en esas fechas
    $sql_vacaciones = "SELECT COUNT(DISTINCT s.usuario_id) as en_vacaciones
                       FROM solicitudes s
                       JOIN usuarios u ON u.id = s.usuario_id
                       WHERE u.departamento = ?
                       AND s.estado IN ('aprobado', 'pendiente')
                       AND s.tipo = 'vacaciones'
                       AND (
                           (s.fecha_inicio <= ? AND s.fecha_fin >= ?)
                           OR (s.fecha_inicio <= ? AND s.fecha_fin >= ?)
                           OR (s.fecha_inicio >= ? AND s.fecha_fin <= ?)
                       )";

    if ($usuario_id_excluir) {
        $sql_vacaciones .= " AND s.usuario_id != ?";
    }

    $stmt2 = $conn->prepare($sql_vacaciones);
    if ($usuario_id_excluir) {
        $stmt2->bind_param('sssssssi', $departamento, $fecha_fin, $fecha_inicio,
                          $fecha_fin, $fecha_inicio, $fecha_inicio, $fecha_fin,
                          $usuario_id_excluir);
    } else {
        $stmt2->bind_param('sssssss', $departamento, $fecha_fin, $fecha_inicio,
                          $fecha_fin, $fecha_inicio, $fecha_inicio, $fecha_fin);
    }

    $stmt2->execute();
    $vacaciones_result = $stmt2->get_result()->fetch_assoc();
    $en_vacaciones = $vacaciones_result['en_vacaciones'] + 1; // +1 porque se suma la solicitud actual

    $disponibles = $total_empleados - $en_vacaciones;
    $porcentaje_disponible = ($disponibles / $total_empleados) * 100;

    $conn->close();

    return [
        'valido' => $porcentaje_disponible >= 70,
        'mensaje' => $porcentaje_disponible >= 70
            ? 'Disponibilidad del departamento cumplida'
            : "Solo estaría disponible el " . round($porcentaje_disponible, 1) . "% (requiere 70%)",
        'total_empleados' => $total_empleados,
        'disponibles' => $disponibles,
        'porcentaje' => round($porcentaje_disponible, 1)
    ];
}

/**
 * Valida que la solicitud se haga con la anticipación requerida
 */
function validarAnticipacion($tipo_permiso, $fecha_inicio) {
    $regla = obtenerReglaAprobacion($tipo_permiso, 1);

    if (!$regla || !$regla['dias_anticipacion_minima']) {
        return ['valido' => true, 'mensaje' => 'Sin requisito de anticipación'];
    }

    $hoy = new DateTime();
    $inicio = new DateTime($fecha_inicio);
    $diff = $hoy->diff($inicio);
    $dias_anticipacion = $diff->days;

    if ($diff->invert) {
        return [
            'valido' => false,
            'mensaje' => 'La fecha de inicio ya pasó'
        ];
    }

    $dias_requeridos = $regla['dias_anticipacion_minima'];

    return [
        'valido' => $dias_anticipacion >= $dias_requeridos,
        'mensaje' => $dias_anticipacion >= $dias_requeridos
            ? "Anticipación cumplida ($dias_anticipacion días)"
            : "Se requieren al menos $dias_requeridos días de anticipación (solo tiene $dias_anticipacion)",
        'dias_anticipacion' => $dias_anticipacion,
        'dias_requeridos' => $dias_requeridos
    ];
}

/**
 * Validación completa de una solicitud de vacaciones/permiso
 */
function validarSolicitudCompleta($datos) {
    $errores = [];
    $warnings = [];

    // 1. Validar días hábiles
    $dias_habiles = calcularDiasHabiles($datos['fecha_inicio'], $datos['fecha_fin']);

    // 2. Obtener regla de aprobación
    $tipo_permiso = $datos['tipo_permiso'] ?? $datos['tipo'] ?? '';
    $regla = obtenerReglaAprobacion($tipo_permiso, $dias_habiles);

    if (!$regla) {
        $errores[] = "No hay regla de aprobación definida para este tipo de permiso";
    } else {
        // 3. Validar documento requerido
        if ($regla['requiere_documento'] && empty($datos['tiene_documento'])) {
            $errores[] = "Este tipo de permiso requiere adjuntar: " . $regla['tipo_documento'];
        }
    }

    // 4. Validar anticipación
    $validacion_anticipacion = validarAnticipacion($tipo_permiso, $datos['fecha_inicio']);
    if (!$validacion_anticipacion['valido']) {
        $errores[] = $validacion_anticipacion['mensaje'];
    }

    // 5. Validar días disponibles (solo para vacaciones)
    if ($tipo_permiso == 'vacaciones') {
        $validacion_dias = validarDiasDisponibles($datos['usuario_id'], $dias_habiles);
        if (!$validacion_dias['valido']) {
            $errores[] = $validacion_dias['mensaje'];
        }

        // 6. Validar disponibilidad del departamento
        if (!empty($datos['departamento'])) {
            $validacion_dpto = validarDisponibilidadDepartamento(
                $datos['departamento'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['usuario_id']
            );

            if (!$validacion_dpto['valido']) {
                $warnings[] = $validacion_dpto['mensaje'];
            }
        }
    }

    return [
        'valido' => count($errores) == 0,
        'errores' => $errores,
        'warnings' => $warnings,
        'dias_habiles' => $dias_habiles,
        'aprobador_rol' => $regla['aprobador_rol'] ?? 'rrhh',
        'requiere_documento' => $regla['requiere_documento'] ?? false,
        'tipo_documento' => $regla['tipo_documento'] ?? null,
        'validacion_dias' => isset($validacion_dias) ? $validacion_dias : null,
        'validacion_dpto' => isset($validacion_dpto) ? $validacion_dpto : null
    ];
}
