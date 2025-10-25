<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
$usuario = getCurrentUser();
$page_title = 'Políticas de Vacaciones y Permisos';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

// Cargar políticas de vacaciones y reglas de aprobación
$politicas_vacaciones = [];
$reglas_aprobacion = [];
$tipos_empleado = [];

try {
    $conn = getConnection();

    // Cargar políticas de vacaciones por tipo de empleado
    $sql_politicas = "SELECT pv.*, te.nombre as tipo_empleado_nombre, te.descripcion as tipo_empleado_desc
                      FROM politicas_vacaciones pv
                      JOIN tipos_empleado te ON te.id = pv.tipo_empleado_id
                      WHERE pv.activo = 1
                      ORDER BY te.nombre";
    $result = $conn->query($sql_politicas);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $politicas_vacaciones[] = $row;
        }
    }

    // Cargar reglas de aprobación
    $sql_reglas = "SELECT * FROM reglas_aprobacion ORDER BY tipo_permiso, dias_minimos";
    $result = $conn->query($sql_reglas);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reglas_aprobacion[] = $row;
        }
    }

    // Cargar tipos de empleado
    $sql_tipos = "SELECT * FROM tipos_empleado WHERE activo = 1 ORDER BY nombre";
    $result = $conn->query($sql_tipos);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tipos_empleado[] = $row;
        }
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Error cargando políticas: " . $e->getMessage());
}
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Encabezado -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Políticas de Vacaciones y Permisos</h1>
            <p class="text-gray-600">Consulta las políticas de vacaciones por tipo de empleado y reglas de aprobación</p>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tipos de Empleado</p>
                        <p class="text-3xl font-bold text-primary"><?= count($tipos_empleado) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Políticas de Vacaciones</p>
                        <p class="text-3xl font-bold text-green-600"><?= count($politicas_vacaciones) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-umbrella-beach text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Reglas de Aprobación</p>
                        <p class="text-3xl font-bold text-blue-600"><?= count($reglas_aprobacion) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Políticas de Vacaciones -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Políticas de Vacaciones por Tipo de Empleado</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($politicas_vacaciones as $politica): ?>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-user-tie text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($politica['tipo_empleado_nombre']) ?></h3>
                            <?php if (!empty($politica['tipo_empleado_desc'])): ?>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($politica['tipo_empleado_desc']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Días por período</span>
                            <span class="text-lg font-bold text-green-600"><?= $politica['dias_por_periodo'] ?> días</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Período</span>
                            <span class="text-sm font-semibold text-gray-900"><?= $politica['periodo_dias'] ?> días</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Acumulable</span>
                            <span class="text-sm font-semibold <?= $politica['es_acumulable'] ? 'text-green-600' : 'text-gray-400' ?>">
                                <?= $politica['es_acumulable'] ? 'Sí (hasta ' . $politica['max_periodos_acumulables'] . ' períodos)' : 'No' ?>
                            </span>
                        </div>
                        <?php if (!empty($politica['observaciones'])): ?>
                        <div class="pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                <?= htmlspecialchars($politica['observaciones']) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($politicas_vacaciones)): ?>
                <div class="col-span-3 bg-gray-50 rounded-2xl p-8 text-center">
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">No hay políticas de vacaciones configuradas</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reglas de Aprobación -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Reglas de Aprobación</h2>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Tipo de Permiso</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Duración</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Aprobador</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Documento</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Anticipación</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reglas_aprobacion as $regla): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900"><?= ucfirst(str_replace('_', ' ', $regla['tipo_permiso'])) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600">
                                        <?php
                                        if ($regla['dias_minimos'] == 0 && $regla['dias_maximos'] === null) {
                                            echo 'Cualquier duración';
                                        } elseif ($regla['dias_maximos'] === null) {
                                            echo $regla['dias_minimos'] . '+ días';
                                        } else {
                                            echo $regla['dias_minimos'] . '-' . $regla['dias_maximos'] . ' días';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $regla['aprobador_rol'] === 'rrhh' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= $regla['aprobador_rol'] === 'rrhh' ? 'RRHH' : 'Jefe Inmediato' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($regla['requiere_documento']): ?>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                        <?= ucfirst(str_replace('_', ' ', $regla['tipo_documento'] ?? 'Documento')) ?>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-sm text-gray-400">No requiere</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600">
                                        <?= $regla['dias_anticipacion_minima'] > 0 ? $regla['dias_anticipacion_minima'] . ' días' : '-' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($reglas_aprobacion)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                    <p>No hay reglas de aprobación configuradas</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
