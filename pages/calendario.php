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

// Cargar eventos y vacaciones desde la base de datos
$eventos_calendario = [];
$proximos_eventos = [];
$stats = ['total_vacaciones' => 0, 'activas_hoy' => 0, 'proximas' => 0, 'departamentos' => []];

try {
    $conn = getConnection();

    // 1. Cargar eventos regulares
    $sql_eventos = "SELECT
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

    $result = $conn->query($sql_eventos);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $eventos_calendario[] = [
                'id' => 'evento_' . $row['id'],
                'titulo' => $row['titulo'],
                'descripcion' => $row['descripcion'] ?? '',
                'fecha' => $row['fecha_inicio'],
                'fecha_inicio' => $row['fecha_inicio'],
                'fecha_fin' => $row['fecha_fin'],
                'tipo' => $row['tipo'],
                'color' => $row['color'] ?? '#0B8A3A',
                'usuario' => $row['usuario_nombre'],
                'es_vacacion' => false
            ];
        }
    }

    // 2. Cargar vacaciones aprobadas
    $sql_vacaciones = "SELECT
        s.id,
        s.fecha_inicio,
        s.fecha_fin,
        s.tipo,
        s.motivo,
        s.dias_habiles,
        u.nombre AS empleado_nombre,
        u.departamento,
        u.email
    FROM solicitudes s
    JOIN usuarios u ON u.id = s.usuario_id
    WHERE s.estado = 'aprobado'
    AND s.tipo = 'vacaciones'
    ORDER BY s.fecha_inicio ASC";

    $result_vac = $conn->query($sql_vacaciones);
    if ($result_vac && $result_vac->num_rows > 0) {
        while ($row = $result_vac->fetch_assoc()) {
            $hoy = date('Y-m-d');
            $es_activa = ($row['fecha_inicio'] <= $hoy && $row['fecha_fin'] >= $hoy);
            $es_proxima = ($row['fecha_inicio'] > $hoy && $row['fecha_inicio'] <= date('Y-m-d', strtotime('+7 days')));

            // Colorear por departamento
            $colores_dept = [
                'Recursos Humanos' => '#8B5CF6',
                'TI' => '#3B82F6',
                'Ventas' => '#10B981',
                'Finanzas' => '#F59E0B',
                'General' => '#6B7280'
            ];
            $color = $colores_dept[$row['departamento'] ?? 'General'] ?? '#6366F1';

            $eventos_calendario[] = [
                'id' => 'vacacion_' . $row['id'],
                'titulo' => '🏖️ ' . $row['empleado_nombre'],
                'descripcion' => $row['motivo'] ?? 'Vacaciones',
                'fecha' => $row['fecha_inicio'],
                'fecha_inicio' => $row['fecha_inicio'],
                'fecha_fin' => $row['fecha_fin'],
                'tipo' => 'vacaciones',
                'color' => $color,
                'usuario' => $row['empleado_nombre'],
                'departamento' => $row['departamento'] ?? 'General',
                'email' => $row['email'],
                'dias_habiles' => $row['dias_habiles'],
                'es_vacacion' => true
            ];

            // Estadísticas
            $stats['total_vacaciones']++;
            if ($es_activa) $stats['activas_hoy']++;
            if ($es_proxima) $stats['proximas']++;

            $dept = $row['departamento'] ?? 'General';
            if (!isset($stats['departamentos'][$dept])) {
                $stats['departamentos'][$dept] = 0;
            }
            $stats['departamentos'][$dept]++;
        }
    }

    // 3. Cargar próximos eventos (eventos + vacaciones futuras)
    $sql_proximos = "
        SELECT 'evento' as source, e.titulo, e.fecha_inicio, e.tipo, COALESCE(u.nombre, 'Sistema') AS usuario_nombre
        FROM eventos e
        LEFT JOIN usuarios u ON u.id = e.usuario_id
        WHERE e.fecha_inicio >= CURDATE()
        UNION ALL
        SELECT 'vacacion' as source,
               CONCAT('🏖️ ', u.nombre) as titulo,
               s.fecha_inicio,
               'vacaciones' as tipo,
               u.nombre AS usuario_nombre
        FROM solicitudes s
        JOIN usuarios u ON u.id = s.usuario_id
        WHERE s.estado = 'aprobado'
        AND s.tipo = 'vacaciones'
        AND s.fecha_inicio >= CURDATE()
        ORDER BY fecha_inicio ASC
        LIMIT 10";

    $result_proximos = $conn->query($sql_proximos);
    if ($result_proximos && $result_proximos->num_rows > 0) {
        while ($row = $result_proximos->fetch_assoc()) {
            $proximos_eventos[] = [
                'titulo' => $row['titulo'],
                'fecha' => $row['fecha_inicio'],
                'tipo' => $row['tipo'],
                'usuario' => $row['usuario_nombre'],
                'es_vacacion' => $row['source'] === 'vacacion'
            ];
        }
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Error cargando eventos: " . $e->getMessage());
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Calendario de Vacaciones</h1>
            <p class="text-gray-600">Visualiza las vacaciones y ausencias del equipo en tiempo real.</p>
        </div>

        <!-- Stats de Vacaciones -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Vacaciones</p>
                        <p class="text-3xl font-bold text-blue-600"><?= $stats['total_vacaciones'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-umbrella-beach text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Activas Hoy</p>
                        <p class="text-3xl font-bold text-green-600"><?= $stats['activas_hoy'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-plane-departure text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Próximas (7 días)</p>
                        <p class="text-3xl font-bold text-orange-600"><?= $stats['proximas'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
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

                <!-- Leyenda de Colores por Departamento -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Colores por Departamento</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 rounded" style="background-color: #8B5CF6;"></div>
                                <span class="text-sm text-gray-700">Recursos Humanos</span>
                            </div>
                            <span class="text-xs text-gray-500"><?= $stats['departamentos']['Recursos Humanos'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 rounded" style="background-color: #3B82F6;"></div>
                                <span class="text-sm text-gray-700">TI</span>
                            </div>
                            <span class="text-xs text-gray-500"><?= $stats['departamentos']['TI'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 rounded" style="background-color: #10B981;"></div>
                                <span class="text-sm text-gray-700">Ventas</span>
                            </div>
                            <span class="text-xs text-gray-500"><?= $stats['departamentos']['Ventas'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 rounded" style="background-color: #F59E0B;"></div>
                                <span class="text-sm text-gray-700">Finanzas</span>
                            </div>
                            <span class="text-xs text-gray-500"><?= $stats['departamentos']['Finanzas'] ?? 0 ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 rounded" style="background-color: #6B7280;"></div>
                                <span class="text-sm text-gray-700">General</span>
                            </div>
                            <span class="text-xs text-gray-500"><?= $stats['departamentos']['General'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Eventos y vacaciones cargados desde la base de datos
window.calendarEvents = [
    <?php if (!empty($eventos_calendario)): ?>
        <?php foreach ($eventos_calendario as $index => $evento): ?>
        {
            id: '<?= addslashes($evento['id']) ?>',
            title: '<?= addslashes($evento['titulo']) ?>',
            start: '<?= $evento['fecha_inicio'] ?>',
            end: '<?= $evento['fecha_fin'] ?>',
            backgroundColor: '<?= $evento['color'] ?>',
            borderColor: '<?= $evento['color'] ?>',
            extendedProps: {
                tipo: '<?= addslashes($evento['tipo']) ?>',
                descripcion: '<?= addslashes($evento['descripcion']) ?>',
                usuario: '<?= addslashes($evento['usuario']) ?>',
                esVacacion: <?= $evento['es_vacacion'] ? 'true' : 'false' ?>,
                <?php if (!empty($evento['departamento'])): ?>
                departamento: '<?= addslashes($evento['departamento']) ?>',
                <?php endif; ?>
                <?php if (!empty($evento['email'])): ?>
                email: '<?= addslashes($evento['email']) ?>',
                <?php endif; ?>
                <?php if (isset($evento['dias_habiles'])): ?>
                diasHabiles: <?= $evento['dias_habiles'] ?>
                <?php endif; ?>
            }
        }<?= $index < count($eventos_calendario) - 1 ? ',' : '' ?>
        <?php endforeach; ?>
    <?php endif; ?>
];

console.log('Eventos cargados:', window.calendarEvents.length);
console.log('Vacaciones:', window.calendarEvents.filter(e => e.extendedProps.esVacacion).length);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
