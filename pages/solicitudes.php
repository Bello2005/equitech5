<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mock_data.php';

requireLogin();
$usuario = getCurrentUser();

if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80';
}

$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

$page_title = 'Solicitudes';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

// Inicializar variables
$solicitudes = [];
$stats = [
    'pendientes' => 0,
    'aprobadas' => 0,
    'rechazadas' => 0,
    'total_mes' => 0
];

// Cargar solicitudes y estadísticas desde la base de datos
try {
    $conn = getConnection();

    // Cargar estadísticas
    $sql_stats = "SELECT
        COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
        COUNT(CASE WHEN estado = 'aprobado' THEN 1 END) as aprobadas,
        COUNT(CASE WHEN estado = 'rechazado' THEN 1 END) as rechazadas,
        COUNT(CASE WHEN MONTH(fecha_creacion) = MONTH(CURRENT_DATE())
                   AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE()) THEN 1 END) as total_mes
    FROM solicitudes";

    $result_stats = $conn->query($sql_stats);
    if ($result_stats) {
        $stats_row = $result_stats->fetch_assoc();
        $stats['pendientes'] = (int)$stats_row['pendientes'];
        $stats['aprobadas'] = (int)$stats_row['aprobadas'];
        $stats['rechazadas'] = (int)$stats_row['rechazadas'];
        $stats['total_mes'] = (int)$stats_row['total_mes'];
    }

    // Cargar solicitudes con información completa
    $sql = "SELECT
        s.id,
        s.usuario_id,
        s.tipo,
        s.fecha_inicio,
        s.fecha_fin,
        s.dias,
        s.motivo,
        s.estado,
        s.prioridad,
        s.fecha_creacion,
        COALESCE(u.nombre, CONCAT('Usuario ', s.usuario_id)) AS empleado,
        u.email,
        COALESCE(aprobador.nombre, '') AS aprobador,
        s.fecha_aprobacion
    FROM solicitudes s
    LEFT JOIN usuarios u ON u.id = s.usuario_id
    LEFT JOIN usuarios aprobador ON aprobador.id = s.aprobador_id
    ORDER BY s.fecha_creacion DESC
    LIMIT 200";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $solicitudes = [];
        while ($r = $result->fetch_assoc()) {
            // Obtener iniciales del empleado
            $nombre_emp = $r['empleado'];
            $iniciales = 'NA';
            if (!is_numeric($nombre_emp)) {
                $partes = explode(' ', $nombre_emp);
                $iniciales = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
            }

            $solicitudes[] = [
                'id' => (int)$r['id'],
                'usuario_id' => (int)$r['usuario_id'],
                'empleado' => $nombre_emp,
                'email' => $r['email'] ?? '',
                'avatar' => $iniciales,
                'tipo' => ucfirst(str_replace('_', ' ', $r['tipo'])),
                'fecha_inicio' => $r['fecha_inicio'],
                'fecha_fin' => $r['fecha_fin'],
                'dias' => (int)$r['dias'],
                'motivo' => $r['motivo'] ?? '',
                'estado' => $r['estado'],
                'prioridad' => $r['prioridad'],
                'aprobador' => $r['aprobador'],
                'fecha_aprobacion' => $r['fecha_aprobacion'],
                'fecha_creacion' => $r['fecha_creacion']
            ];
        }
    }

    $conn->close();
} catch (Exception $e) {
    // En caso de error, usar datos mock
    error_log("Error cargando solicitudes: " . $e->getMessage());
    // Los valores por defecto ya están inicializados arriba
}
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Solicitudes</h1>
            <p class="text-gray-600">Administra las solicitudes de vacaciones, permisos y ausencias de tu equipo.</p>
        </div>

        <!-- Stats Cards - Datos desde la BD -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pendientes</p>
                        <p class="text-3xl font-bold text-yellow-600"><?= $stats['pendientes'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Aprobadas</p>
                        <p class="text-3xl font-bold text-green-600"><?= $stats['aprobadas'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Rechazadas</p>
                        <p class="text-3xl font-bold text-red-600"><?= $stats['rechazadas'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Mes</p>
                        <p class="text-3xl font-bold text-blue-600"><?= $stats['total_mes'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Solicitudes Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 lg:mb-0">
                        Todas las Solicitudes
                    </h3>

                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Empleado</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Periodo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Días</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Motivo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($solicitudes)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                <p>No hay solicitudes registradas</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($solicitudes as $solicitud): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-primary-dark rounded-lg flex items-center justify-center text-white text-sm font-semibold mr-3">
                                            <?= $solicitud['avatar'] ?>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitud['empleado']) ?></span>
                                            <?php if (!empty($solicitud['email'])): ?>
                                            <span class="text-xs text-gray-500"><?= htmlspecialchars($solicitud['email']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900"><?= htmlspecialchars($solicitud['tipo']) ?></span>
                                    <?php if ($solicitud['prioridad'] === 'alta'): ?>
                                    <span class="ml-1 inline-block w-2 h-2 bg-red-500 rounded-full" title="Prioridad alta"></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= date('d M Y', strtotime($solicitud['fecha_inicio'])) ?></div>
                                    <div class="text-xs text-gray-500">hasta <?= date('d M Y', strtotime($solicitud['fecha_fin'])) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= $solicitud['dias'] ?> día<?= $solicitud['dias'] > 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <span class="text-sm text-gray-600 line-clamp-2" title="<?= htmlspecialchars($solicitud['motivo']) ?>">
                                        <?= !empty($solicitud['motivo']) ? htmlspecialchars(substr($solicitud['motivo'], 0, 50)) . (strlen($solicitud['motivo']) > 50 ? '...' : '') : '-' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="badge-<?= $solicitud['estado'] ?> px-3 py-1 rounded-full text-xs font-semibold">
                                        <?= ucfirst($solicitud['estado']) ?>
                                    </span>
                                    <?php if (!empty($solicitud['aprobador']) && $solicitud['estado'] !== 'pendiente'): ?>
                                    <div class="text-xs text-gray-500 mt-1">por <?= htmlspecialchars($solicitud['aprobador']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="text-primary hover:text-primary-dark mr-3 btn-ver-detalle"
                                            data-id="<?= $solicitud['id'] ?>"
                                            data-empleado="<?= htmlspecialchars($solicitud['empleado']) ?>"
                                            data-tipo="<?= htmlspecialchars($solicitud['tipo']) ?>"
                                            data-inicio="<?= $solicitud['fecha_inicio'] ?>"
                                            data-fin="<?= $solicitud['fecha_fin'] ?>"
                                            data-dias="<?= $solicitud['dias'] ?>"
                                            data-motivo="<?= htmlspecialchars($solicitud['motivo']) ?>"
                                            data-estado="<?= $solicitud['estado'] ?>"
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <?php if ($solicitud['estado'] === 'pendiente'): ?>
                                        <button type="button" class="btn-approve inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition mr-2" data-id="<?= $solicitud['id'] ?>" title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn-reject inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition" data-id="<?= $solicitud['id'] ?>" title="Rechazar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Script de validación en tiempo real -->
<script src="../assets/js/solicitudes-validacion.js"></script>

<!-- Modal para nueva solicitud -->
<div id="modal-nueva-solicitud" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-6 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-2xl bg-white mb-10">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Nueva Solicitud</h3>
                <button id="close-modal-nueva" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Card de días disponibles -->
            <div id="info-dias-disponibles" class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-xl p-4 mb-6 hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-700 font-medium mb-1">Tus días de vacaciones disponibles</p>
                        <p class="text-3xl font-bold text-green-800"><span id="dias-disponibles-numero">0</span> días</p>
                        <p class="text-xs text-green-600 mt-1">
                            <span id="periodos-info"></span>
                        </p>
                    </div>
                    <div class="w-16 h-16 bg-green-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-umbrella-beach text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <form id="form-nueva-solicitud" class="space-y-5">
                <!-- Tipo de permiso -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipo de permiso <span class="text-red-500">*</span>
                    </label>
                    <select id="tipo-permiso" name="tipo" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition">
                        <option value="">Selecciona un tipo</option>
                        <option value="vacaciones">Vacaciones</option>
                        <option value="permiso_medico">Permiso Médico</option>
                        <option value="maternidad">Maternidad</option>
                        <option value="paternidad">Paternidad</option>
                        <option value="duelo">Duelo</option>
                        <option value="jurado">Jurado</option>
                        <option value="cita_medica_hijo">Cita Médica de Hijo/a</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <!-- Fechas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Fecha inicio <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="fecha-inicio" name="fecha_inicio" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Fecha fin <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="fecha-fin" name="fecha_fin" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition">
                    </div>
                </div>

                <!-- Días calculados -->
                <div id="info-dias-calculados" class="bg-blue-50 border border-blue-200 rounded-xl p-4 hidden">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-check text-blue-600 text-xl mr-3"></i>
                        <div>
                            <p class="text-sm font-semibold text-blue-900">Días hábiles calculados</p>
                            <p class="text-2xl font-bold text-blue-700"><span id="dias-habiles-calculados">0</span> días</p>
                            <p class="text-xs text-blue-600">Los sábados cuentan como días hábiles</p>
                        </div>
                    </div>
                </div>

                <!-- Motivo -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Motivo <span class="text-red-500">*</span>
                    </label>
                    <textarea id="motivo" name="motivo" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition" placeholder="Describe brevemente el motivo de tu solicitud"></textarea>
                </div>

                <!-- Documento requerido -->
                <div id="info-documento" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 hidden">
                    <div class="flex items-start">
                        <i class="fas fa-file-medical text-yellow-600 text-xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-yellow-900 mb-1">Documento requerido</p>
                            <p class="text-sm text-yellow-700" id="tipo-documento-texto">Este tipo de permiso requiere documentación</p>
                            <div class="mt-3">
                                <label class="block text-xs font-medium text-yellow-800 mb-2">Adjuntar documento</label>
                                <input type="file" id="documento-adjunto" name="documento" accept=".pdf,.jpg,.jpeg,.png" class="text-sm text-yellow-700">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aprobador asignado -->
                <div id="info-aprobador" class="bg-purple-50 border border-purple-200 rounded-xl p-4 hidden">
                    <div class="flex items-center">
                        <i class="fas fa-user-check text-purple-600 text-xl mr-3"></i>
                        <div>
                            <p class="text-xs text-purple-700 font-medium mb-1">Será revisado por</p>
                            <p class="text-lg font-bold text-purple-900" id="aprobador-texto">-</p>
                        </div>
                    </div>
                </div>

                <!-- Disponibilidad departamento -->
                <div id="info-disponibilidad-dept" class="hidden">
                    <!-- Se llenará dinámicamente -->
                </div>

                <!-- Errores de validación -->
                <div id="validation-errors" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-600 text-xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-red-900 mb-2">Errores de validación</p>
                            <ul id="error-list" class="list-disc list-inside text-sm text-red-700 space-y-1">
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Warnings -->
                <div id="validation-warnings" class="hidden bg-orange-50 border border-orange-200 rounded-xl p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-orange-600 text-xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-orange-900 mb-2">Advertencias</p>
                            <ul id="warning-list" class="list-disc list-inside text-sm text-orange-700 space-y-1">
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" id="btn-cancelar-nueva" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-submit-solicitud" class="px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition shadow-sm">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de solicitud -->
<div id="modal-detalle" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-2xl bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Detalles de la Solicitud</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modal-content" class="text-sm text-gray-700">
                <!-- El contenido se cargará dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
// Modal de detalles y acciones de aprobación/rechazo
document.addEventListener('DOMContentLoaded', function() {
    // Modal de detalles
    const modal = document.getElementById('modal-detalle');
    const modalContent = document.getElementById('modal-content');
    const closeModal = document.getElementById('close-modal');

    // Cerrar modal
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
    }

    // Cerrar modal al hacer clic fuera
    modal?.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });

    // Delegation para botones
    document.body.addEventListener('click', async function(e) {
        const verDetalleBtn = e.target.closest('.btn-ver-detalle');
        const approveBtn = e.target.closest('.btn-approve');
        const rejectBtn = e.target.closest('.btn-reject');

        // Ver detalles
        if (verDetalleBtn) {
            const empleado = verDetalleBtn.dataset.empleado;
            const tipo = verDetalleBtn.dataset.tipo;
            const inicio = verDetalleBtn.dataset.inicio;
            const fin = verDetalleBtn.dataset.fin;
            const dias = verDetalleBtn.dataset.dias;
            const motivo = verDetalleBtn.dataset.motivo;
            const estado = verDetalleBtn.dataset.estado;

            const estadoBadge = estado === 'pendiente' ? 'badge-pendiente' :
                               estado === 'aprobado' ? 'badge-aprobado' : 'badge-rechazado';

            modalContent.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <p class="text-xs text-gray-500">Empleado</p>
                            <p class="font-semibold text-gray-900">${empleado}</p>
                        </div>
                        <span class="${estadoBadge} px-3 py-1 rounded-full text-xs font-semibold">
                            ${estado.charAt(0).toUpperCase() + estado.slice(1)}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Tipo de solicitud</p>
                        <p class="font-medium text-gray-900">${tipo}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Fecha inicio</p>
                            <p class="font-medium text-gray-900">${new Date(inicio).toLocaleDateString('es-ES', {year: 'numeric', month: 'long', day: 'numeric'})}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Fecha fin</p>
                            <p class="font-medium text-gray-900">${new Date(fin).toLocaleDateString('es-ES', {year: 'numeric', month: 'long', day: 'numeric'})}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Duración</p>
                        <p class="font-medium text-gray-900">${dias} día${dias > 1 ? 's' : ''}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Motivo</p>
                        <p class="text-gray-900">${motivo || 'Sin motivo especificado'}</p>
                    </div>
                </div>
            `;
            modal.classList.remove('hidden');
        }

        // Aprobar solicitud
        if (approveBtn) {
            const id = approveBtn.getAttribute('data-id');
            if (!id) return alert('ID de solicitud no disponible');
            if (!confirm('¿Aprobar esta solicitud?')) return;
            try {
                const res = await fetch('api/solicitud_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, action: 'approve' })
                });
                if (!res.ok) throw new Error('Error al aprobar');
                const data = await res.json();
                if (data && data.success) {
                    alert('Solicitud aprobada');
                    location.reload();
                } else {
                    alert(data.message || 'Error al aprobar la solicitud');
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al aprobar solicitud');
            }
        }

        if (rejectBtn) {
            const id = rejectBtn.getAttribute('data-id');
            if (!id) return alert('ID de solicitud no disponible');
            const motivo = prompt('Motivo del rechazo (opcional):', '');
            if (!confirm('¿Rechazar esta solicitud?')) return;
            try {
                const res = await fetch('api/solicitud_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, action: 'reject', motivo: motivo })
                });
                if (!res.ok) throw new Error('Error al rechazar');
                const data = await res.json();
                if (data && data.success) {
                    alert('Solicitud rechazada');
                    location.reload();
                } else {
                    alert(data.message || 'Error al rechazar la solicitud');
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al rechazar solicitud');
            }
        }
    });

    // Manejar parámetro 'tipo' en la URL para abrir automáticamente el modal
    const urlParams = new URLSearchParams(window.location.search);
    const tipoParam = urlParams.get('tipo');

    if (tipoParam) {
        // Abrir el modal de nueva solicitud
        const modal = document.getElementById('modal-nueva-solicitud');
        if (modal) {
            modal.classList.remove('hidden');
        }

        // Pre-seleccionar el tipo de permiso
        const selectTipo = document.getElementById('tipo-permiso');
        if (selectTipo) {
            // Mapear los tipos del asistente a los valores del select
            const tipoMap = {
                'vacaciones': 'vacaciones',
                'maternidad_paternidad': 'maternidad',
                'maternidad': 'maternidad',
                'paternidad': 'paternidad',
                'medico': 'permiso_medico',
                'permiso_medico': 'permiso_medico',
                'jurado': 'jurado',
                'duelo': 'duelo',
                'teletrabajo': 'otro',
                'cita_medica_hijo': 'cita_medica_hijo'
            };

            const valorSeleccionar = tipoMap[tipoParam.toLowerCase()] || tipoParam;
            selectTipo.value = valorSeleccionar;

            // Disparar evento change para actualizar la UI si es necesario
            selectTipo.dispatchEvent(new Event('change'));
        }

        // Limpiar la URL para evitar que se reabra el modal al recargar
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>
