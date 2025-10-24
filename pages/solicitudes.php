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

$page_title = 'Solicitudes';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<?php
// Intentar cargar solicitudes reales desde la base de datos. Si falla, usar el mock definido en mock_data.php
try {
    require_once __DIR__ . '/../config/database.php';
    $conn = getConnection();

    // Verificar si la tabla 'solicitudes' existe
    $dbName = DB_NAME;
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $conn->real_escape_string($dbName) . "' AND TABLE_NAME = 'solicitudes'");
    $row = $res->fetch_assoc();
    if ($row && $row['cnt'] > 0) {
        // Intentar obtener nombre de usuario si existe la tabla 'usuarios' o 'usuarios' equivalente
        $usersTable = null;
        $res2 = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $conn->real_escape_string($dbName) . "' AND TABLE_NAME IN ('usuarios','users','empleados')");
        $r2 = $res2->fetch_assoc();
        if ($r2 && $r2['cnt'] > 0) {
            // Preferir 'usuarios', luego 'users', luego 'empleados'
            if ($conn->query("SELECT 1 FROM usuarios LIMIT 1") !== false) $usersTable = 'usuarios';
            elseif ($conn->query("SELECT 1 FROM users LIMIT 1") !== false) $usersTable = 'users';
            elseif ($conn->query("SELECT 1 FROM empleados LIMIT 1") !== false) $usersTable = 'empleados';
        }

        if ($usersTable) {
            $sql = "SELECT s.id, s.usuario_id, s.tipo, s.fecha_inicio AS fecha, s.dias, s.estado, s.prioridad, COALESCE(u.nombre, CONCAT('Usuario ', s.usuario_id)) AS empleado
                    FROM solicitudes s
                    LEFT JOIN " . $usersTable . " u ON u.id = s.usuario_id
                    ORDER BY s.fecha_creacion DESC LIMIT 200";
        } else {
            $sql = "SELECT id, usuario_id, tipo, fecha_inicio AS fecha, dias, estado, prioridad, usuario_id AS empleado FROM solicitudes ORDER BY fecha_creacion DESC LIMIT 200";
        }

        $result = $conn->query($sql);
        if ($result) {
            $solicitudes = [];
            while ($r = $result->fetch_assoc()) {
                $solicitudes[] = [
                    'id' => (int)$r['id'],
                    'empleado' => is_numeric($r['empleado']) ? 'Usuario ' . $r['empleado'] : $r['empleado'],
                    'avatar' => strtoupper(substr((string)$r['empleado'],0,2)),
                    'tipo' => $r['tipo'] ?? '',
                    'fecha' => $r['fecha'] ?? null,
                    'dias' => $r['dias'] ?? 1,
                    'estado' => $r['estado'] ?? 'pendiente',
                    'prioridad' => $r['prioridad'] ?? 'media'
                ];
            }
        }
    }
} catch (Exception $e) {
    // En caso de error, mantenemos $solicitudes del mock_data.php
}
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Solicitudes</h1>
            <p class="text-gray-600">Administra las solicitudes de vacaciones, permisos y ausencias de tu equipo.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pendientes</p>
                        <p class="text-3xl font-bold text-yellow-600">8</p>
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
                        <p class="text-3xl font-bold text-green-600">24</p>
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
                        <p class="text-3xl font-bold text-red-600">3</p>
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
                        <p class="text-3xl font-bold text-blue-600">35</p>
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
                    <div class="flex space-x-2">
                        <button id="btn-new-solicitud" type="button" class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all duration-200 shadow-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Nueva Solicitud
                        </button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Empleado</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Días</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($solicitudes as $solicitud): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-primary to-primary-dark rounded-lg flex items-center justify-center text-white text-sm font-semibold mr-3">
                                        <?= $solicitud['avatar'] ?>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900"><?= $solicitud['empleado'] ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $solicitud['tipo'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y', strtotime($solicitud['fecha'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= $solicitud['dias'] ?> días
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge-<?= $solicitud['estado'] ?> px-3 py-1 rounded-full text-xs font-semibold">
                                    <?= ucfirst($solicitud['estado']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button class="text-primary hover:text-primary-dark mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-blue-600 hover:text-blue-800 mr-2">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <?php if (isset($solicitud['estado']) && strtolower($solicitud['estado']) === 'pendiente'): ?>
                                    <button type="button" class="btn-approve inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition mr-2" data-id="<?= isset($solicitud['id']) ? $solicitud['id'] : '' ?>" title="Aprobar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn-reject inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition" data-id="<?= isset($solicitud['id']) ? $solicitud['id'] : '' ?>" title="Rechazar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Handler para crear nueva solicitud (usa prompts actuales)
document.addEventListener('DOMContentLoaded', function() {
    const btnNew = document.getElementById('btn-new-solicitud');
    if (btnNew) {
        btnNew.addEventListener('click', async function() {
            const tipo = prompt('Tipo de permiso (ej: Vacaciones, Enfermedad, Permiso personal):');
            if (!tipo) return alert('Tipo requerido');
            const fecha_inicio = prompt('Fecha inicio (YYYY-MM-DD):');
            if (!fecha_inicio) return alert('Fecha inicio requerido');
            const fecha_fin = prompt('Fecha fin (YYYY-MM-DD):');
            if (!fecha_fin) return alert('Fecha fin requerido');
            const dias = prompt('Número de días:');
            if (!dias) return alert('Días requeridos');
            const motivo = prompt('Motivo (opcional):') || '';

            const form = new FormData();
            form.append('tipo', tipo);
            form.append('fecha_inicio', fecha_inicio);
            form.append('fecha_fin', fecha_fin);
            form.append('dias', dias);
            form.append('motivo', motivo);

            try {
                const res = await fetch('api/solicitud_create.php', { method: 'POST', body: form });
                if (!res.ok) throw new Error('Error creando solicitud');
                const data = await res.json();
                if (data && data.success) {
                    alert('Solicitud enviada');
                    location.reload();
                } else {
                    alert(data.message || 'Error al crear solicitud');
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al crear solicitud');
            }
        });
    }

    // Delegation para botones aprobar/rechazar
    document.body.addEventListener('click', async function(e) {
        const approveBtn = e.target.closest && e.target.closest('.btn-approve');
        const rejectBtn = e.target.closest && e.target.closest('.btn-reject');

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
});
</script>
