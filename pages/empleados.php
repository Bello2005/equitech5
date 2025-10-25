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

$page_title = 'Equipo';

// Cargar empleados con información completa
$miembros_equipo = [];
$stats = ['total' => 0, 'activos' => 0, 'vacaciones' => 0, 'nuevos_mes' => 0];

try {
    $conn = getConnection();

    // Query mejorada con tipo de empleado, departamento y días disponibles
    $sql = "SELECT
                u.id,
                u.nombre,
                u.email,
                u.rol,
                u.departamento,
                u.activo,
                u.fecha_creacion,
                u.fecha_ingreso,
                u.dias_vacaciones_acumulados,
                u.periodos_acumulados,
                te.nombre as tipo_empleado,
                pv.dias_por_periodo
            FROM usuarios u
            LEFT JOIN tipos_empleado te ON te.id = u.tipo_empleado_id
            LEFT JOIN politicas_vacaciones pv ON pv.tipo_empleado_id = u.tipo_empleado_id
            WHERE u.rol IN ('empleado', 'gerente', 'admin')
            ORDER BY u.nombre ASC";

    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Calcular días disponibles
            $dias_ganados = 0;
            $dias_usados = $row['dias_vacaciones_acumulados'] ?? 0;

            if ($row['fecha_ingreso']) {
                $dias_trabajados = (strtotime('now') - strtotime($row['fecha_ingreso'])) / 86400;
                $periodos_completos = floor($dias_trabajados / 360);
                $periodos_disponibles = min($periodos_completos, 2); // Máximo 2 períodos
                $dias_por_periodo = $row['dias_por_periodo'] ?? 15;
                $dias_ganados = $periodos_disponibles * $dias_por_periodo;
            }

            $dias_disponibles = $dias_ganados - $dias_usados;

            // Verificar si está en vacaciones actualmente
            $sql_vacaciones = "SELECT COUNT(*) as en_vacaciones FROM solicitudes
                             WHERE usuario_id = ?
                             AND tipo = 'vacaciones'
                             AND estado = 'aprobado'
                             AND fecha_inicio <= CURDATE()
                             AND fecha_fin >= CURDATE()";
            $stmt = $conn->prepare($sql_vacaciones);
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            $vac_result = $stmt->get_result()->fetch_assoc();
            $en_vacaciones = $vac_result['en_vacaciones'] > 0;
            $stmt->close();

            $miembros_equipo[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'email' => $row['email'],
                'puesto' => ucfirst($row['rol']),
                'tipo_empleado' => $row['tipo_empleado'] ?? 'No asignado',
                'departamento' => $row['departamento'] ?? 'General',
                'dias_disponibles' => $dias_disponibles,
                'dias_ganados' => $dias_ganados,
                'dias_usados' => $dias_usados,
                'fecha_ingreso' => $row['fecha_ingreso'],
                'avatar' => 'https://i.pravatar.cc/150?u=' . urlencode($row['email']),
                'estado' => $en_vacaciones ? 'vacaciones' : ($row['activo'] ? 'activo' : 'inactivo')
            ];

            // Calcular stats
            $stats['total']++;
            if ($row['activo']) $stats['activos']++;
            if ($en_vacaciones) $stats['vacaciones']++;
            if ($row['fecha_creacion'] && date('Y-m', strtotime($row['fecha_creacion'])) == date('Y-m')) {
                $stats['nuevos_mes']++;
            }
        }
    }
    $conn->close();
} catch (Exception $e) {
    error_log("Error cargando empleados: " . $e->getMessage());
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Equipo</h1>
            <p class="text-gray-600">Administra y visualiza toda la información de los miembros de tu equipo.</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Empleados</p>
                        <p class="text-3xl font-bold text-gray-900"><?= count($miembros_equipo) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Activos</p>
                        <p class="text-3xl font-bold text-green-600"><?= $stats['activos'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">En Vacaciones</p>
                        <p class="text-3xl font-bold text-yellow-600"><?= $stats['vacaciones'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-plane text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Departamentos</p>
                        <p class="text-3xl font-bold text-purple-600">5</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-building text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members Grid -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Empleados</h3>
                    <button id="btn-agregar-empleado" type="button" class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>
                        Agregar Empleado
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($miembros_equipo as $miembro): ?>
                    <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-6 border border-gray-200 hover:shadow-md transition-all duration-200">
                        <div class="flex items-start justify-between mb-4">
                            <img src="<?= $miembro['avatar'] ?>" alt="<?= $miembro['nombre'] ?>"
                                 class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-md">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $miembro['estado'] == 'activo' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                <?= $miembro['estado'] == 'activo' ? 'Activo' : 'Vacaciones' ?>
                            </span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-1"><?= $miembro['nombre'] ?></h4>
                        <p class="text-sm text-gray-600 mb-1"><?= $miembro['puesto'] ?></p>
                        <p class="text-xs text-gray-500 mb-2"><?= $miembro['departamento'] ?></p>
                        <p class="text-xs text-gray-500 mb-3">
                            <i class="fas fa-id-card mr-1"></i><?= $miembro['tipo_empleado'] ?>
                        </p>

                        <!-- Días de vacaciones disponibles -->
                        <div class="bg-green-50 rounded-lg p-3 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-green-700 font-medium">Días disponibles</span>
                                <span class="text-lg font-bold text-green-800"><?= number_format($miembro['dias_disponibles'], 1) ?></span>
                            </div>
                            <div class="text-xs text-green-600 mt-1">
                                <?= number_format($miembro['dias_ganados'], 1) ?> ganados - <?= number_format($miembro['dias_usados'], 1) ?> usados
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <button type="button" class="text-primary hover:text-primary-dark text-sm editar-empleado" 
                                    data-id="<?= $miembro['id'] ?>"
                                    data-cedula="<?= $miembro['cedula'] ?>"
                                    data-primer-nombre="<?= $miembro['primer_nombre'] ?>"
                                    data-primer-apellido="<?= $miembro['primer_apellido'] ?>"
                                    data-tipo-empleado="<?= $miembro['tipo_empleado_id'] ?>"
                                    data-departamento="<?= $miembro['departamento'] ?>">
                                <i class="fas fa-edit mr-1"></i>
                                Editar
                            </button>
                            <button type="button" class="btn-opciones text-gray-600 hover:text-gray-900" data-id="<?= $miembro['id'] ?>">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar empleado -->
    <div id="modal-editar-empleado" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-6 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-2xl bg-white mb-10">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Editar Empleado</h3>
                    <button id="close-modal-editar" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="form-editar-empleado" class="space-y-5">
                    <input type="hidden" id="editar-empleado-id" name="id">
                    
                    <!-- Cédula -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Cédula <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="editar-empleado-cedula" name="cedula" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="Ej: 1234567890">
                    </div>

                    <!-- Nombre y Apellido -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Primer Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="editar-empleado-primer-nombre" name="primer_nombre" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                   placeholder="Ej: Juan">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Primer Apellido <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="editar-empleado-primer-apellido" name="primer_apellido" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                   placeholder="Ej: Pérez">
                        </div>
                    </div>

                    <!-- Tipo de Empleado y Departamento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipo de Empleado <span class="text-red-500">*</span>
                            </label>
                            <select id="editar-empleado-tipo" name="tipo_empleado_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition">
                                <option value="">Seleccionar tipo</option>
                                <?php
                                try {
                                    $conn = getConnection();
                                    $result = $conn->query("SELECT id, nombre FROM tipos_empleado WHERE activo = 1 ORDER BY nombre");
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nombre']) . '</option>';
                                    }
                                    $conn->close();
                                } catch (Exception $e) {
                                    error_log("Error cargando tipos: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Departamento <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="editar-empleado-departamento" name="departamento" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                   placeholder="Ej: Recursos Humanos">
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" id="btn-cancelar-editar" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-submit-editar" class="px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition shadow-sm">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Modal para agregar empleado -->
<div id="modal-agregar-empleado" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-6 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-2xl bg-white mb-10">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Agregar Nuevo Empleado</h3>
                <button id="close-modal-empleado" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="form-nuevo-empleado" class="space-y-5">
                <!-- Cédula -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Cédula <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="empleado-cedula" name="cedula" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                           placeholder="Ej: 1234567890">
                </div>

                <!-- Nombre y Apellido -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Primer Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="empleado-primer-nombre" name="primer_nombre" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="Ej: Juan">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Primer Apellido <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="empleado-primer-apellido" name="primer_apellido" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="Ej: Pérez">
                    </div>
                </div>

                <!-- Tipo de Empleado y Departamento -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Tipo de Empleado <span class="text-red-500">*</span>
                        </label>
                        <select id="empleado-tipo" name="tipo_empleado_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition">
                            <option value="">Seleccionar tipo</option>
                            <?php
                            try {
                                $conn = getConnection();
                                $result = $conn->query("SELECT id, nombre FROM tipos_empleado WHERE activo = 1 ORDER BY nombre");
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nombre']) . '</option>';
                                }
                                $conn->close();
                            } catch (Exception $e) {
                                error_log("Error cargando tipos: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Departamento <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="empleado-departamento" name="departamento" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="Ej: Recursos Humanos">
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" id="btn-cancelar-empleado" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-submit-empleado" class="px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition shadow-sm">
                        <i class="fas fa-user-plus mr-2"></i>
                        Crear Empleado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handler para agregar empleado
document.addEventListener('DOMContentLoaded', function() {
    const btnAgregar = document.getElementById('btn-agregar-empleado');
    const modal = document.getElementById('modal-agregar-empleado');
    const closeModal = document.getElementById('close-modal-empleado');
    const btnCancelar = document.getElementById('btn-cancelar-empleado');
    const form = document.getElementById('form-nuevo-empleado');

    // Abrir modal
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function() {
            modal.classList.remove('hidden');
        });
    }

    // Cerrar modal
    const cerrarModal = () => {
        modal.classList.add('hidden');
        form.reset();
    };

    if (closeModal) closeModal.addEventListener('click', cerrarModal);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModal);

    // Cerrar al hacer clic fuera
    modal?.addEventListener('click', function(e) {
        if (e.target === modal) cerrarModal();
    });

    // Submit del formulario
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const btnSubmit = document.getElementById('btn-submit-empleado');
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creando...';

                const res = await fetch('api/empleado_create.php', {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) throw new Error('Error creando empleado');

                const data = await res.json();

                if (data && data.success) {
                    alert('Empleado creado exitosamente');
                    cerrarModal();
                    location.reload();
                } else {
                    alert(data.message || 'Error al crear empleado');
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al crear empleado');
            } finally {
                const btnSubmit = document.getElementById('btn-submit-empleado');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-user-plus mr-2"></i> Crear Empleado';
            }
        });
    }

    // Handler para editar empleado
    const modalEditar = document.getElementById('modal-editar-empleado');
    const formEditar = document.getElementById('form-editar-empleado');
    const closeModalEditar = document.getElementById('close-modal-editar');
    const btnCancelarEditar = document.getElementById('btn-cancelar-editar');

    // Cerrar modal de edición
    const cerrarModalEditar = () => {
        modalEditar.classList.add('hidden');
        formEditar.reset();
    };

    if (closeModalEditar) closeModalEditar.addEventListener('click', cerrarModalEditar);
    if (btnCancelarEditar) btnCancelarEditar.addEventListener('click', cerrarModalEditar);

    // Cerrar al hacer clic fuera
    modalEditar?.addEventListener('click', function(e) {
        if (e.target === modalEditar) cerrarModalEditar();
    });

    // Abrir modal de edición
    document.querySelectorAll('.editar-empleado').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const cedula = this.getAttribute('data-cedula');
            const primerNombre = this.getAttribute('data-primer-nombre');
            const primerApellido = this.getAttribute('data-primer-apellido');
            const tipoEmpleado = this.getAttribute('data-tipo-empleado');
            const departamento = this.getAttribute('data-departamento');

            // Llenar el formulario
            document.getElementById('editar-empleado-id').value = id;
            document.getElementById('editar-empleado-cedula').value = cedula;
            document.getElementById('editar-empleado-primer-nombre').value = primerNombre;
            document.getElementById('editar-empleado-primer-apellido').value = primerApellido;
            document.getElementById('editar-empleado-tipo').value = tipoEmpleado;
            document.getElementById('editar-empleado-departamento').value = departamento;

            modalEditar.classList.remove('hidden');
        });
    });

    // Submit del formulario de edición
    if (formEditar) {
        formEditar.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(formEditar);

            try {
                const btnSubmit = document.getElementById('btn-submit-editar');
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';

                const res = await fetch('api/empleado_update.php', {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) throw new Error('Error actualizando empleado');

                const data = await res.json();

                if (data && data.success) {
                    alert('Empleado actualizado exitosamente');
                    cerrarModalEditar();
                    location.reload();
                } else {
                    alert(data.message || 'Error al actualizar empleado');
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al actualizar empleado');
            } finally {
                const btnSubmit = document.getElementById('btn-submit-editar');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-save mr-2"></i> Guardar Cambios';
            }
        });
    }

    // Handler para menú de opciones
    document.querySelectorAll('.btn-opciones').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            
            const opciones = [
                { valor: 'eliminar', texto: 'Eliminar empleado' },
                { valor: 'cambiar-estado', texto: 'Cambiar estado' }
            ];

            // Crear el menú desplegable
            const menu = document.createElement('div');
            menu.className = 'absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50';
            menu.style.top = '100%';

            const lista = document.createElement('div');
            lista.className = 'py-1';
            lista.role = 'menu';

            opciones.forEach(opcion => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100';
                item.textContent = opcion.texto;
                item.addEventListener('click', () => manejarAccion(opcion.valor, id));
                lista.appendChild(item);
            });

            menu.appendChild(lista);
            
            // Remover menús existentes
            document.querySelectorAll('.menu-opciones').forEach(m => m.remove());
            menu.classList.add('menu-opciones');
            
            // Posicionar y mostrar el menú
            this.parentNode.style.position = 'relative';
            this.parentNode.appendChild(menu);
            
            // Cerrar al hacer clic fuera
            const cerrarMenu = (e) => {
                if (!menu.contains(e.target) && !this.contains(e.target)) {
                    menu.remove();
                    document.removeEventListener('click', cerrarMenu);
                }
            };
            
            setTimeout(() => document.addEventListener('click', cerrarMenu), 0);
        });
    });

    // Función para manejar las acciones del menú
    async function manejarAccion(accion, id) {
        if (accion === 'eliminar') {
            if (!confirm('¿Está seguro que desea eliminar este empleado?')) return;
            
            try {
                const res = await fetch('api/empleado_delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const data = await res.json();

                if (data && data.success) {
                    alert('Empleado eliminado exitosamente');
                    location.reload();
                } else {
                    alert(data.message || 'Error al eliminar empleado');
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al eliminar empleado');
            }
        } else if (accion === 'cambiar-estado') {
            const estado = prompt('¿Nuevo estado? (activo/inactivo):', 'activo');
            if (!estado || !['activo', 'inactivo'].includes(estado.toLowerCase())) {
                return alert('Estado inválido');
            }

            try {
                const res = await fetch('api/empleado_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        id: id, 
                        activo: estado.toLowerCase() === 'activo' ? 1 : 0 
                    })
                });

                const data = await res.json();

                if (data && data.success) {
                    alert('Estado actualizado exitosamente');
                    location.reload();
                } else {
                    alert(data.message || 'Error al actualizar estado');
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al actualizar estado');
            }
        }
    }
});
</script>
