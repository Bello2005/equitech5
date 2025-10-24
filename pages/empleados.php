<?php
require_once __DIR__ . '/../config/session.php';

requireLogin();
$usuario = getCurrentUser();

if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80';
}

$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

$page_title = 'Equipo';

// Datos de equipo de prueba
$miembros_equipo = [
    ['nombre' => 'Carlos Ruiz', 'puesto' => 'Desarrollador Senior', 'departamento' => 'Desarrollo', 'email' => 'carlos.ruiz@comfachoco.com', 'avatar' => 'https://i.pravatar.cc/150?img=12', 'estado' => 'activo'],
    ['nombre' => 'Ana Mendoza', 'puesto' => 'Diseñadora UX/UI', 'departamento' => 'Diseño', 'email' => 'ana.mendoza@comfachoco.com', 'avatar' => 'https://i.pravatar.cc/150?img=5', 'estado' => 'activo'],
    ['nombre' => 'David Torres', 'puesto' => 'Gerente de Proyecto', 'departamento' => 'Gestión', 'email' => 'david.torres@comfachoco.com', 'avatar' => 'https://i.pravatar.cc/150?img=33', 'estado' => 'vacaciones'],
    ['nombre' => 'Laura Silva', 'puesto' => 'Analista de Datos', 'departamento' => 'Analytics', 'email' => 'laura.silva@comfachoco.com', 'avatar' => 'https://i.pravatar.cc/150?img=9', 'estado' => 'activo'],
    ['nombre' => 'Miguel Santos', 'puesto' => 'DevOps Engineer', 'departamento' => 'Infraestructura', 'email' => 'miguel.santos@comfachoco.com', 'avatar' => 'https://i.pravatar.cc/150?img=15', 'estado' => 'activo'],
    ['nombre' => 'Sofia Ramirez', 'puesto' => 'HR Manager', 'departamento' => 'Recursos Humanos', 'email' => 'sofia.ramirez@comfachoco.com', 'avatar' => 'https://i.pravatar.cc/150?img=20', 'estado' => 'activo'],
];

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
                        <p class="text-3xl font-bold text-green-600">5</p>
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
                        <p class="text-3xl font-bold text-yellow-600">1</p>
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

        <!-- Formularios Rápidos -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-xl font-semibold text-gray-900">Formularios Rápidos</h3>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600">Crear rápidamente un empleado o gestionar los tipos de empleo.</p>
                <div class="flex space-x-3">
                    <button id="btn-open-employee-form" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary-dark">
                        <i class="fas fa-user-plus mr-2"></i> Nuevo Empleado
                    </button>
                    <button id="btn-open-jobtypes" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-50">
                        <i class="fas fa-briefcase mr-2"></i> Tipos de Empleo
                    </button>
                </div>
            </div>
        </div>

        <!-- Team Members Grid -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Miembros del Equipo</h3>
                    <button id="btn-agregar-miembro" type="button" class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>
                        Agregar Miembro
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
                        <p class="text-xs text-gray-500 mb-4"><?= $miembro['departamento'] ?></p>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <a href="mailto:<?= $miembro['email'] ?>" class="text-primary hover:text-primary-dark text-sm">
                                <i class="fas fa-envelope mr-1"></i>
                                Email
                            </a>
                            <button type="button" class="btn-opciones text-gray-600 hover:text-gray-900" data-email="<?= $miembro['email'] ?>" data-nombre="<?= $miembro['nombre'] ?>">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Modal: Empleado -->
<div id="employee-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-2xl">
        <div class="px-6 py-4 border-b">
            <h3 id="employee-modal-title" class="text-lg font-semibold">Nuevo Empleado</h3>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="emp-id">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input id="emp-nombre" class="mt-1 block w-full border-gray-300 rounded-md p-2" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Correo</label>
                    <input id="emp-email" class="mt-1 block w-full border-gray-300 rounded-md p-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input id="emp-telefono" class="mt-1 block w-full border-gray-300 rounded-md p-2" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rol</label>
                    <input id="emp-rol" class="mt-1 block w-full border-gray-300 rounded-md p-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <select id="emp-estado" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Departamento (ID)</label>
                <input id="emp-departamento" class="mt-1 block w-full border-gray-300 rounded-md p-2" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tipo de Empleo</label>
                <select id="emp-job-type" class="mt-1 block w-full border-gray-300 rounded-md p-2">
                    <option value="">Cargando...</option>
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-end space-x-2">
            <button id="emp-modal-cancel" class="px-4 py-2 bg-gray-100 rounded">Cerrar</button>
            <button id="emp-modal-save" class="px-4 py-2 bg-primary text-white rounded">Guardar</button>
        </div>
    </div>
</div>

<!-- Modal: Tipos de Empleo (lista simple + crear con prompt) -->
<div id="jobtypes-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-xl">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Tipos de Empleo</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="mb-4">
                <h4 class="text-lg font-medium text-gray-900 mb-2">Departamentos</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2" id="departamentos-list">
                    <!-- se llenará con JS -->
                </div>
            </div>
            <div class="flex justify-end">
                <button id="jobtypes-add" class="px-4 py-2 bg-primary text-white rounded">Nuevo Tipo</button>
            </div>
            <div id="jobtypes-list" class="divide-y divide-gray-100 max-h-64 overflow-y-auto scrollbar-thin">
                <!-- se llenará con JS -->
            </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-end">
            <button id="jobtypes-close" class="px-4 py-2 bg-gray-100 rounded">Cerrar</button>
        </div>
    </div>
</div>

<script>
// Funciones para mostrar/ocultar modales
function showModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('hidden');
    el.classList.add('flex');
}
function hideModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('hidden');
    el.classList.remove('flex');
}

// Cargar tipos de empleo desde el endpoint y poblar select/lista
// Función para cargar los departamentos desde la API
async function fetchDepartamentos() {
    try {
        const res = await fetch('api/departamentos_action.php?action=list');
        const data = await res.json();
        return data.success ? data.departamentos || [] : [];
    } catch (err) {
        console.error('Error cargando departamentos', err);
        return [];
    }
}

async function fetchJobTypes() {
    try {
        const res = await fetch('api/job_types_action.php?action=list');
        const data = await res.json();
        return data.success ? data.job_types || [] : [];
    } catch (err) {
        console.error('Error cargando job types', err);
        return [];
    }
}

async function populateJobTypeSelect() {
    const select = document.getElementById('emp-job-type');
    if (!select) return;
    select.innerHTML = '<option value="">Cargando...</option>';
    const types = await fetchJobTypes();
    if (!types.length) {
        select.innerHTML = '<option value="">No hay tipos disponibles</option>';
        return;
    }
    select.innerHTML = '<option value="">Seleccione...</option>' + types.map(t => 
        `<option value="${t.id}">${escapeHtml(t.nombre)}</option>`).join('');
}

function escapeHtml(s) {
    return (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Abrir modal de empleado y poblar select
document.getElementById('btn-open-employee-form')?.addEventListener('click', async function() {
    document.getElementById('emp-id').value = '';
    document.getElementById('emp-nombre').value = '';
    document.getElementById('emp-email').value = '';
    document.getElementById('emp-telefono').value = '';
    document.getElementById('emp-rol').value = '';
    document.getElementById('emp-estado').value = 'activo';
    document.getElementById('emp-departamento').value = '';
    await populateJobTypeSelect();
    showModal('employee-modal');
});

document.getElementById('emp-modal-cancel')?.addEventListener('click', function() { 
    hideModal('employee-modal'); 
});

// Guardar empleado (simple, delega a api/empleados_action.php)
document.getElementById('emp-modal-save')?.addEventListener('click', async function() {
    const payload = {
        action: 'create',
        nombre: document.getElementById('emp-nombre').value.trim(),
        email: document.getElementById('emp-email').value.trim(),
        telefono: document.getElementById('emp-telefono').value.trim(),
        rol: document.getElementById('emp-rol').value.trim(),
        estado: document.getElementById('emp-estado').value,
        departamento_id: document.getElementById('emp-departamento').value || null,
        job_type_id: document.getElementById('emp-job-type').value || null
    };
    if (!payload.nombre) return alert('Nombre es obligatorio');
    try {
        const res = await fetch('api/empleados_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('Empleado creado');
            location.reload();
        } else {
            alert(data.error || 'Error al guardar empleado');
        }
    } catch (err) { console.error(err); alert('Error de red'); }
});

// Job types modal
document.getElementById('btn-open-jobtypes')?.addEventListener('click', async function() {
    await renderJobTypesList();
    showModal('jobtypes-modal');
});
document.getElementById('jobtypes-close')?.addEventListener('click', function() { 
    hideModal('jobtypes-modal'); 
});

async function renderJobTypesList(departamentoId = null) {
    const container = document.getElementById('jobtypes-list');
    container.innerHTML = '<div class="p-4 text-sm text-gray-500">Cargando...</div>';
    
    // Obtener tipos y departamentos
    const [types, departamentos] = await Promise.all([
        fetchJobTypes(),
        fetchDepartamentos()
    ]);
    
    // Crear mapa de departamentos para fácil acceso
    const deptMap = new Map(departamentos.map(d => [d.id, d]));
    
    let filteredTypes = types;
    if (departamentoId) {
        filteredTypes = types.filter(t => t.departamento_id === parseInt(departamentoId));
    }
    
    if (!filteredTypes.length) {
        container.innerHTML = '<div class="p-4 text-sm text-gray-500">No hay tipos registrados' + 
            (departamentoId ? ' para este departamento' : '') + '</div>';
        return;
    }
    
    container.innerHTML = filteredTypes.map(t => `
        <div class="p-4 flex items-center justify-between">
            <div>
                <div class="font-medium text-gray-900">${escapeHtml(t.nombre)}</div>
                <div class="text-sm text-gray-500 space-y-1">
                    ${t.descripcion ? `<div>${escapeHtml(t.descripcion)}</div>` : ''}
                    <div class="text-xs text-primary-dark">
                        ${t.departamento_id && deptMap.has(t.departamento_id) 
                          ? `<i class="fas fa-building mr-1"></i>${escapeHtml(deptMap.get(t.departamento_id).nombre)}` 
                          : ''}
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button data-id="${t.id}" class="jobtype-edit px-3 py-1 text-sm bg-gray-100 rounded">Editar</button>
                <button data-id="${t.id}" class="jobtype-delete px-3 py-1 text-sm bg-red-100 text-red-600 rounded">Eliminar</button>
            </div>
        </div>
    `).join('');

    // attach handlers
    document.querySelectorAll('.jobtype-delete').forEach(btn => btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        if (!confirm('Eliminar tipo de empleo #' + id + '?')) return;
        try {
            const res = await fetch('api/job_types_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'delete', id })
            });
            const data = await res.json();
            if (data.success) renderJobTypesList(); else alert(data.error || 'Error');
        } catch (err) { console.error(err); alert('Error de red'); }
    }));

    document.querySelectorAll('.jobtype-edit').forEach(btn => btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const nuevo = prompt('Nuevo nombre');
        if (!nuevo) return;
        try {
            const res = await fetch('api/job_types_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'update', id, nombre: nuevo })
            });
            const data = await res.json();
            if (data.success) renderJobTypesList(); else alert(data.error || 'Error');
        } catch (err) { console.error(err); alert('Error de red'); }
    }));
}

// Función para mostrar los departamentos
async function renderDepartamentosList() {
    const container = document.getElementById('departamentos-list');
    const departamentos = await fetchDepartamentos();
    
    if (!departamentos.length) {
        container.innerHTML = '<p class="col-span-3 text-sm text-gray-500">No hay departamentos disponibles</p>';
        return;
    }

    container.innerHTML = departamentos.map(d => `
        <button class="departamento-filter px-3 py-2 text-sm rounded-lg border ${d.active ? 'bg-primary text-white' : 'border-gray-300 hover:bg-gray-50'}"
                data-id="${d.id}">
            ${escapeHtml(d.nombre)}
        </button>
    `).join('');

    // Click handlers para filtrar por departamento
    document.querySelectorAll('.departamento-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            const wasActive = this.classList.contains('bg-primary');
            // Resetear todos los botones
            document.querySelectorAll('.departamento-filter').forEach(b => {
                b.classList.remove('bg-primary', 'text-white');
                b.classList.add('border-gray-300');
            });
            if (!wasActive) {
                // Activar este botón
                this.classList.add('bg-primary', 'text-white');
                this.classList.remove('border-gray-300');
                renderJobTypesList(this.dataset.id);
            } else {
                // Si estaba activo, mostrar todos
                renderJobTypesList();
            }
        });
    });
}

document.getElementById('jobtypes-add')?.addEventListener('click', async function() {
    const departamentos = await fetchDepartamentos();
    if (!departamentos.length) {
        return alert('Error: Primero debe haber departamentos disponibles');
    }

    const nombre = prompt('Nombre del tipo de empleo');
    if (!nombre) return;
    
    const descripcion = prompt('Descripción (opcional)') || '';
    
    // Mostrar lista de departamentos disponibles
    const deptOptions = departamentos.map((d, i) => `${i + 1}. ${d.nombre}`).join('\n');
    const deptIndex = prompt(`Seleccione el departamento (ingrese el número):\n${deptOptions}`);
    if (!deptIndex) return;
    
    const departamento = departamentos[parseInt(deptIndex) - 1];
    if (!departamento) {
        return alert('Departamento no válido');
    }

    try {
        const res = await fetch('api/job_types_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ 
                action: 'create', 
                nombre, 
                descripcion,
                departamento_id: departamento.id 
            })
        });
        const data = await res.json();
        if (data.success) {
            renderJobTypesList();
        } else {
            alert(data.error || 'Error');
        }
    } catch (err) { 
        console.error(err); 
        alert('Error de red'); 
    }
});

// Handler para agregar miembro
document.addEventListener('DOMContentLoaded', function() {
    const btnAgregar = document.getElementById('btn-agregar-miembro');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', async function() {
            const nombre = prompt('Nombre completo:');
            if (!nombre) return alert('Nombre requerido');
            const puesto = prompt('Puesto:');
            if (!puesto) return alert('Puesto requerido');
            const departamento = prompt('Departamento:');
            if (!departamento) return alert('Departamento requerido');
            const email = prompt('Email:');
            if (!email) return alert('Email requerido');
            const avatar = prompt('URL de avatar (opcional):') || '';
            const estado = prompt('Estado (activo/vacaciones):', 'activo');
            if (!estado) return alert('Estado requerido');

            const form = new FormData();
            form.append('nombre', nombre);
            form.append('puesto', puesto);
            form.append('departamento', departamento);
            form.append('email', email);
            form.append('avatar', avatar);
            form.append('estado', estado);

            try {
                const res = await fetch('api/equipo_create.php', { method: 'POST', body: form });
                if (!res.ok) throw new Error('Error creando miembro');
                const data = await res.json();
                if (data && data.success) {
                    alert('Miembro agregado');
                    location.reload();
                } else {
                    alert(data.message || 'Error al agregar miembro');
                }
            } catch (err) {
                console.error(err);
                alert('Error de red al agregar miembro');
            }
        });
    }

    // Handler para menú de opciones
    document.body.addEventListener('click', function(e) {
        const btnOpciones = e.target.closest('.btn-opciones');
        if (btnOpciones) {
            const nombre = btnOpciones.getAttribute('data-nombre');
            const email = btnOpciones.getAttribute('data-email');
            const accion = prompt(`Acción para ${nombre} (${email}):\n1. Editar\n2. Eliminar\n3. Cambiar estado\nEscribe el número de la acción:`);
            if (!accion) return;
            if (accion === '1') {
                // Editar
                const nuevoPuesto = prompt('Nuevo puesto:');
                if (!nuevoPuesto) return alert('Puesto requerido');
                fetch('api/equipo_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, puesto: nuevoPuesto })
                }).then(res => res.json()).then(data => {
                    if (data && data.success) {
                        alert('Puesto actualizado');
                        location.reload();
                    } else {
                        alert(data.message || 'Error al actualizar');
                    }
                }).catch(err => {
                    console.error(err);
                    alert('Error de red al actualizar');
                });
            } else if (accion === '2') {
                // Eliminar
                if (!confirm('¿Eliminar este miembro?')) return;
                fetch('api/equipo_delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email })
                }).then(res => res.json()).then(data => {
                    if (data && data.success) {
                        alert('Miembro eliminado');
                        location.reload();
                    } else {
                        alert(data.message || 'Error al eliminar');
                    }
                }).catch(err => {
                    console.error(err);
                    alert('Error de red al eliminar');
                });
            } else if (accion === '3') {
                // Cambiar estado
                const nuevoEstado = prompt('Nuevo estado (activo/vacaciones):', 'activo');
                if (!nuevoEstado) return alert('Estado requerido');
                fetch('api/equipo_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, estado: nuevoEstado })
                }).then(res => res.json()).then(data => {
                    if (data && data.success) {
                        alert('Estado actualizado');
                        location.reload();
                    } else {
                        alert(data.message || 'Error al actualizar estado');
                    }
                }).catch(err => {
                    console.error(err);
                    alert('Error de red al actualizar estado');
                });
            }
        }
    });
});
</script>
