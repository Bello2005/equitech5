<?php
ob_start(); // Capturar cualquier salida
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/mock_data.php';

// Requerir autenticación
requireLogin();

// Obtener datos del usuario actual
$usuario = getCurrentUser();

// Redirigir empleados a su dashboard específico
if ($usuario['rol'] === 'empleado') {
    header('Location: empleado_dashboard.php');
    exit;
}

// Si no hay avatar, usar uno por defecto
if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80';
}

// Obtener iniciales del nombre
$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

// Políticas corporativas (texto formal para el panel derecho)
// Políticas corporativas (texto formal para el panel derecho)
$politicas = [
    ['nombre' => 'Permiso de Vacaciones', 'categoria' => 'vacaciones', 'descripcion' => 'Permiso de vacaciones: 15 días hábiles por periodo de un año.'],
    ['nombre' => 'Permiso de Maternidad/Paternidad', 'categoria' => 'permisos', 'descripcion' => 'Permiso maternidad o paternidad: mujeres 4 meses, hombres 15 días.'],
    ['nombre' => 'Permisos Médicos', 'categoria' => 'permisos', 'descripcion' => 'Permisos médicos (deben estar acompañados de las órdenes médicas, todo anexo que tenga que ver con la enfermedad).'],
    ['nombre' => 'Permiso por Servicio como Jurado', 'categoria' => 'permisos', 'descripcion' => 'Permiso por ser miembro o jurado.'],
];

$page_title = 'Executive Dashboard';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

// Intentar cargar datos reales desde la base de datos (solicitudes recientes, eventos y actividad).
try {
    require_once __DIR__ . '/../config/database.php';
    $conn = getConnection();

    // Solicitudes recientes (mostrar 5)
    $sql = "SELECT s.id, COALESCE(u.nombre, CONCAT('Usuario ', s.usuario_id)) AS empleado, s.tipo, s.fecha_inicio AS fecha, s.dias, s.estado, s.prioridad
            FROM solicitudes s
            LEFT JOIN usuarios u ON u.id = s.usuario_id
            ORDER BY s.fecha_creacion DESC LIMIT 5";
    $res = $conn->query($sql);
    if ($res) {
        $solicitudes_db = [];
        while ($r = $res->fetch_assoc()) {
            $solicitudes_db[] = [
                'id' => (int)$r['id'],
                'empleado' => $r['empleado'],
                'avatar' => strtoupper(substr((string)$r['empleado'],0,2)),
                'tipo' => $r['tipo'] ?? '',
                'fecha' => $r['fecha'] ?? null,
                'dias' => $r['dias'] ?? 1,
                'estado' => $r['estado'] ?? 'pendiente',
                'prioridad' => $r['prioridad'] ?? 'media'
            ];
        }
        if (!empty($solicitudes_db)) $solicitudes = $solicitudes_db;
    }

    // Eventos calendario (últimos 20)
    $sql = "SELECT titulo, fecha_inicio AS fecha, tipo FROM eventos ORDER BY fecha_creacion DESC LIMIT 20";
    $res = $conn->query($sql);
    if ($res) {
        $eventos_db = [];
        while ($r = $res->fetch_assoc()) {
            $eventos_db[] = ['titulo' => $r['titulo'], 'fecha' => $r['fecha'], 'tipo' => $r['tipo']];
        }
        if (!empty($eventos_db)) $eventos_calendario = $eventos_db;
    }

    // Actividad reciente
    $sql = "SELECT a.descripcion AS accion, COALESCE(u.nombre, 'Sistema') AS usuario, a.fecha_creacion AS tiempo, a.tipo FROM actividad a LEFT JOIN usuarios u ON u.id = a.usuario_id ORDER BY a.fecha_creacion DESC LIMIT 10";
    $res = $conn->query($sql);
    if ($res) {
        $actividad_db = [];
        while ($r = $res->fetch_assoc()) {
            // Normalizar tipo para icon mapping
            $actividad_db[] = ['usuario' => $r['usuario'], 'accion' => $r['accion'], 'tiempo' => $r['tiempo'], 'tipo' => $r['tipo'] ?? 'solicitud'];
        }
        if (!empty($actividad_db)) $actividad_reciente = $actividad_db;
    }

    $conn->close();
} catch (Exception $e) {
    // Mantener datos mock si falla la consulta
}
?>

            <!-- Enhanced Page content -->
            <main class="flex-1 overflow-y-auto focus:outline-none py-8">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <!-- Hero section with welcome -->
                    <div class="mb-8 animate-fade-in">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                                    <?= htmlspecialchars($usuario['nombre']) ?>
                                </h1>
                                <p class="text-lg text-gray-600 max-w-2xl">
                                    <span class="font-medium text-gray-800"><?= htmlspecialchars($usuario['rol'] ?: 'Colaborador') ?> &middot; <?= htmlspecialchars($usuario['empresa']) ?></span>
                                    <?php if (!empty($usuario['email'])): ?>
                                    <br>
                                    <span class="text-sm text-gray-600">Correo: <?= htmlspecialchars($usuario['email']) ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="mt-4 lg:mt-0 flex items-center space-x-4">
                                <div class="flex items-center bg-white rounded-2xl px-4 py-2 shadow-sm border border-gray-100">
                                    <img src="<?= htmlspecialchars($usuario['avatar']) ?>" alt="Avatar" class="w-10 h-10 rounded-md object-cover mr-3">
                                    <div class="text-left">
                                        <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($usuario['nombre']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($usuario['rol'] ?: 'Colaborador') ?></div>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <button id="btn-dashboard-export" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-2xl hover:bg-gray-50 transition-all duration-200 shadow-sm">
                                        <i class="fas fa-download mr-2"></i>
                                        Exportar
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Enhanced KPI Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                        <?php foreach ($kpis as $key => $kpi):
                            $colors = [
                                'green' => ['bg' => 'from-emerald-50 to-green-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
                                'yellow' => ['bg' => 'from-amber-50 to-yellow-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
                                'blue' => ['bg' => 'from-blue-50 to-cyan-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200'],
                                'purple' => ['bg' => 'from-purple-50 to-violet-50', 'text' => 'text-purple-600', 'border' => 'border-purple-200'],
                                'indigo' => ['bg' => 'from-indigo-50 to-blue-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-200'],
                                'emerald' => ['bg' => 'from-emerald-50 to-teal-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200']
                            ];
                            $color = $colors[$kpi['color']];
                        ?>
                        <div class="bg-gradient-to-br <?= $color['bg'] ?> border <?= $color['border'] ?> rounded-2xl p-6 card-hover shadow-sm hover:shadow-elegant transition-all duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-<?= $kpi['icono'] ?> <?= $color['text'] ?> text-lg"></i>
                                </div>
                                <div class="text-right">
                                    <?php if (!empty($kpi['tendencia'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white text-green-600 shadow-sm">
                                        <i class="fas fa-arrow-up mr-1 text-xs"></i>
                                        <?= $kpi['tendencia'] ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-1"><?= $kpi['valor'] ?></h3>
                            <p class="text-sm text-gray-600 mb-4"><?= ucfirst(str_replace('_', ' ', $key)) ?></p>
                            <?php if (!empty($kpi['meta'])): ?>
                            <div class="w-full bg-white/50 rounded-full h-2">
                                <div class="bg-gradient-to-r <?= $color['text'] ?> h-2 rounded-full"
                                     style="width: <?= min(100, ($kpi['valor'] / $kpi['meta']) * 100) ?>%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Progreso</span>
                                <span>Meta: <?= $kpi['meta'] ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <!-- Main content - Enhanced Table -->
                        <div class="xl:col-span-2 space-y-8">
                            <!-- Políticas y Tipos de Permisos -->
                            <div class="grid grid-cols-1 gap-6 mb-8">
                                <!-- Tipos de Permisos -->
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                                    <div class="px-6 py-5 border-b border-gray-100">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-xl font-semibold text-gray-900">
                                                Tipos de Permisos
                                            </h3>
                                            <button class="px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
                                                <i class="fas fa-plus mr-2"></i>
                                                Nuevo Tipo
                                            </button>
                                        </div>
                                    </div>
                                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto scrollbar-thin">
                                        <?php
                                        // Tipos de permiso actualizados según políticas corporativas
                                        $tipos_permisos = [
                                            ['nombre' => 'Permiso de Vacaciones', 'dias' => 15, 'requiere_doc' => false],
                                            ['nombre' => 'Permiso de Maternidad/Paternidad', 'dias' => null, 'requiere_doc' => true],
                                            ['nombre' => 'Permiso Médico', 'dias' => null, 'requiere_doc' => true],
                                            ['nombre' => 'Permiso por Servicio como Jurado', 'dias' => null, 'requiere_doc' => true],
                                            ['nombre' => 'Teletrabajo', 'dias' => null, 'requiere_doc' => false],
                                        ];
                                        foreach ($tipos_permisos as $tipo):
                                        ?>
                                        <div class="p-4 hover:bg-gray-50/50 transition-colors duration-150">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900"><?= $tipo['nombre'] ?></h4>
                                                    <div class="flex items-center mt-1 space-x-2">
                                                        <?php if ($tipo['dias']): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <?= $tipo['dias'] ?> días máx.
                                                        </span>
                                                        <?php endif; ?>
                                                        <?php if ($tipo['requiere_doc']): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                            <i class="fas fa-file-medical mr-1"></i>
                                                            Requiere documentación
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <button class="w-8 h-8 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center hover:bg-gray-200 transition-colors">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </button>
                                                    <button class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Requests Table -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                                <div class="px-6 py-5 border-b border-gray-100">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2 lg:mb-0">
                                            Solicitudes Recientes
                                        </h3>
                                        <div class="flex flex-wrap gap-2">
                                            <select class="block w-full lg:w-48 px-4 py-2 text-sm border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50">
                                                <option>Todos los estados</option>
                                                <option>Pendiente</option>
                                                <option>Aprobado</option>
                                                <option>Rechazado</option>
                                            </select>
                                            <button class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all duration-200 shadow-sm">
                                                <i class="fas fa-filter mr-2"></i>
                                                Filtrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto scrollbar-thin">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Empleado</th>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Días</th>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($solicitudes as $solicitud):
                                                $badge_class = "badge-{$solicitud['estado']}";
                                                $priority_class = "priority-{$solicitud['prioridad']}";
                                            ?>
                                            <tr class="hover:bg-gray-50/50 transition-colors duration-150 <?= $priority_class ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 bg-gradient-to-br from-primary to-primary-dark rounded-lg flex items-center justify-center text-white text-sm font-semibold mr-3 shadow-sm">
                                                            <?= $solicitud['avatar'] ?>
                                                        </div>
                                                        <div class="text-sm font-medium text-gray-900"><?= $solicitud['empleado'] ?></div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= $solicitud['tipo'] ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= date('d M Y', strtotime($solicitud['fecha'])) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <?= $solicitud['dias'] ?> días
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $badge_class ?>">
                                                        <?= ucfirst($solicitud['estado']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex items-center space-x-2">
                                                        <?php if ($solicitud['estado'] == 'pendiente'): ?>
                                                        <button data-id="<?= $solicitud['id'] ?>" class="btn-approve w-8 h-8 bg-green-100 text-green-600 rounded-lg flex items-center justify-center hover:bg-green-200 transition-colors">
                                                            <i class="fas fa-check text-xs"></i>
                                                        </button>
                                                        <button data-id="<?= $solicitud['id'] ?>" class="btn-reject w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors">
                                                            <i class="fas fa-times text-xs"></i>
                                                        </button>
                                                        <?php else: ?>
                                                        <span class="text-gray-400 text-xs">Completado</span>
                                                        <?php endif; ?>
                                                        <button class="w-8 h-8 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center hover:bg-gray-200 transition-colors">
                                                            <i class="fas fa-eye text-xs"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-gray-600">
                                            Mostrando <span class="font-semibold">5</span> de <span class="font-semibold">24</span> solicitudes
                                        </p>
                                        <div class="flex space-x-2">
                                            <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                Anterior
                                            </button>
                                            <button class="px-3 py-1 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                                                Siguiente
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Calendar -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                                <div class="px-6 py-5 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xl font-semibold text-gray-900">
                                            Calendario de Ausencias
                                        </h3>
                                        <button id="btn-new-event" class="px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Nuevo Evento
                                        </button>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div id="calendar" class="fc fc-media-screen fc-direction-ltr fc-theme-standard"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Sidebar content -->
                        <div class="space-y-8">
                            <!-- (Panel de políticas eliminado; ahora está disponible en la pestaña Políticas) -->
                            <!-- Enhanced Activity Feed -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                                <div class="px-6 py-5 border-b border-gray-100">
                                    <h3 class="text-xl font-semibold text-gray-900">
                                        Actividad Reciente
                                    </h3>
                                </div>
                                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto scrollbar-thin">
                                    <?php foreach ($actividad_reciente as $index => $actividad):
                                        $icon_colors = [
                                            'aprobacion' => ['bg' => 'bg-green-100', 'icon' => 'fa-check-circle', 'color' => 'text-green-600'],
                                            'solicitud' => ['bg' => 'bg-blue-100', 'icon' => 'fa-file-alt', 'color' => 'text-blue-600'],
                                            'recordatorio' => ['bg' => 'bg-amber-100', 'icon' => 'fa-bell', 'color' => 'text-amber-600'],
                                            'completado' => ['bg' => 'bg-purple-100', 'icon' => 'fa-flag-checkered', 'color' => 'text-purple-600']
                                        ];
                                        $icon = $icon_colors[$actividad['tipo']];
                                    ?>
                                    <div class="p-4 hover:bg-gray-50/50 transition-colors duration-150">
                                        <div class="flex space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 <?= $icon['bg'] ?> rounded-xl flex items-center justify-center shadow-sm">
                                                    <i class="fas <?= $icon['icon'] ?> <?= $icon['color'] ?>"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900"><?= $actividad['usuario'] ?></p>
                                                <p class="text-sm text-gray-600 mt-1"><?= $actividad['accion'] ?></p>
                                                <p class="text-xs text-gray-400 mt-2"><?= $actividad['tiempo'] ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                                    <a href="#" class="block text-center text-sm text-primary font-semibold py-2 hover:text-primary-dark transition-colors">
                                        Ver todo el historial
                                    </a>
                                </div>
                            </div>

                            <!-- Enhanced Quick Stats -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gradient-to-br from-primary to-primary-dark rounded-2xl p-6 text-white shadow-elegant">
                                    <div class="text-center">
                                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3 backdrop-blur-sm">
                                            <i class="fas fa-clock text-xl"></i>
                                        </div>
                                        <p class="text-2xl font-bold">12</p>
                                        <p class="text-sm opacity-90 mt-1">Pendientes</p>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-accent to-accent-dark rounded-2xl p-6 text-gray-900 shadow-elegant">
                                    <div class="text-center">
                                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3 backdrop-blur-sm">
                                            <i class="fas fa-check-circle text-xl"></i>
                                        </div>
                                        <p class="text-2xl font-bold">24</p>
                                        <p class="text-sm opacity-90 mt-1">Aprobados</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Export Panel -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                                <div class="px-6 py-5 border-b border-gray-100">
                                    <h3 class="text-xl font-semibold text-gray-900">
                                        Exportar Datos
                                    </h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <button id="btn-dashboard-export-csv" class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary hover:bg-primary/5 transition-all duration-200 group">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-green-200 transition-colors">
                                                <i class="fas fa-file-csv text-green-600"></i>
                                            </div>
                                            <div class="text-left">
                                                <p class="font-semibold text-gray-900">Exportar CSV</p>
                                                <p class="text-sm text-gray-500">Datos en formato Excel</p>
                                            </div>
                                        </div>
                                        <i class="fas fa-download text-gray-400 group-hover:text-primary transition-colors"></i>
                                    </button>

                                    <button id="btn-dashboard-export-pdf" class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary hover:bg-primary/5 transition-all duration-200 group">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-blue-200 transition-colors">
                                                <i class="fas fa-file-pdf text-blue-600"></i>
                                            </div>
                                            <div class="text-left">
                                                <p class="font-semibold text-gray-900">Generar PDF</p>
                                                <p class="text-sm text-gray-500">Reporte ejecutivo</p>
                                            </div>
                                        </div>
                                        <i class="fas fa-print text-gray-400 group-hover:text-primary transition-colors"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

<script>
// Preparar datos del calendario para JavaScript
window.calendarEvents = [
    <?php foreach ($eventos_calendario as $evento): ?>
    {
        title: '<?= addslashes($evento['titulo']) ?>',
        start: '<?= $evento['fecha'] ?>',
        color: '<?= $evento['tipo'] == 'vacaciones' ? '#0B8A3A' : ($evento['tipo'] == 'permiso' ? '#FFD400' : ($evento['tipo'] == 'reunion' ? '#3B82F6' : '#8B5CF6')) ?>',
        textColor: '<?= $evento['tipo'] == 'vacaciones' ? 'white' : 'black' ?>'
    },
    <?php endforeach; ?>
];
</script>

<script>
// Wire up dashboard export buttons to download CSV reports
const exportEndpoint = 'api/export_report.php';

function downloadReport(report, format = 'csv') {
    const url = `${exportEndpoint}?report=${encodeURIComponent(report)}&format=${encodeURIComponent(format)}`;
    // Open in new tab to trigger download without navigating away
    window.open(url, '_blank');
}

document.getElementById('btn-dashboard-export')?.addEventListener('click', function() {
    // Default: download monthly CSV
    downloadReport('monthly', 'csv');
});

document.getElementById('btn-dashboard-export-csv')?.addEventListener('click', function() {
    downloadReport('monthly', 'csv');
});

document.getElementById('btn-dashboard-export-pdf')?.addEventListener('click', function() {
    // Generate PDF report (monthly)
    downloadReport('monthly', 'pdf');
});

// Approve / Reject handlers for solicitudes
document.querySelectorAll('.btn-approve').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        if (!confirm('¿Aprobar solicitud #' + id + '?')) return;
        try {
            const res = await fetch('api/solicitud_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id, action: 'approve'})
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Error');
            }
        } catch (err) { console.error(err); alert('Error de red'); }
    });
});

document.querySelectorAll('.btn-reject').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        if (!confirm('¿Rechazar solicitud #' + id + '?')) return;
        try {
            const res = await fetch('api/solicitud_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id, action: 'reject'})
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Error');
            }
        } catch (err) { console.error(err); alert('Error de red'); }
    });
});

// New solicitud (simple prompts)
document.getElementById('btn-new-solicitud')?.addEventListener('click', async function() {
    const tipo = prompt('Tipo de permiso (ej. Vacaciones)');
    if (!tipo) return;
    const fecha_inicio = prompt('Fecha inicio (YYYY-MM-DD)');
    if (!fecha_inicio) return;
    const fecha_fin = prompt('Fecha fin (YYYY-MM-DD)', fecha_inicio);
    const dias = prompt('Días', '1');
    const motivo = prompt('Motivo');

    const form = new FormData();
    form.append('tipo', tipo);
    form.append('fecha_inicio', fecha_inicio);
    form.append('fecha_fin', fecha_fin);
    form.append('dias', dias);
    form.append('motivo', motivo);

    try {
        const res = await fetch('api/solicitud_create.php', { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) {
            alert('Solicitud creada (ID ' + data.id + ')');
            location.reload();
        } else {
            alert(data.error || 'Error al crear solicitud');
        }
    } catch (err) { console.error(err); alert('Error de red'); }
});

// New event
document.getElementById('btn-new-event')?.addEventListener('click', async function() {
    const title = prompt('Título del evento');
    if (!title) return;
    const fecha_inicio = prompt('Fecha inicio (YYYY-MM-DD)');
    if (!fecha_inicio) return;
    const fecha_fin = prompt('Fecha fin (YYYY-MM-DD)', fecha_inicio);
    const tipo = prompt('Tipo (vacaciones, permiso, reunion, teletrabajo, otro)', 'otro');

    const form = new FormData();
    form.append('title', title);
    form.append('fecha_inicio', fecha_inicio);
    form.append('fecha_fin', fecha_fin);
    form.append('tipo', tipo);

    try {
        const res = await fetch('api/event_create.php', { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) {
            alert('Evento creado (ID ' + data.id + ')');
            location.reload();
        } else {
            alert(data.error || 'Error al crear evento');
        }
    } catch (err) { console.error(err); alert('Error de red'); }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
