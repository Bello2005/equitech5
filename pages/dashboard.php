<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/mock_data.php';

// Requerir autenticación
requireLogin();

// Obtener datos del usuario actual
$usuario = getCurrentUser();

// Si no hay avatar, usar uno por defecto
if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80';
}

// Obtener iniciales del nombre
$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

$page_title = 'Executive Dashboard';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

            <!-- Enhanced Page content -->
            <main class="flex-1 overflow-y-auto focus:outline-none py-8">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <!-- Hero section with welcome -->
                    <div class="mb-8 animate-fade-in">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                                    Bienvenido, <?= explode(' ', $usuario['nombre'])[0] ?> 👋
                                </h1>
                                <p class="text-lg text-gray-600 max-w-2xl">
                                    Aquí tienes un resumen completo de la actividad de tu equipo y métricas clave para hoy.
                                </p>
                            </div>
                            <div class="mt-4 lg:mt-0 flex space-x-3">
                                <button class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-2xl hover:bg-primary-dark transition-all duration-200 shadow-md hover:shadow-lg">
                                    <i class="fas fa-plus mr-2"></i>
                                    Nueva Solicitud
                                </button>
                                <button class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-2xl hover:bg-gray-50 transition-all duration-200 shadow-sm">
                                    <i class="fas fa-download mr-2"></i>
                                    Exportar
                                </button>
                            </div>
                        </div>
                        <div class="mt-6 bg-gradient-to-r from-primary to-primary-dark rounded-2xl p-8 text-white shadow-elegant">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold mb-2">¡Excelente trabajo esta semana!</h2>
                                    <p class="text-primary-100 opacity-90 max-w-2xl">
                                        Has aprobado 12 solicitudes y el índice de satisfacción del equipo ha aumentado un 5%.
                                        Sigue así para mantener un ambiente laboral saludable.
                                    </p>
                                </div>
                                <div class="mt-4 lg:mt-0">
                                    <div class="flex items-center space-x-2 bg-white/20 rounded-2xl p-4 backdrop-blur-sm">
                                        <i class="fas fa-trophy text-2xl text-accent"></i>
                                        <div>
                                            <p class="text-sm opacity-90">Ranking del mes</p>
                                            <p class="text-xl font-bold">#1 en eficiencia</p>
                                        </div>
                                    </div>
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
                                                        <button class="w-8 h-8 bg-green-100 text-green-600 rounded-lg flex items-center justify-center hover:bg-green-200 transition-colors">
                                                            <i class="fas fa-check text-xs"></i>
                                                        </button>
                                                        <button class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors">
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
                                        <button class="px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
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
                                    <button class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary hover:bg-primary/5 transition-all duration-200 group">
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

                                    <button class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary hover:bg-primary/5 transition-all duration-200 group">
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
