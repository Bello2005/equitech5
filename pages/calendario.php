<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/mock_data.php';

requireLogin();
$usuario = getCurrentUser();

if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80';
}

$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

$page_title = 'Calendario';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Calendario de Ausencias</h1>
            <p class="text-gray-600">Visualiza y gestiona todas las ausencias, vacaciones y eventos del equipo.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- Calendar -->
            <div class="xl:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">Enero 2024</h3>
                        <button class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Nuevo Evento
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="calendar"></div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Próximos Eventos -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Próximos Eventos</h3>
                    <div class="space-y-4">
                        <?php foreach (array_slice($eventos_calendario, 0, 5) as $evento): ?>
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 rounded-full bg-primary mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?= $evento['titulo'] ?></p>
                                <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($evento['fecha'])) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Leyenda -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tipos de Eventos</h3>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded bg-green-500"></div>
                            <span class="text-sm text-gray-700">Vacaciones</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded bg-yellow-500"></div>
                            <span class="text-sm text-gray-700">Permisos</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded bg-blue-500"></div>
                            <span class="text-sm text-gray-700">Reuniones</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded bg-purple-500"></div>
                            <span class="text-sm text-gray-700">Teletrabajo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
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
