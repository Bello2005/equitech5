<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

// Requerir autenticación
requireLogin();

// Obtener datos del usuario actual
$usuario = getCurrentUser();

$page_title = 'Políticas Corporativas';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

// Intentar cargar políticas y estadísticas desde la base de datos
$politicas = [];
$stats = [
    'total' => 0,
    'por_categoria' => []
];

try {
    $conn = getConnection();

    // Cargar políticas
    $sql = "SELECT id, nombre, categoria, descripcion, aplicable_a, activo, fecha_creacion, fecha_actualizacion
            FROM politicas
            WHERE activo = 1
            ORDER BY categoria, nombre";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $politicas[] = $row;
        }
    }

    // Calcular estadísticas
    $stats['total'] = count($politicas);
    foreach ($politicas as $pol) {
        $cat = $pol['categoria'];
        if (!isset($stats['por_categoria'][$cat])) {
            $stats['por_categoria'][$cat] = 0;
        }
        $stats['por_categoria'][$cat]++;
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Error cargando políticas: " . $e->getMessage());
    // Si falla la base de datos, usar políticas por defecto
    $politicas = [
        ['id' => 1, 'nombre' => 'Permiso de Vacaciones', 'categoria' => 'vacaciones',
         'descripcion' => 'Permiso de vacaciones: 15 días hábiles por periodo de un año.',
         'aplicable_a' => 'todos', 'activo' => 1, 'fecha_creacion' => date('Y-m-d H:i:s'),
         'fecha_actualizacion' => date('Y-m-d H:i:s')],
        ['id' => 2, 'nombre' => 'Permiso de Maternidad/Paternidad', 'categoria' => 'permisos',
         'descripcion' => 'Permiso maternidad o paternidad: mujeres 4 meses, hombres 15 días.',
         'aplicable_a' => 'todos', 'activo' => 1, 'fecha_creacion' => date('Y-m-d H:i:s'),
         'fecha_actualizacion' => date('Y-m-d H:i:s')],
        ['id' => 3, 'nombre' => 'Permisos Médicos', 'categoria' => 'permisos',
         'descripcion' => 'Permisos médicos (deben estar acompañados de las órdenes médicas, todo anexo que tenga que ver con la enfermedad).',
         'aplicable_a' => 'todos', 'activo' => 1, 'fecha_creacion' => date('Y-m-d H:i:s'),
         'fecha_actualizacion' => date('Y-m-d H:i:s')],
        ['id' => 4, 'nombre' => 'Permiso por Servicio como Jurado', 'categoria' => 'permisos',
         'descripcion' => 'Permiso por ser miembro o jurado.',
         'aplicable_a' => 'todos', 'activo' => 1, 'fecha_creacion' => date('Y-m-d H:i:s'),
         'fecha_actualizacion' => date('Y-m-d H:i:s')],
    ];
    $stats['total'] = count($politicas);
}
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Encabezado -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Políticas Corporativas</h1>
                    <p class="mt-2 text-sm text-gray-700">
                        Gestión y consulta de políticas corporativas
                    </p>
                </div>
                <button id="btn-nueva-politica" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Nueva Política
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-book text-3xl text-gray-400"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Políticas</dt>
                                <dd class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-umbrella-beach text-3xl text-green-400"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Vacaciones</dt>
                                <dd class="text-2xl font-bold text-gray-900"><?= $stats['por_categoria']['vacaciones'] ?? 0 ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check text-3xl text-blue-400"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Permisos</dt>
                                <dd class="text-2xl font-bold text-gray-900"><?= $stats['por_categoria']['permisos'] ?? 0 ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-laptop-house text-3xl text-purple-400"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Teletrabajo</dt>
                                <dd class="text-2xl font-bold text-gray-900"><?= $stats['por_categoria']['teletrabajo'] ?? 0 ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de políticas -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($politicas as $politica):
                $categoria_color = [
                    'vacaciones' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-umbrella-beach'],
                    'permisos' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-calendar-check'],
                    'teletrabajo' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'icon' => 'fa-laptop-house'],
                    'general' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-book']
                ][$politica['categoria'] ?? 'general'];
            ?>
            <div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">
                <div class="px-4 py-5 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="h-10 w-10 rounded-lg <?= $categoria_color['bg'] ?> <?= $categoria_color['text'] ?> flex items-center justify-center">
                                    <i class="fas <?= $categoria_color['icon'] ?>"></i>
                                </span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <?= htmlspecialchars($politica['nombre']) ?>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Categoría: <?= ucfirst($politica['categoria']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="text-gray-400 hover:text-gray-500" onclick="editarPolitica(<?= $politica['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-red-400 hover:text-red-500" onclick="eliminarPolitica(<?= $politica['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-4 sm:px-6">
                    <p class="text-sm text-gray-600">
                        <?= nl2br(htmlspecialchars($politica['descripcion'])) ?>
                    </p>
                </div>
                <div class="px-4 py-4 sm:px-6 bg-gray-50">
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>
                            <i class="fas fa-clock mr-1"></i>
                            Actualizado: <?= date('d/m/Y', strtotime($politica['fecha_actualizacion'] ?? $politica['fecha_creacion'])) ?>
                        </span>
                        <button class="text-primary hover:text-primary-dark" onclick="verDetalles(<?= $politica['id'] ?>)">
                            Ver más
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
// Funciones para gestionar políticas
function editarPolitica(id) {
    // Abrir modal de edición con datos cargados desde el API
    fetch(`api/politicas_action.php?id=${encodeURIComponent(id)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.politica) return alert('No se pudo cargar la política');
            const p = data.politica;
            document.getElementById('modal-title').textContent = 'Editar política';
            document.getElementById('politica-id').value = p.id;
            document.getElementById('politica-nombre').value = p.nombre;
            document.getElementById('politica-categoria').value = p.categoria;
            document.getElementById('politica-descripcion').value = p.descripcion;
            showPoliticaModal();
        }).catch(err => { console.error(err); alert('Error al cargar política'); });
}

function eliminarPolitica(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta política?')) return;
    fetch('api/politicas_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id: id})
    }).then(r => r.json()).then(resp => {
        if (resp.success) location.reload();
        else alert(resp.error || 'Error al eliminar');
    }).catch(err => { console.error(err); alert('Error de red'); });
}

function verDetalles(id) {
    // Cargar y mostrar modal solo lectura
    fetch(`api/politicas_action.php?id=${encodeURIComponent(id)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.politica) return alert('No se pudo cargar la política');
            const p = data.politica;
            document.getElementById('modal-title').textContent = p.nombre;
            document.getElementById('politica-id').value = p.id;
            document.getElementById('politica-nombre').value = p.nombre;
            document.getElementById('politica-categoria').value = p.categoria;
            document.getElementById('politica-descripcion').value = p.descripcion;
            // Desactivar edición para vista detalle
            document.getElementById('politica-nombre').disabled = true;
            document.getElementById('politica-categoria').disabled = true;
            document.getElementById('politica-descripcion').disabled = true;
            document.getElementById('modal-save').style.display = 'none';
            showPoliticaModal();
        }).catch(err => { console.error(err); alert('Error al cargar política'); });
}

// Manejador para nueva política
document.getElementById('btn-nueva-politica')?.addEventListener('click', function() {
    // Abrir modal vacío para crear
    document.getElementById('modal-title').textContent = 'Nueva política';
    document.getElementById('politica-id').value = '';
    document.getElementById('politica-nombre').value = '';
    document.getElementById('politica-categoria').value = 'general';
    document.getElementById('politica-descripcion').value = '';
    document.getElementById('politica-nombre').disabled = false;
    document.getElementById('politica-categoria').disabled = false;
    document.getElementById('politica-descripcion').disabled = false;
    document.getElementById('modal-save').style.display = 'inline-flex';
    showPoliticaModal();
});
</script>

<!-- Modal simple para ver/editar políticas -->
<div id="politica-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-2xl">
        <div class="px-6 py-4 border-b">
            <h3 id="modal-title" class="text-lg font-semibold">Detalle</h3>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="politica-id">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input id="politica-nombre" class="mt-1 block w-full border-gray-300 rounded-md p-2" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Categoría</label>
                <select id="politica-categoria" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                    <option value="vacaciones">vacaciones</option>
                    <option value="permisos">permisos</option>
                    <option value="teletrabajo">teletrabajo</option>
                    <option value="general">general</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea id="politica-descripcion" class="mt-1 block w-full border-gray-300 rounded-md p-2" rows="6"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-end space-x-2">
            <button id="modal-cancel" class="px-4 py-2 bg-gray-100 rounded">Cerrar</button>
            <button id="modal-save" class="px-4 py-2 bg-primary text-white rounded">Guardar</button>
        </div>
    </div>
</div>

<script>
function showPoliticaModal() {
    document.getElementById('politica-modal').classList.remove('hidden');
    document.getElementById('politica-modal').classList.add('flex');
}
function hidePoliticaModal() {
    document.getElementById('politica-modal').classList.add('hidden');
    document.getElementById('politica-modal').classList.remove('flex');
}

document.getElementById('modal-cancel')?.addEventListener('click', function() {
    // Habilitar campos por si estaban deshabilitados
    document.getElementById('politica-nombre').disabled = false;
    document.getElementById('politica-categoria').disabled = false;
    document.getElementById('politica-descripcion').disabled = false;
    document.getElementById('modal-save').style.display = 'inline-flex';
    hidePoliticaModal();
});

document.getElementById('modal-save')?.addEventListener('click', function() {
    const id = document.getElementById('politica-id').value;
    const nombre = document.getElementById('politica-nombre').value.trim();
    const categoria = document.getElementById('politica-categoria').value;
    const descripcion = document.getElementById('politica-descripcion').value.trim();
    if (!nombre || !descripcion) return alert('Nombre y descripción son obligatorios');

    const action = id ? 'update' : 'create';
    const body = { action, nombre, categoria, descripcion };
    if (id) body.id = id;

    fetch('api/politicas_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(body)
    }).then(r => r.json()).then(resp => {
        if (resp.success) location.reload();
        else alert(resp.error || 'Error al guardar');
    }).catch(err => { console.error(err); alert('Error de red'); });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>