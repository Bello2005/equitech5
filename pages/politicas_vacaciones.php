<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
$usuario = getCurrentUser();

if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80';
}

$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

$page_title = 'Políticas de Vacaciones';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

// Cargar políticas de vacaciones por tipo de empleado
$politicas_vacaciones = [];
$tipos_empleado = [];
$reglas_aprobacion = [];

try {
    $conn = getConnection();

    // Cargar tipos de empleado
    $sql_tipos = "SELECT * FROM tipos_empleado WHERE activo = 1 ORDER BY nombre";
    $result_tipos = $conn->query($sql_tipos);
    if ($result_tipos) {
        while ($row = $result_tipos->fetch_assoc()) {
            $tipos_empleado[] = $row;
        }
    }

    // Cargar políticas de vacaciones con tipo de empleado
    $sql_politicas = "SELECT pv.*, te.nombre as tipo_empleado_nombre
                      FROM politicas_vacaciones pv
                      JOIN tipos_empleado te ON te.id = pv.tipo_empleado_id
                      WHERE pv.activo = 1
                      ORDER BY te.nombre";
    $result_politicas = $conn->query($sql_politicas);
    if ($result_politicas) {
        while ($row = $result_politicas->fetch_assoc()) {
            $politicas_vacaciones[] = $row;
        }
    }

    // Cargar reglas de aprobación
    $sql_reglas = "SELECT * FROM reglas_aprobacion WHERE activo = 1 ORDER BY tipo_permiso, dias_minimos";
    $result_reglas = $conn->query($sql_reglas);
    if ($result_reglas) {
        while ($row = $result_reglas->fetch_assoc()) {
            $reglas_aprobacion[] = $row;
        }
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Error cargando políticas: " . $e->getMessage());
}
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Políticas de Vacaciones y Permisos</h1>
            <p class="text-gray-600">Políticas configuradas por tipo de empleado y reglas de aprobación</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <nav class="flex space-x-4" aria-label="Tabs">
                <button onclick="showTab('vacaciones')" id="tab-vacaciones" class="tab-button active px-3 py-2 font-medium text-sm rounded-md">
                    <i class="fas fa-umbrella-beach mr-2"></i>
                    Políticas de Vacaciones
                </button>
                <button onclick="showTab('aprobacion')" id="tab-aprobacion" class="tab-button px-3 py-2 font-medium text-sm rounded-md">
                    <i class="fas fa-check-circle mr-2"></i>
                    Reglas de Aprobación
                </button>
                <button onclick="showTab('restricciones')" id="tab-restricciones" class="tab-button px-3 py-2 font-medium text-sm rounded-md">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Restricciones
                </button>
            </nav>
        </div>

        <!-- Tab: Políticas de Vacaciones -->
        <div id="content-vacaciones" class="tab-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($politicas_vacaciones as $politica): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($politica['tipo_empleado_nombre']) ?></h3>
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-xs font-medium text-white">
                                <?= $politica['dias_por_periodo'] ?> días/año
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Días por período</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $politica['dias_por_periodo'] ?> días</p>
                            <p class="text-xs text-gray-500">Cada <?= $politica['periodo_dias'] ?> días (≈ 1 año)</p>
                        </div>

                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-sm text-gray-600">Acumulable</span>
                            <span class="text-sm font-medium <?= $politica['es_acumulable'] ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $politica['es_acumulable'] ? 'Sí' : 'No' ?>
                            </span>
                        </div>

                        <?php if ($politica['es_acumulable']): ?>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-sm text-gray-600">Máx. períodos acumulables</span>
                            <span class="text-sm font-medium text-gray-900"><?= $politica['max_periodos_acumulables'] ?> períodos</span>
                        </div>
                        <?php endif; ?>

                        <?php if ($politica['observaciones']): ?>
                        <div class="pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                <?= nl2br(htmlspecialchars($politica['observaciones'])) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex justify-between items-center">
                        <span class="text-xs text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>
                            Actualizado <?= date('d/m/Y', strtotime($politica['fecha_actualizacion'])) ?>
                        </span>
                        <?php if ($usuario['rol'] === 'admin'): ?>
                        <button onclick="editarPoliticaVacaciones(<?= $politica['id'] ?>)" class="text-primary hover:text-primary-dark text-sm">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Info importante -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="font-semibold text-blue-900 mb-3 flex items-center">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Información Importante
                </h4>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                        <span>Los <strong>sábados cuentan como días hábiles</strong></span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                        <span>Las vacaciones se pueden solicitar <strong>apenas se cumpla el período</strong></span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                        <span>Debe solicitar con <strong>30 días de anticipación</strong> como mínimo</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-600 mr-2 mt-1"></i>
                        <span>Los días son <strong>acumulables hasta 2 períodos máximo</strong></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab: Reglas de Aprobación -->
        <div id="content-aprobacion" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Permiso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duración</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprobador</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anticipación</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($reglas_aprobacion as $regla): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">
                                    <?= ucfirst(str_replace('_', ' ', $regla['tipo_permiso'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php if ($regla['dias_maximos']): ?>
                                    <?= $regla['dias_minimos'] ?> - <?= $regla['dias_maximos'] ?> días
                                <?php else: ?>
                                    ≥ <?= $regla['dias_minimos'] ?> días
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $regla['aprobador_rol'] === 'rrhh' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= $regla['aprobador_rol'] === 'rrhh' ? 'RRHH' : 'Jefe Inmediato' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php if ($regla['requiere_documento']): ?>
                                    <span class="text-red-600">
                                        <i class="fas fa-file-alt mr-1"></i>
                                        <?= ucfirst(str_replace('_', ' ', $regla['tipo_documento'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">No requiere</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= $regla['dias_anticipacion_minima'] > 0 ? $regla['dias_anticipacion_minima'] . ' días' : 'Sin mínimo' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Resumen de reglas -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h4 class="font-semibold text-blue-900 mb-3">Permisos Cortos (≤ 2 días)</h4>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li><i class="fas fa-user-tie mr-2"></i>Aprueba: <strong>Jefe Inmediato</strong></li>
                        <li><i class="fas fa-file-medical mr-2"></i>Permisos médicos requieren certificado</li>
                        <li><i class="fas fa-child mr-2"></i>Citas médicas de hijos requieren certificado</li>
                    </ul>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                    <h4 class="font-semibold text-purple-900 mb-3">Permisos Largos (≥ 3 días)</h4>
                    <ul class="space-y-2 text-sm text-purple-800">
                        <li><i class="fas fa-building mr-2"></i>Aprueba: <strong>Recursos Humanos</strong></li>
                        <li><i class="fas fa-calendar-alt mr-2"></i>Vacaciones: 30 días de anticipación</li>
                        <li><i class="fas fa-file-medical-alt mr-2"></i>Todos requieren certificado/documento</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tab: Restricciones -->
        <div id="content-restricciones" class="tab-content hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Disponibilidad Mínima</h3>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-4xl font-bold text-green-600">70%</p>
                            <p class="text-sm text-gray-600">del personal debe estar disponible</p>
                        </div>
                        <p class="text-sm text-gray-700">
                            Se debe mantener un mínimo del 70% de los funcionarios disponibles en todo momento para garantizar la operación normal.
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Restricción por Departamento</h3>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-building text-orange-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-4xl font-bold text-orange-600">1</p>
                            <p class="text-sm text-gray-600">persona máximo por departamento</p>
                        </div>
                        <p class="text-sm text-gray-700">
                            No pueden estar de vacaciones varias personas del mismo departamento simultáneamente. Máximo una persona por vez.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<style>
.tab-button {
    background-color: #f3f4f6;
    color: #6b7280;
    transition: all 0.2s;
}

.tab-button.active {
    background-color: #0B8A3A;
    color: white;
}

.tab-button:hover:not(.active) {
    background-color: #e5e7eb;
}
</style>

<script>
function showTab(tabName) {
    // Ocultar todos los contenidos
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));

    // Mostrar el seleccionado
    document.getElementById('content-' + tabName).classList.remove('hidden');
    document.getElementById('tab-' + tabName).classList.add('active');
}

function editarPoliticaVacaciones(id) {
    alert('Función de edición en desarrollo. ID: ' + id);
}
</script>
