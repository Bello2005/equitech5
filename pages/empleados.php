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

<script>
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
