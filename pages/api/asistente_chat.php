<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/validaciones_permisos.php';

header('Content-Type: application/json');
requireLogin();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$mensaje = strtolower(trim($data['mensaje'] ?? ''));
$tiene_archivos = !empty($data['archivos']);

// Guardar archivos adjuntos en la sesión si hay una solicitud en proceso
if ($tiene_archivos && isset($_SESSION['solicitud_en_proceso'])) {
    $_SESSION['solicitud_en_proceso']['archivos'] = $data['archivos'];

    // Para maternidad/paternidad que ya tienen motivo, simular "confirmar" automáticamente
    $tipo = $_SESSION['solicitud_en_proceso']['tipo'] ?? '';
    $tiene_motivo = isset($_SESSION['solicitud_en_proceso']['motivo']);
    if (in_array($tipo, ['maternidad', 'paternidad']) && $tiene_motivo) {
        $mensaje = 'confirmar'; // Simular confirmación automática
    }
}

if (empty($mensaje) && !$tiene_archivos) {
    http_response_code(400);
    echo json_encode(['error' => 'Mensaje vacío']);
    exit;
}

try {
    $conn = getConnection();
    $respuesta = '';
    $tipo = 'texto';
    $datos_adicionales = [];

    // 0. COMANDO CANCELAR - Volver al menú principal
    if (preg_match('/^(cancelar|volver|men[uú]|inicio|salir)$/i', $mensaje)) {
        unset($_SESSION['solicitud_en_proceso']);
        $respuesta = "
            <div class='bg-blue-50 border-l-4 border-blue-500 rounded-lg p-3 my-2'>
                <p class='text-blue-800 font-semibold'><i class='fas fa-info-circle mr-2'></i>Operación cancelada</p>
                <p class='text-sm text-blue-700 mt-1'>Has vuelto al menú principal.</p>
            </div>
            <p class='text-gray-700 mt-3'>¿En qué puedo ayudarte?</p>
        ";
    }

    // 1. PREGUNTAS SOBRE DÍAS DISPONIBLES
    elseif (preg_match('/cuantos.*d[ií]as.*(tengo|disponible|permiso|vacaciones|quedan)/iu', $mensaje) ||
        preg_match('/d[ií]as.*disponible/iu', $mensaje) ||
        preg_match('/ver.*mis.*d[ií]as/iu', $mensaje) ||
        preg_match('/consulta.*saldo/iu', $mensaje) ||
        preg_match('/cu[áa]ntos.*d[ií]as.*de.*permiso.*tengo.*disponibles?/iu', $mensaje) ||
        preg_match('/cuantos.*dias.*permiso.*disponibles?/iu', $mensaje)) {

        $dias_info = validarDiasDisponibles($user['id'], 0);

        // Calcular máximo de días acumulables (2 períodos x 15 días = 30 días)
        // Los días se acumulan año tras año, máximo 2 años (30 días)
        $max_dias_acumulables = 30;
        $dias_disponibles_real = max(0, min($dias_info['dias_disponibles'], $max_dias_acumulables));
        $periodos_disponibles = min($dias_info['periodos_completos'], 2);

        $respuesta = "
            <div class='bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 rounded-lg p-4 my-3'>
                <p class='flex items-center text-green-800 font-bold mb-3'>
                    <i class='fas fa-umbrella-beach mr-2'></i>
                    Tus Días de Vacaciones
                </p>
                <div class='bg-white p-3 rounded-lg shadow-sm'>
                    <div class='space-y-2 text-sm'>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Días disponibles:</span>
                            <span class='font-bold text-green-600'>" . round($dias_disponibles_real) . " días</span>
                        </div>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Días ganados:</span>
                            <span class='text-gray-900'>" . round($dias_info['dias_ganados']) . " días</span>
                        </div>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Días usados:</span>
                            <span class='text-gray-900'>" . round($dias_info['dias_usados']) . " días</span>
                        </div>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Períodos acumulados:</span>
                            <span class='text-gray-900'>" . $periodos_disponibles . " de 2 máximo</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class='bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3'>
                <p class='text-xs text-blue-800 font-semibold mb-2'><i class='fas fa-info-circle mr-1'></i>Información importante:</p>
                <ul class='text-xs text-blue-700 ml-4 space-y-1'>
                    <li>• <strong>15 días hábiles</strong> por cada período de un año (360 días)</li>
                    <li>• Máximo acumulable: <strong>30 días (2 períodos)</strong></li>
                    <li>• Los sábados cuentan como día hábil</li>
                    <li>• Se solicita con 30 días de anticipación</li>
                </ul>
            </div>
        ";

        $datos_adicionales = [
            'dias_disponibles' => $dias_info['dias_disponibles'],
            'dias_ganados' => $dias_info['dias_ganados'],
            'dias_usados' => $dias_info['dias_usados'],
            'periodos' => $dias_info['periodos_completos']
        ];
    }

    // 2. PREGUNTAS SOBRE MIS SOLICITUDES
    elseif (preg_match('/estado.*de.*mis.*solicitudes/i', $mensaje) ||
        preg_match('/estado.*mis.*solicitudes/i', $mensaje) ||
        preg_match('/mis.*solicitudes/i', $mensaje) ||
        preg_match('/solicitudes.*pendientes/i', $mensaje) ||
        preg_match('/revisar.*solicitudes/i', $mensaje) ||
        preg_match('/ver.*solicitudes/i', $mensaje)) {

        $sql = "SELECT id, tipo, fecha_inicio, fecha_fin, dias_habiles, estado, fecha_creacion, motivo
                FROM solicitudes
                WHERE usuario_id = ?
                ORDER BY fecha_creacion DESC
                LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $respuesta = "<div class='space-y-3'>";
            $respuesta .= "<p class='font-bold text-gray-800'><i class='fas fa-list mr-2'></i>Tus últimas solicitudes:</p>";

            while ($row = $result->fetch_assoc()) {
                $color_estado = [
                    'pendiente' => 'bg-yellow-50 border-yellow-500 text-yellow-800',
                    'aprobado' => 'bg-green-50 border-green-500 text-green-800',
                    'rechazado' => 'bg-red-50 border-red-500 text-red-800'
                ][$row['estado']] ?? 'bg-gray-50 border-gray-500 text-gray-800';

                $respuesta .= "
                    <div class='$color_estado border-l-4 rounded-lg p-3 text-sm'>
                        <div class='flex justify-between items-start'>
                            <div>
                                <p class='font-semibold'>" . ucfirst(str_replace('_', ' ', $row['tipo'])) . "</p>
                                <p class='text-xs mt-1'>Del " . date('d/m/Y', strtotime($row['fecha_inicio'])) . " al " . date('d/m/Y', strtotime($row['fecha_fin'])) . "</p>
                                <p class='text-xs'>Días: " . $row['dias_habiles'] . " días hábiles</p>
                            </div>
                            <span class='px-2 py-1 rounded-full text-xs font-bold'>" . strtoupper($row['estado']) . "</span>
                        </div>
                    </div>
                ";
            }
            $respuesta .= "</div>";
        } else {
            $respuesta = "<p class='text-gray-600'>No tienes solicitudes registradas aún.</p>";
        }
        $stmt->close();
    }

    // 3. PREGUNTAS SOBRE POLÍTICAS DE VACACIONES
    elseif (preg_match('/pol[ií]ticas|reglas|normas/iu', $mensaje) ||
        preg_match('/conocer.*pol[ií]ticas/iu', $mensaje) ||
        preg_match('/pol[ií]ticas.*reglas/iu', $mensaje) ||
        preg_match('/quiero.*conocer.*pol[ií]ticas/iu', $mensaje) ||
        preg_match('/pol[ií]ticas.*de.*permisos/iu', $mensaje)) {

        // Obtener tipo de empleado del usuario
        $sql = "SELECT te.nombre as tipo_empleado, pv.dias_por_periodo, pv.es_acumulable, pv.max_periodos_acumulables, pv.observaciones
                FROM usuarios u
                LEFT JOIN tipos_empleado te ON te.id = u.tipo_empleado_id
                LEFT JOIN politicas_vacaciones pv ON pv.tipo_empleado_id = u.tipo_empleado_id
                WHERE u.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $politica = $result->fetch_assoc();
        $stmt->close();

        if ($politica && $politica['tipo_empleado']) {
            $respuesta = "
                <div class='bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg p-4 my-3'>
                    <p class='font-bold text-blue-800 mb-3'><i class='fas fa-book mr-2'></i>Políticas de Permisos y Vacaciones ComfaChoco</p>

                    <div class='space-y-3 text-sm'>
                        <!-- Vacaciones -->
                        <div class='bg-white p-3 rounded-lg shadow-sm'>
                            <p class='font-semibold text-blue-800 mb-2'>🏖️ Vacaciones</p>
                            <ul class='text-blue-700 space-y-1 ml-4 text-xs'>
                                <li>• <strong>15 días hábiles</strong> por periodo de 1 año (360 días)</li>
                                <li>• Acumulables hasta <strong>2 períodos máximo</strong></li>
                                <li>• Se solicita inmediatamente al cumplir el periodo</li>
                                <li>• Debe tomarse con <strong>30 días de anticipación</strong></li>
                                <li>• Los <strong>sábados cuentan como día hábil</strong></li>
                            </ul>
                        </div>

                        <!-- Maternidad/Paternidad -->
                        <div class='bg-white p-3 rounded-lg shadow-sm'>
                            <p class='font-semibold text-pink-700 mb-2'>👶 Maternidad y Paternidad</p>
                            <ul class='text-gray-700 space-y-1 ml-4 text-xs'>
                                <li>• <strong>Mujeres:</strong> 4 meses (requiere certificado)</li>
                                <li>• <strong>Hombres:</strong> 15 días (requiere certificado)</li>
                            </ul>
                        </div>

                        <!-- Permisos Médicos -->
                        <div class='bg-white p-3 rounded-lg shadow-sm'>
                            <p class='font-semibold text-red-700 mb-2'>🏥 Permisos Médicos</p>
                            <ul class='text-gray-700 space-y-1 ml-4 text-xs'>
                                <li>• Requieren <strong>órdenes médicas</strong></li>
                                <li>• Todos los <strong>anexos relacionados con la enfermedad</strong></li>
                                <li>• Padre acompañando hijo a cita médica es válido</li>
                            </ul>
                        </div>

                        <!-- Otros Permisos -->
                        <div class='bg-white p-3 rounded-lg shadow-sm'>
                            <p class='font-semibold text-purple-700 mb-2'>📋 Otros Permisos</p>
                            <ul class='text-gray-700 space-y-1 ml-4 text-xs'>
                                <li>• <strong>Jurado:</strong> Documento acreditativo</li>
                                <li>• <strong>Duelo:</strong> Certificado de defunción</li>
                                <li>• <strong>Otras causas:</strong> Anexo correspondiente</li>
                            </ul>
                        </div>

                        <!-- Gestión de Reemplazo -->
                        <div class='bg-white p-3 rounded-lg shadow-sm'>
                            <p class='font-semibold text-orange-700 mb-2'>⚖️ Gestión de Reemplazo</p>
                            <ul class='text-gray-700 space-y-1 ml-4 text-xs'>
                                <li>• El <strong>70% de funcionarios</strong> deben estar disponibles</li>
                                <li>• Personas de la <strong>misma dependencia</strong> no pueden salir al mismo tiempo</li>
                            </ul>
                        </div>
                    </div>
                </div>
            ";
        } else {
            $respuesta = "<p class='text-gray-600'>No encontré información sobre tu tipo de empleado. Por favor contacta a RRHH.</p>";
        }
    }

    // 4. TIPOS ESPECÍFICOS DE PERMISOS (DEBEN IR PRIMERO)
    // 4A. Permiso Médico
    elseif (preg_match('/solicitar.*permiso.*m[eé]dico/iu', $mensaje) ||
            preg_match('/permiso.*m[eé]dico/iu', $mensaje)) {

        // Guardar tipo en sesión
        $_SESSION['solicitud_en_proceso'] = ['tipo' => 'permiso_medico'];

        $respuesta = "
            <div class='bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                <p class='font-bold text-red-800 mb-3'><i class='fas fa-hospital mr-2'></i>Solicitud de Permiso Médico</p>

                <div class='bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4'>
                    <p class='text-xs text-yellow-800 font-semibold mb-1'><i class='fas fa-info-circle mr-1'></i>Requisitos:</p>
                    <ul class='text-xs text-yellow-700 ml-4 space-y-1'>
                        <li>• <strong>Órdenes médicas obligatorias</strong></li>
                        <li>• Anexos relacionados con la enfermedad</li>
                        <li>• Válido para acompañar hijo/a a cita médica</li>
                        <li>• 0-2 días: Aprueba Jefe Inmediato</li>
                        <li>• 3+ días: Aprueba RRHH</li>
                    </ul>
                </div>
            </div>

            <p class='text-gray-700 mb-2'>Para continuar, necesito que me indiques:</p>
            <p class='text-gray-700 font-semibold mb-1'>📅 Fecha de inicio (formato: DD/MM/AAAA)</p>
            <p class='text-xs text-gray-500 mb-3'>Ejemplo: 25/10/2025</p>
            <p class='text-xs text-gray-600 mt-2'><i class='fas fa-paperclip mr-1'></i>Recuerda adjuntar las órdenes médicas usando el botón de archivos 📎</p>
            <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
        ";
    }

    // 4B. SOLICITAR PERMISOS - MOSTRAR TIPOS (PATRÓN GENERAL)
    elseif (preg_match('/quiero.*solicitar.*permisos/i', $mensaje) ||
        preg_match('/solicitar.*permisos/i', $mensaje) ||
        preg_match('/crear.*solicitud/i', $mensaje)) {

        $respuesta = "
            <div class='bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-lg p-4 my-3'>
                <p class='font-bold text-blue-800 mb-3'><i class='fas fa-clipboard-list mr-2'></i>Tipos de Permisos Disponibles</p>

                <div class='space-y-2 text-sm'>
                    <button onclick=\"window.chatInstance.quickMessage('Solicitar vacaciones')\" class='w-full bg-white hover:bg-blue-50 p-3 rounded-lg shadow-sm border-l-4 border-blue-500 text-left transition-all duration-200 cursor-pointer'>
                        <p class='font-semibold text-blue-800'>🏖️ Vacaciones</p>
                        <p class='text-xs text-gray-600 mt-1'>15 días hábiles por año. Acumulables hasta 2 períodos.</p>
                    </button>

                    <button onclick=\"window.chatInstance.quickMessage('Solicitar maternidad o paternidad')\" class='w-full bg-white hover:bg-pink-50 p-3 rounded-lg shadow-sm border-l-4 border-pink-500 text-left transition-all duration-200 cursor-pointer'>
                        <p class='font-semibold text-pink-700'>👶 Maternidad/Paternidad</p>
                        <p class='text-xs text-gray-600 mt-1'>Mujeres: 4 meses | Hombres: 15 días (requiere certificado)</p>
                    </button>

                    <button onclick=\"window.chatInstance.quickMessage('Solicitar permiso médico')\" class='w-full bg-white hover:bg-red-50 p-3 rounded-lg shadow-sm border-l-4 border-red-500 text-left transition-all duration-200 cursor-pointer'>
                        <p class='font-semibold text-red-700'>🏥 Permiso Médico</p>
                        <p class='text-xs text-gray-600 mt-1'>Requiere órdenes médicas y anexos relacionados</p>
                    </button>

                    <button onclick=\"window.chatInstance.quickMessage('Solicitar permiso por jurado')\" class='w-full bg-white hover:bg-purple-50 p-3 rounded-lg shadow-sm border-l-4 border-purple-500 text-left transition-all duration-200 cursor-pointer'>
                        <p class='font-semibold text-purple-700'>⚖️ Jurado</p>
                        <p class='text-xs text-gray-600 mt-1'>Requiere documento acreditativo</p>
                    </button>

                    <button onclick=\"window.chatInstance.quickMessage('Solicitar permiso por duelo')\" class='w-full bg-white hover:bg-gray-50 p-3 rounded-lg shadow-sm border-l-4 border-gray-500 text-left transition-all duration-200 cursor-pointer'>
                        <p class='font-semibold text-gray-700'>🕊️ Duelo</p>
                        <p class='text-xs text-gray-600 mt-1'>Requiere certificado de defunción del familiar</p>
                    </button>

                    <button onclick=\"window.chatInstance.quickMessage('Solicitar teletrabajo')\" class='w-full bg-white hover:bg-orange-50 p-3 rounded-lg shadow-sm border-l-4 border-orange-500 text-left transition-all duration-200 cursor-pointer'>
                        <p class='font-semibold text-orange-700'>💼 Teletrabajo</p>
                        <p class='text-xs text-gray-600 mt-1'>Solicitud de trabajo remoto</p>
                    </button>
                </div>

                <div class='mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg'>
                    <p class='text-xs text-yellow-800 font-semibold mb-2'><i class='fas fa-info-circle mr-1'></i>Recuerda:</p>
                    <ul class='text-xs text-yellow-700 ml-4 space-y-1'>
                        <li>• <strong>0-2 días:</strong> Aprueba tu Jefe Inmediato</li>
                        <li>• <strong>3+ días:</strong> Aprueba Recursos Humanos</li>
                        <li>• <strong>Vacaciones:</strong> Solicita con 30 días de anticipación (se pueden solicitar al cumplir el período)</li>
                        <li>• <strong>Los sábados cuentan como día hábil</strong></li>
                    </ul>
                </div>
            </div>
            <p class='text-gray-700 text-sm mt-3'><i class='fas fa-hand-pointer mr-1'></i>Haz clic en el tipo de permiso que deseas solicitar.</p>
        ";
    }

    // 4B. INICIAR SOLICITUD DE VACACIONES
    elseif (preg_match('/solicitar vacaciones/i', $mensaje)) {

        // Guardar tipo en sesión
        $_SESSION['solicitud_en_proceso'] = ['tipo' => 'vacaciones'];

        $dias_info = validarDiasDisponibles($user['id'], 0);

        // Calcular máximo de días acumulables (2 períodos x 15 días)
        $max_dias_acumulables = 30;
        $dias_disponibles_real = max(0, min($dias_info['dias_disponibles'], $max_dias_acumulables));

        // Validar si tiene días disponibles
        if ($dias_disponibles_real <= 0) {
            $respuesta = "
                <div class='bg-red-50 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                    <p class='text-red-800 font-bold'><i class='fas fa-exclamation-circle mr-2'></i>Sin días disponibles</p>
                    <p class='text-sm text-red-700 mt-2'>No tienes días de vacaciones disponibles en este momento.</p>

                    <div class='mt-3 bg-white p-3 rounded-lg'>
                        <p class='text-sm font-semibold text-gray-700 mb-2'>Tu saldo actual:</p>
                        <div class='space-y-1 text-xs text-gray-600'>
                            <p><strong>Días ganados:</strong> " . round($dias_info['dias_ganados']) . " días</p>
                            <p><strong>Días usados:</strong> " . round($dias_info['dias_usados']) . " días</p>
                            <p><strong>Días disponibles:</strong> " . round($dias_disponibles_real) . " días</p>
                        </div>
                    </div>

                    <p class='text-xs text-gray-600 mt-3'><i class='fas fa-info-circle mr-1'></i>Debes trabajar más tiempo para acumular días. Cada 360 días trabajados = 15 días de vacaciones.</p>
                </div>
            ";
            unset($_SESSION['solicitud_en_proceso']);
        } else {
            $respuesta = "
                <div class='bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-lg p-4 my-3'>
                    <p class='font-bold text-blue-800 mb-3'><i class='fas fa-umbrella-beach mr-2'></i>Solicitud de Vacaciones</p>

                    <div class='bg-white p-3 rounded-lg shadow-sm mb-3'>
                        <p class='text-sm font-semibold text-gray-700 mb-2'>Tus días disponibles:</p>
                        <p class='text-3xl font-bold text-green-600'>" . round($dias_disponibles_real) . " días</p>
                        <div class='mt-2 pt-2 border-t border-gray-200 space-y-1'>
                            <p class='text-xs text-gray-600'>Días ganados: <strong>" . round($dias_info['dias_ganados']) . "</strong> | Días usados: <strong>" . round($dias_info['dias_usados']) . "</strong></p>
                            <p class='text-xs text-blue-600'><i class='fas fa-info-circle mr-1'></i>Máximo acumulable: " . $max_dias_acumulables . " días (2 períodos)</p>
                        </div>
                    </div>

                    <div class='bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4'>
                        <p class='text-xs text-yellow-800 font-semibold mb-1'><i class='fas fa-exclamation-triangle mr-1'></i>Requisitos importantes:</p>
                        <ul class='text-xs text-yellow-700 ml-4 space-y-1'>
                            <li>• Solicitar con <strong>30 días de anticipación</strong></li>
                            <li>• 70% del equipo debe estar disponible</li>
                            <li>• No puede haber compañeros de tu área de vacaciones al mismo tiempo</li>
                            <li>• Los sábados cuentan como día hábil</li>
                        </ul>
                    </div>
                </div>

                <p class='text-gray-700 mb-2'>Para continuar, necesito que me indiques:</p>
                <p class='text-gray-700 font-semibold mb-1'>📅 Fecha de inicio (formato: DD/MM/AAAA)</p>
                <p class='text-xs text-gray-500 mb-3'>Ejemplo: 15/12/2025</p>
                <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
            ";
        }
    }

    // 4C. INICIAR SOLICITUD DE MATERNIDAD/PATERNIDAD
    elseif (preg_match('/solicitar (maternidad|paternidad)/i', $mensaje, $matches)) {

        $tipo = strtolower($matches[1]);
        $dias = ($tipo === 'maternidad') ? '120 días (4 meses)' : '15 días';

        // Guardar tipo en sesión
        $_SESSION['solicitud_en_proceso'] = ['tipo' => $tipo];

        $respuesta = "
            <div class='bg-gradient-to-r from-pink-50 to-pink-100 border-l-4 border-pink-500 rounded-lg p-4 my-3'>
                <p class='font-bold text-pink-800 mb-3'><i class='fas fa-baby mr-2'></i>Solicitud de " . ucfirst($tipo) . "</p>

                <div class='bg-white p-3 rounded-lg shadow-sm mb-3'>
                    <p class='text-sm font-semibold text-gray-700'>Duración del permiso:</p>
                    <p class='text-2xl font-bold text-pink-600'>" . $dias . "</p>
                </div>

                <div class='bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4'>
                    <p class='text-xs text-yellow-800 font-semibold mb-1'><i class='fas fa-info-circle mr-1'></i>Requisitos:</p>
                    <ul class='text-xs text-yellow-700 ml-4 space-y-1'>
                        <li>• <strong>Certificado médico obligatorio</strong></li>
                        <li>• Aprueba Recursos Humanos</li>
                    </ul>
                </div>
            </div>

            <p class='text-gray-700 mb-2'>Para continuar, necesito que me indiques:</p>
            <p class='text-gray-700 font-semibold mb-1'>📅 Fecha probable de parto o nacimiento (formato: DD/MM/AAAA)</p>
            <p class='text-xs text-gray-500 mb-3'>Ejemplo: 15/03/2026</p>
            <p class='text-xs text-gray-600 mt-2'><i class='fas fa-paperclip mr-1'></i>Recuerda adjuntar el certificado médico usando el botón de archivos 📎</p>
            <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
        ";
    }

    // 4D. INICIAR SOLICITUD DE PERMISO POR JURADO
    elseif (preg_match('/solicitar permiso por jurado/i', $mensaje)) {

        // Guardar tipo en sesión
        $_SESSION['solicitud_en_proceso'] = ['tipo' => 'jurado'];

        $respuesta = "
            <div class='bg-gradient-to-r from-purple-50 to-purple-100 border-l-4 border-purple-500 rounded-lg p-4 my-3'>
                <p class='font-bold text-purple-800 mb-3'><i class='fas fa-gavel mr-2'></i>Solicitud de Permiso por Jurado</p>

                <div class='bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4'>
                    <p class='text-xs text-yellow-800 font-semibold mb-1'><i class='fas fa-info-circle mr-1'></i>Requisitos:</p>
                    <ul class='text-xs text-yellow-700 ml-4 space-y-1'>
                        <li>• <strong>Documento acreditativo obligatorio</strong></li>
                        <li>• Aprueba Recursos Humanos</li>
                    </ul>
                </div>
            </div>

            <p class='text-gray-700 mb-2'>Para continuar, necesito que me indiques:</p>
            <p class='text-gray-700 font-semibold mb-1'>📅 Fecha de inicio (formato: DD/MM/AAAA)</p>
            <p class='text-xs text-gray-500 mb-3'>Ejemplo: 10/11/2025</p>
            <p class='text-xs text-gray-600 mt-2'><i class='fas fa-paperclip mr-1'></i>Recuerda adjuntar el documento acreditativo usando el botón de archivos 📎</p>
            <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
        ";
    }

    // 4F. INICIAR SOLICITUD DE PERMISO POR DUELO
    elseif (preg_match('/solicitar permiso por duelo/i', $mensaje)) {

        // Guardar tipo en sesión
        $_SESSION['solicitud_en_proceso'] = ['tipo' => 'duelo'];

        $respuesta = "
            <div class='bg-gradient-to-r from-gray-50 to-gray-100 border-l-4 border-gray-500 rounded-lg p-4 my-3'>
                <p class='font-bold text-gray-800 mb-3'><i class='fas fa-dove mr-2'></i>Solicitud de Permiso por Duelo</p>

                <div class='bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4'>
                    <p class='text-xs text-yellow-800 font-semibold mb-1'><i class='fas fa-info-circle mr-1'></i>Requisitos:</p>
                    <ul class='text-xs text-yellow-700 ml-4 space-y-1'>
                        <li>• <strong>Certificado de defunción obligatorio</strong></li>
                        <li>• Aprueba Recursos Humanos</li>
                    </ul>
                </div>
            </div>

            <p class='text-gray-700 mb-2'>Mis condolencias por tu pérdida. Para continuar, necesito que me indiques:</p>
            <p class='text-gray-700 font-semibold mb-1'>📅 Fecha de inicio (formato: DD/MM/AAAA)</p>
            <p class='text-xs text-gray-500 mb-3'>Ejemplo: 26/10/2025</p>
            <p class='text-xs text-gray-600 mt-2'><i class='fas fa-paperclip mr-1'></i>Recuerda adjuntar el certificado de defunción usando el botón de archivos 📎</p>
            <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
        ";
    }

    // 4G. INICIAR SOLICITUD DE TELETRABAJO
    elseif (preg_match('/solicitar teletrabajo/i', $mensaje)) {

        // Guardar tipo en sesión
        $_SESSION['solicitud_en_proceso'] = ['tipo' => 'teletrabajo'];

        $respuesta = "
            <div class='bg-gradient-to-r from-orange-50 to-orange-100 border-l-4 border-orange-500 rounded-lg p-4 my-3'>
                <p class='font-bold text-orange-800 mb-3'><i class='fas fa-laptop-house mr-2'></i>Solicitud de Teletrabajo</p>

                <div class='bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4'>
                    <p class='text-xs text-yellow-800 font-semibold mb-1'><i class='fas fa-info-circle mr-1'></i>Información:</p>
                    <ul class='text-xs text-yellow-700 ml-4 space-y-1'>
                        <li>• 0-2 días: Aprueba Jefe Inmediato</li>
                        <li>• 3+ días: Aprueba RRHH</li>
                    </ul>
                </div>
            </div>

            <p class='text-gray-700 mb-2'>Para continuar, necesito que me indiques:</p>
            <p class='text-gray-700 font-semibold mb-1'>📅 Fecha de inicio (formato: DD/MM/AAAA)</p>
            <p class='text-xs text-gray-500 mb-3'>Ejemplo: 28/10/2025</p>
            <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
        ";
    }

    // 4H. PROCESAR FECHA EN FORMATO DD/MM/AAAA
    elseif (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/i', $mensaje, $matches)) {

        // El usuario escribió una fecha, guardar en sesión y pedir más info
        $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $anio = $matches[3];
        $fecha_formateada = "$anio-$mes-$dia";

        // Guardar en sesión para el siguiente paso
        if (!isset($_SESSION['solicitud_en_proceso'])) {
            $_SESSION['solicitud_en_proceso'] = [];
        }

        if (!isset($_SESSION['solicitud_en_proceso']['fecha_inicio'])) {
            // Primera fecha = fecha de inicio
            $_SESSION['solicitud_en_proceso']['fecha_inicio'] = $fecha_formateada;

            $respuesta = "
                <div class='bg-green-50 border-l-4 border-green-500 rounded-lg p-3 my-2'>
                    <p class='text-green-800 font-semibold'><i class='fas fa-check-circle mr-2'></i>Fecha de inicio registrada</p>
                    <p class='text-sm text-green-700 mt-1'>" . date('d/m/Y', strtotime($fecha_formateada)) . "</p>
                </div>

                <p class='text-gray-700 mt-3 mb-2'>Ahora necesito:</p>
                <p class='text-gray-700 font-semibold mb-1'>📅 Fecha de fin (formato: DD/MM/AAAA)</p>
                <p class='text-xs text-gray-500 mb-3'>Ejemplo: 20/12/2025</p>
                <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
            ";
        } elseif (!isset($_SESSION['solicitud_en_proceso']['fecha_fin'])) {
            // Segunda fecha = fecha de fin
            $fecha_inicio = $_SESSION['solicitud_en_proceso']['fecha_inicio'];
            $fecha_fin = $fecha_formateada;
            $tipo_permiso = $_SESSION['solicitud_en_proceso']['tipo'] ?? 'otro';

            // Calcular días hábiles
            $dias_habiles = calcularDiasHabiles($fecha_inicio, $fecha_fin);

            // VALIDACIÓN 1: Verificar que la fecha de fin sea posterior a la de inicio
            if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
                $respuesta = "
                    <div class='bg-red-50 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                        <p class='text-red-800 font-bold'><i class='fas fa-exclamation-circle mr-2'></i>Fecha inválida</p>
                        <p class='text-sm text-red-700 mt-2'>La fecha de fin debe ser posterior a la fecha de inicio.</p>
                        <p class='text-sm text-red-700 mt-2'><strong>Fecha inicio:</strong> " . date('d/m/Y', strtotime($fecha_inicio)) . "</p>
                        <p class='text-sm text-red-700 mt-2'><strong>Fecha fin (inválida):</strong> " . date('d/m/Y', strtotime($fecha_fin)) . "</p>
                    </div>
                    <p class='text-gray-700 mt-3'>Por favor, ingresa una fecha de fin que sea POSTERIOR a la fecha de inicio:</p>
                ";
                unset($_SESSION['solicitud_en_proceso']);
            } else {
                // Fechas válidas, continuar con otras validaciones

            // VALIDACIÓN 2: Para vacaciones, verificar días disponibles
            $validacion_ok = true;
            if ($tipo_permiso === 'vacaciones') {
                $dias_info = validarDiasDisponibles($user['id'], $dias_habiles);

                if (!$dias_info['valido']) {
                    $respuesta = "
                        <div class='bg-red-50 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                            <p class='text-red-800 font-bold'><i class='fas fa-exclamation-circle mr-2'></i>Días insuficientes</p>
                            <p class='text-sm text-red-700 mt-2'>" . $dias_info['mensaje'] . "</p>

                            <div class='mt-3 bg-white p-3 rounded-lg'>
                                <p class='text-sm font-semibold text-gray-700 mb-2'>Tu saldo de vacaciones:</p>
                                <div class='space-y-1 text-xs text-gray-600'>
                                    <p><strong>Días disponibles:</strong> " . $dias_info['dias_disponibles'] . " días</p>
                                    <p><strong>Días ganados:</strong> " . $dias_info['dias_ganados'] . " días (máximo " . (2 * 15) . " días = 2 períodos)</p>
                                    <p><strong>Días usados:</strong> " . $dias_info['dias_usados'] . " días</p>
                                    <p><strong>Días solicitados:</strong> " . $dias_habiles . " días</p>
                                </div>
                            </div>
                        </div>
                        <p class='text-gray-700 mt-3 text-sm'>Por favor, ajusta las fechas para no exceder tus días disponibles.</p>
                    ";
                    unset($_SESSION['solicitud_en_proceso']);
                    $validacion_ok = false;
                } else {
                    // VALIDACIÓN 3: Verificar 30 días de anticipación para vacaciones
                    $hoy = new DateTime();
                    $inicio_date = new DateTime($fecha_inicio);
                    $diff_dias = $hoy->diff($inicio_date)->days;

                    if ($inicio_date > $hoy && $diff_dias < 30) {
                        $respuesta = "
                            <div class='bg-red-50 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                                <p class='text-red-800 font-bold'><i class='fas fa-exclamation-circle mr-2'></i>Anticipación insuficiente</p>
                                <p class='text-sm text-red-700 mt-2'>Las vacaciones deben solicitarse con <strong>al menos 30 días de anticipación</strong>.</p>
                                <p class='text-sm text-red-700 mt-2'>Tu fecha de inicio es en $diff_dias días. Necesitas " . (30 - $diff_dias) . " días más de anticipación.</p>
                            </div>
                            <p class='text-gray-700 mt-3 text-sm'>Por favor, elige fechas con mayor anticipación.</p>
                        ";
                        unset($_SESSION['solicitud_en_proceso']);
                        $validacion_ok = false;
                    }
                }
            }

            if ($validacion_ok) {

            // Guardar datos validados
            $_SESSION['solicitud_en_proceso']['fecha_fin'] = $fecha_fin;
            $_SESSION['solicitud_en_proceso']['dias_habiles'] = $dias_habiles;

            // Determinar quién aprueba
            $aprobador = ($dias_habiles <= 2) ? 'Jefe Inmediato' : 'Recursos Humanos (RRHH)';

            // Verificar si requiere certificado
            $tipos_con_certificado = ['maternidad', 'paternidad', 'permiso_medico', 'jurado', 'duelo', 'cita_medica_hijo'];
            $requiere_certificado = in_array($tipo_permiso, $tipos_con_certificado);

            $respuesta = "
                <div class='bg-green-50 border-l-4 border-green-500 rounded-lg p-3 my-2'>
                    <p class='text-green-800 font-semibold'><i class='fas fa-check-circle mr-2'></i>Fecha de fin registrada</p>
                    <p class='text-sm text-green-700 mt-1'>" . date('d/m/Y', strtotime($fecha_fin)) . "</p>
                </div>

                <div class='bg-blue-50 border border-blue-200 rounded-lg p-4 my-3'>
                    <p class='text-blue-800 font-semibold mb-2'><i class='fas fa-calendar-check mr-2'></i>Resumen de tu solicitud:</p>
                    <div class='space-y-2 text-sm'>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Tipo:</span>
                            <span class='font-semibold text-gray-900'>" . ucfirst(str_replace('_', ' ', $tipo_permiso)) . "</span>
                        </div>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Del:</span>
                            <span class='font-semibold text-gray-900'>" . date('d/m/Y', strtotime($fecha_inicio)) . "</span>
                        </div>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Al:</span>
                            <span class='font-semibold text-gray-900'>" . date('d/m/Y', strtotime($fecha_fin)) . "</span>
                        </div>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Días hábiles:</span>
                            <span class='font-bold text-blue-600'>" . $dias_habiles . " días</span>
                        </div>
                        <div class='flex justify-between'>
                            <span class='text-gray-600'>Aprobador:</span>
                            <span class='font-semibold text-purple-600'>" . $aprobador . "</span>
                        </div>
                    </div>
                </div>

                " . ($requiere_certificado ? "
                <div class='bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-3 my-3'>
                    <p class='text-yellow-800 font-semibold'><i class='fas fa-exclamation-triangle mr-2'></i>Documentación requerida</p>
                    <p class='text-sm text-yellow-700 mt-2'>Este tipo de permiso <strong>REQUIERE certificado obligatorio</strong>.</p>
                    <p class='text-xs text-yellow-700 mt-2'>Asegúrate de adjuntarlo usando el botón de archivos 📎 antes de continuar.</p>
                </div>
                " : "") . "";

            // Para maternidad/paternidad, NO pedir motivo, procesar directamente
            if (in_array($tipo_permiso, ['maternidad', 'paternidad'])) {
                // Simular que se ingresó un motivo automático
                $_SESSION['solicitud_en_proceso']['motivo'] = ucfirst($tipo_permiso);

                $respuesta .= "
                    <p class='text-gray-700 mt-3 mb-2'>📎 <strong>Paso final: Adjuntar certificado</strong></p>
                    <p class='text-sm text-gray-600 mb-3'>Por favor, adjunta el certificado médico usando el botón de archivos 📎 y luego escribe 'confirmar' para crear la solicitud.</p>
                    <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
                ";
            } else {
                $respuesta .= "
                    <p class='text-gray-700 mt-3 mb-2'>Por último, necesito:</p>
                    <p class='text-gray-700 font-semibold mb-1'>✍️ Motivo de la solicitud</p>
                    <p class='text-xs text-gray-500 mb-3'>Escribe brevemente el motivo de tu permiso</p>
                    <p class='text-xs text-gray-400 mt-3'><i class='fas fa-reply mr-1'></i>Escribe <strong>'cancelar'</strong> para volver al menú principal</p>
                ";
            }
            } // cierre del if ($validacion_ok)
            } // cierre del else (fechas válidas)
        } elseif (!isset($_SESSION['solicitud_en_proceso']['motivo'])) {
            // Si ya tenemos ambas fechas, lo que sigue es el motivo
            // Esto se maneja en la siguiente sección
        }
    }

    // 4I. PROCESAR MOTIVO Y CREAR SOLICITUD (O CONFIRMAR PARA MATERNIDAD/PATERNIDAD)
    elseif (isset($_SESSION['solicitud_en_proceso']['fecha_inicio']) &&
            isset($_SESSION['solicitud_en_proceso']['fecha_fin'])) {

        $tipo_permiso = $_SESSION['solicitud_en_proceso']['tipo'] ?? 'otro';

        // Para maternidad/paternidad con motivo ya guardado, esperar "confirmar"
        $es_maternidad_paternidad = in_array($tipo_permiso, ['maternidad', 'paternidad']);
        $ya_tiene_motivo = isset($_SESSION['solicitud_en_proceso']['motivo']);

        if ($es_maternidad_paternidad && $ya_tiene_motivo) {
            // Requiere escribir "confirmar" con certificado adjunto
            if (!preg_match('/confirmar|crear|enviar|listo/i', $mensaje)) {
                $respuesta = "
                    <div class='bg-blue-50 border-l-4 border-blue-500 rounded-lg p-3 my-2'>
                        <p class='text-blue-800 font-semibold'><i class='fas fa-info-circle mr-2'></i>Esperando confirmación</p>
                        <p class='text-sm text-blue-700 mt-2'>Adjunta el certificado médico 📎 y escribe <strong>'confirmar'</strong> para crear la solicitud.</p>
                    </div>
                ";
            } else {
                // Continuar con la creación (usar el motivo ya guardado)
                $motivo = $_SESSION['solicitud_en_proceso']['motivo'];
            }
        } elseif (!$ya_tiene_motivo && strlen($mensaje) > 5) {
            // El mensaje es el motivo
            $motivo = $mensaje;
        } else {
            // Mensaje muy corto, pedir que escriba algo más largo
            $respuesta = "
                <div class='bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-3 my-2'>
                    <p class='text-yellow-800 font-semibold'><i class='fas fa-info-circle mr-2'></i>Motivo muy corto</p>
                    <p class='text-sm text-yellow-700 mt-2'>Por favor, escribe un motivo más detallado (mínimo 5 caracteres).</p>
                </div>
            ";
        }

        // Solo continuar si tenemos un motivo válido
        if (isset($motivo)) {
        $tipo_permiso = $_SESSION['solicitud_en_proceso']['tipo'] ?? 'otro';
        $fecha_inicio = $_SESSION['solicitud_en_proceso']['fecha_inicio'];
        $fecha_fin = $_SESSION['solicitud_en_proceso']['fecha_fin'];
        $dias_habiles = $_SESSION['solicitud_en_proceso']['dias_habiles'];

        // VALIDACIÓN: Verificar certificados obligatorios
        $tipos_con_certificado = ['maternidad', 'paternidad', 'permiso_medico', 'jurado', 'duelo', 'cita_medica_hijo'];
        $requiere_certificado = in_array($tipo_permiso, $tipos_con_certificado);

        // Verificar si hay archivos en la solicitud actual o en mensajes anteriores (sesión)
        $archivos_actuales = $data['archivos'] ?? [];
        $archivos_sesion = $_SESSION['solicitud_en_proceso']['archivos'] ?? [];
        $tiene_certificado = !empty($archivos_actuales) || !empty($archivos_sesion);

        $validacion_certificado_ok = true;
        if ($requiere_certificado && !$tiene_certificado) {
            $respuesta = "
                <div class='bg-red-50 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                    <p class='text-red-800 font-bold'><i class='fas fa-exclamation-circle mr-2'></i>Certificado obligatorio</p>
                    <p class='text-sm text-red-700 mt-2'>Este tipo de permiso <strong>REQUIERE</strong> adjuntar un certificado.</p>

                    <div class='mt-3 bg-white p-3 rounded-lg'>
                        <p class='text-xs font-semibold text-gray-700 mb-2'>Certificados requeridos según tipo:</p>
                        <ul class='text-xs text-gray-600 ml-4 space-y-1'>
                            <li>• <strong>Maternidad/Paternidad:</strong> Certificado médico</li>
                            <li>• <strong>Permiso médico:</strong> Órdenes médicas y anexos</li>
                            <li>• <strong>Jurado:</strong> Documento acreditativo</li>
                            <li>• <strong>Duelo:</strong> Certificado de defunción</li>
                            <li>• <strong>Cita médica hijo/a:</strong> Orden médica</li>
                        </ul>
                    </div>
                </div>
                <p class='text-gray-700 mt-3 text-sm'><i class='fas fa-paperclip mr-1'></i>Por favor, adjunta el certificado usando el botón de archivos 📎 y luego vuelve a escribir tu motivo.</p>
            ";
            // No limpiamos la sesión para que pueda intentar de nuevo
            unset($_SESSION['solicitud_en_proceso']['motivo']);
            $validacion_certificado_ok = false;
        }

        // VALIDACIÓN ADICIONAL: Para vacaciones, verificar disponibilidad del equipo
        $validacion_equipo_ok = true;
        if ($validacion_certificado_ok && $tipo_permiso === 'vacaciones') {
            // Obtener departamento del usuario
            $sql_dept = "SELECT departamento_id FROM usuarios WHERE id = ?";
            $stmt_dept = $conn->prepare($sql_dept);
            $stmt_dept->bind_param('i', $user['id']);
            $stmt_dept->execute();
            $dept_result = $stmt_dept->get_result()->fetch_assoc();
            $departamento_id = $dept_result['departamento_id'] ?? null;
            $stmt_dept->close();

            if ($departamento_id) {
                // Contar total de empleados en el departamento
                $sql_total = "SELECT COUNT(*) as total FROM usuarios WHERE departamento_id = ? AND activo = 1";
                $stmt_total = $conn->prepare($sql_total);
                $stmt_total->bind_param('i', $departamento_id);
                $stmt_total->execute();
                $total_empleados = $stmt_total->get_result()->fetch_assoc()['total'];
                $stmt_total->close();

                // Contar empleados que estarán de vacaciones en las mismas fechas
                $sql_ausentes = "SELECT COUNT(DISTINCT usuario_id) as ausentes
                                FROM solicitudes
                                WHERE tipo = 'vacaciones'
                                AND estado IN ('aprobado', 'pendiente')
                                AND usuario_id IN (SELECT id FROM usuarios WHERE departamento_id = ? AND activo = 1)
                                AND (
                                    (fecha_inicio <= ? AND fecha_fin >= ?) OR
                                    (fecha_inicio <= ? AND fecha_fin >= ?) OR
                                    (fecha_inicio >= ? AND fecha_fin <= ?)
                                )";

                $stmt_ausentes = $conn->prepare($sql_ausentes);
                $stmt_ausentes->bind_param('issssss',
                    $departamento_id,
                    $fecha_fin, $fecha_inicio,
                    $fecha_fin, $fecha_fin,
                    $fecha_inicio, $fecha_fin
                );
                $stmt_ausentes->execute();
                $empleados_ausentes = $stmt_ausentes->get_result()->fetch_assoc()['ausentes'];
                $stmt_ausentes->close();

                // Calcular disponibilidad (incluyendo al solicitante como ausente)
                $empleados_disponibles = $total_empleados - ($empleados_ausentes + 1);
                $porcentaje_disponible = ($total_empleados > 0) ? ($empleados_disponibles / $total_empleados) * 100 : 0;

                if ($porcentaje_disponible < 70) {
                    $respuesta = "
                        <div class='bg-red-50 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                            <p class='text-red-800 font-bold'><i class='fas fa-exclamation-circle mr-2'></i>Disponibilidad insuficiente</p>
                            <p class='text-sm text-red-700 mt-2'>No se puede aprobar: El <strong>70% del equipo debe estar disponible</strong>.</p>

                            <div class='mt-3 bg-white p-3 rounded-lg'>
                                <p class='text-sm font-semibold text-gray-700 mb-2'>Estado del departamento:</p>
                                <div class='space-y-1 text-xs text-gray-600'>
                                    <p><strong>Total empleados:</strong> " . $total_empleados . "</p>
                                    <p><strong>Ya ausentes en esas fechas:</strong> " . $empleados_ausentes . "</p>
                                    <p><strong>Disponibles si te vas:</strong> " . $empleados_disponibles . " (" . round($porcentaje_disponible, 1) . "%)</p>
                                    <p class='text-red-600 font-semibold'><strong>Requerido:</strong> Mínimo 70%</p>
                                </div>
                            </div>
                        </div>
                        <p class='text-gray-700 mt-3 text-sm'>Por favor, elige otras fechas cuando haya más disponibilidad en tu departamento.</p>
                    ";
                    unset($_SESSION['solicitud_en_proceso']);
                    $validacion_equipo_ok = false;
                }
            }
        }

        // Crear la solicitud en la base de datos solo si todas las validaciones pasaron
        if ($validacion_certificado_ok && $validacion_equipo_ok) {
        try {
            $_SESSION['solicitud_en_proceso']['motivo'] = $motivo;

            // Determinar aprobador según días
            $aprobador_id = ($dias_habiles <= 2) ? 2 : 3; // 2 = jefe, 3 = RRHH

            $sql = "INSERT INTO solicitudes (usuario_id, tipo, fecha_inicio, fecha_fin, dias_habiles, dias, motivo, estado, aprobador_id, fecha_creacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', ?, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isssidsi',
                $user['id'],
                $tipo_permiso,
                $fecha_inicio,
                $fecha_fin,
                $dias_habiles,
                $dias_habiles,
                $motivo,
                $aprobador_id
            );

            if ($stmt->execute()) {
                $solicitud_id = $conn->insert_id;

                $respuesta = "
                    <div class='bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 rounded-lg p-4 my-3'>
                        <p class='text-green-800 font-bold text-lg mb-3'><i class='fas fa-check-circle mr-2'></i>¡Solicitud Creada Exitosamente!</p>

                        <div class='bg-white p-4 rounded-lg shadow-sm mb-3'>
                            <div class='space-y-2 text-sm'>
                                <div class='flex justify-between items-center pb-2 border-b border-gray-200'>
                                    <span class='text-gray-600'>ID de Solicitud:</span>
                                    <span class='font-bold text-green-600'>#" . $solicitud_id . "</span>
                                </div>
                                <div class='flex justify-between'>
                                    <span class='text-gray-600'>Tipo:</span>
                                    <span class='font-semibold text-gray-900'>" . ucfirst(str_replace('_', ' ', $tipo_permiso)) . "</span>
                                </div>
                                <div class='flex justify-between'>
                                    <span class='text-gray-600'>Del:</span>
                                    <span class='font-semibold text-gray-900'>" . date('d/m/Y', strtotime($fecha_inicio)) . "</span>
                                </div>
                                <div class='flex justify-between'>
                                    <span class='text-gray-600'>Al:</span>
                                    <span class='font-semibold text-gray-900'>" . date('d/m/Y', strtotime($fecha_fin)) . "</span>
                                </div>
                                <div class='flex justify-between'>
                                    <span class='text-gray-600'>Días hábiles:</span>
                                    <span class='font-bold text-blue-600'>" . $dias_habiles . " días</span>
                                </div>
                                <div class='flex justify-between'>
                                    <span class='text-gray-600'>Estado:</span>
                                    <span class='px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold'>PENDIENTE</span>
                                </div>
                            </div>
                        </div>

                        <div class='bg-blue-50 border border-blue-200 rounded-lg p-3'>
                            <p class='text-xs text-blue-800'><i class='fas fa-info-circle mr-1'></i><strong>Próximos pasos:</strong></p>
                            <p class='text-xs text-blue-700 mt-1'>Tu solicitud será revisada por " . (($dias_habiles <= 2) ? 'tu Jefe Inmediato' : 'Recursos Humanos') . ". Recibirás una notificación cuando sea aprobada o rechazada.</p>
                        </div>
                    </div>
                ";

                // Limpiar sesión
                unset($_SESSION['solicitud_en_proceso']);

            } else {
                $respuesta = "
                    <div class='bg-red-50 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                        <p class='text-red-800 font-bold'><i class='fas fa-exclamation-circle mr-2'></i>Error al crear la solicitud</p>
                        <p class='text-sm text-red-700 mt-2'>Por favor, intenta nuevamente o contacta a soporte.</p>
                    </div>
                ";
                unset($_SESSION['solicitud_en_proceso']);
            }

            $stmt->close();

        } catch (Exception $e) {
            error_log("Error creando solicitud: " . $e->getMessage());
            $respuesta = "
                <div class='bg-red-50 border-l-4 border-red-500 rounded-lg p-4 my-3'>
                    <p class='text-red-800 font-bold'><i class='fas fa-exclamation-circle mr-2'></i>Error al crear la solicitud</p>
                    <p class='text-sm text-red-700 mt-2'>Error: " . $e->getMessage() . "</p>
                </div>
            ";
            unset($_SESSION['solicitud_en_proceso']);
        }
        } // cierre del if ($validacion_certificado_ok && $validacion_equipo_ok)
        } // cierre del if (isset($motivo))
    }

    // 5. SOLICITAR VACACIONES CON FECHAS (FORMATO ESPECÍFICO)
    elseif (preg_match('/vacaciones del ([\d-]+) al ([\d-]+)/i', $mensaje, $matches)) {
        // Este bloque maneja el formato específico: "vacaciones del DD/MM/AAAA al DD/MM/AAAA"
        // Ya no intercepta archivos genéricos
        $respuesta = "
            <p class='text-gray-700'>Para procesar tu solicitud de vacaciones, por favor accede al módulo de Solicitudes donde podrás:</p>
            <ul class='list-disc list-inside text-gray-700 ml-4 mt-2'>
                <li>Ver tus días disponibles en tiempo real</li>
                <li>Calcular automáticamente los días hábiles</li>
                <li>Ver quién aprobará tu solicitud</li>
                <li>Verificar disponibilidad del departamento</li>
            </ul>
            <div class='mt-4'>
                <a href='solicitudes.php' class='inline-block px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition'>
                    <i class='fas fa-plus mr-2'></i>Ir a Solicitudes
                </a>
            </div>
        ";
    }

    // 6. PREGUNTAS SOBRE APROBADORES
    elseif (preg_match('/qui[ée]n.*aprueba|aprobador|jefe|rrhh/i', $mensaje)) {

        $respuesta = "
            <div class='bg-gradient-to-r from-purple-50 to-purple-100 border-l-4 border-purple-500 rounded-lg p-4 my-3'>
                <p class='font-bold text-purple-800 mb-3'><i class='fas fa-user-check mr-2'></i>Proceso de Aprobación ComfaChoco</p>
                <div class='text-sm text-purple-700 space-y-3 ml-2'>
                    <div class='bg-white p-3 rounded-lg shadow-sm'>
                        <p class='font-semibold'>📌 Permisos de 0-2 días</p>
                        <p class='text-xs mt-1'>→ Aprueba: <strong>Jefe Inmediato</strong></p>
                    </div>
                    <div class='bg-white p-3 rounded-lg shadow-sm'>
                        <p class='font-semibold'>📌 Permisos de 3+ días</p>
                        <p class='text-xs mt-1'>→ Aprueba: <strong>Recursos Humanos (RRHH)</strong></p>
                    </div>
                    <div class='bg-white p-3 rounded-lg shadow-sm'>
                        <p class='font-semibold'>🏖️ Vacaciones</p>
                        <p class='text-xs mt-1'>→ Siempre aprueba: <strong>RRHH</strong></p>
                        <p class='text-xs text-purple-600 mt-2'><i class='fas fa-clock mr-1'></i>Se solicita inmediatamente al cumplir el periodo, pero debe tomarse con <strong>30 días de anticipación</strong></p>
                    </div>
                    <div class='bg-white p-3 rounded-lg shadow-sm'>
                        <p class='font-semibold'>👶 Maternidad/Paternidad, Jurado</p>
                        <p class='text-xs mt-1'>→ Aprueba: <strong>RRHH</strong></p>
                    </div>
                </div>
            </div>
        ";
    }

    // 7. RESPUESTA POR DEFECTO - SUGERENCIAS
    else {
        $respuesta = "
            <div class='bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 my-3'>
                <p class='text-blue-800'><i class='fas fa-info-circle mr-2'></i>No entendí tu consulta.</p>
                <p class='text-sm text-blue-700 mt-2'>Intenta hacer preguntas más específicas como:</p>
                <ul class='text-sm text-blue-700 mt-2 ml-4 space-y-1'>
                    <li>• ¿Cuántos días de vacaciones tengo?</li>
                    <li>• ¿Cuáles son mis solicitudes?</li>
                    <li>• ¿Cuáles son las políticas?</li>
                    <li>• ¿Quién aprueba mis permisos?</li>
                </ul>
            </div>
        ";
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'respuesta' => $respuesta,
        'tipo' => $tipo,
        'datos' => $datos_adicionales
    ]);

} catch (Exception $e) {
    error_log("Error en asistente_chat.php: " . $e->getMessage());
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
