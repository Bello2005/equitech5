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

$page_title = 'Calendario';

// Cargar eventos desde la base de datos
$eventos_calendario = [];
$proximos_eventos = [];

try {
    $conn = getConnection();

    // Cargar todos los eventos para el calendario
    $sql = "SELECT
        e.id,
        e.titulo,
        e.descripcion,
        e.fecha_inicio,
        e.fecha_fin,
        e.tipo,
        e.color,
        COALESCE(u.nombre, 'Sistema') AS usuario_nombre
    FROM eventos e
    LEFT JOIN usuarios u ON u.id = e.usuario_id
    ORDER BY e.fecha_inicio ASC";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $eventos_calendario[] = [
                'id' => (int)$row['id'],
                'titulo' => $row['titulo'],
                'descripcion' => $row['descripcion'] ?? '',
                'fecha' => $row['fecha_inicio'],
                'fecha_inicio' => $row['fecha_inicio'],
                'fecha_fin' => $row['fecha_fin'],
                'tipo' => $row['tipo'],
                'color' => $row['color'] ?? '#0B8A3A',
                'usuario' => $row['usuario_nombre']
            ];
        }
    }

    // Cargar próximos eventos (futuros o de hoy)
    $sql_proximos = "SELECT
        e.id,
        e.titulo,
        e.fecha_inicio,
        e.tipo,
        COALESCE(u.nombre, 'Sistema') AS usuario_nombre
    FROM eventos e
    LEFT JOIN usuarios u ON u.id = e.usuario_id
    WHERE e.fecha_inicio >= CURDATE()
    ORDER BY e.fecha_inicio ASC
    LIMIT 10";

    $result_proximos = $conn->query($sql_proximos);
    if ($result_proximos && $result_proximos->num_rows > 0) {
        while ($row = $result_proximos->fetch_assoc()) {
            $proximos_eventos[] = [
                'titulo' => $row['titulo'],
                'fecha' => $row['fecha_inicio'],
                'tipo' => $row['tipo'],
                'usuario' => $row['usuario_nombre']
            ];
        }
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Error cargando eventos: " . $e->getMessage());
    // Usar datos mock como fallback (ya cargados)
}

// Si no hay eventos de la BD, usar los del mock_data
if (empty($proximos_eventos) && !empty($eventos_calendario)) {
    $proximos_eventos = array_slice($eventos_calendario, 0, 10);
}

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
                        <?php if (empty($proximos_eventos)): ?>
                            <p class="text-sm text-gray-500 text-center py-4">No hay eventos próximos</p>
                        <?php else: ?>
                            <?php foreach (array_slice($proximos_eventos, 0, 5) as $evento): ?>
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 rounded-full bg-primary mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($evento['titulo']) ?></p>
                                    <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($evento['fecha'])) ?></p>
                                    <?php if (!empty($evento['usuario'])): ?>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($evento['usuario']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
// Eventos cargados desde la base de datos
window.calendarEvents = [
    <?php if (!empty($eventos_calendario)): ?>
        <?php foreach ($eventos_calendario as $index => $evento): ?>
        {
            id: <?= $evento['id'] ?>,
            title: '<?= addslashes($evento['titulo']) ?>',
            start: '<?= $evento['fecha_inicio'] ?>',
            end: '<?= $evento['fecha_fin'] ?>',
            color: '<?= $evento['color'] ?>',
            extendedProps: {
                tipo: '<?= addslashes($evento['tipo']) ?>',
                descripcion: '<?= addslashes($evento['descripcion']) ?>',
                usuario: '<?= addslashes($evento['usuario']) ?>'
            }
        }<?= $index < count($eventos_calendario) - 1 ? ',' : '' ?>
        <?php endforeach; ?>
    <?php endif; ?>
];

console.log('Eventos cargados:', window.calendarEvents.length);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
