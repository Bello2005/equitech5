<?php
require_once __DIR__ . '/../config/session.php';

requireLogin();
$usuario = getCurrentUser();

if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80';
}

$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

$page_title = 'Configuración';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Configuración del Sistema</h1>
            <p class="text-gray-600">Administra las preferencias y configuraciones de tu cuenta y del sistema.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- Sidebar Navigation -->
            <div class="xl:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <nav class="space-y-2">
                        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium bg-primary/10 text-primary rounded-xl">
                            <i class="fas fa-user mr-3"></i>
                            Perfil
                        </a>
                        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl">
                            <i class="fas fa-lock mr-3"></i>
                            Seguridad
                        </a>
                        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl">
                            <i class="fas fa-bell mr-3"></i>
                            Notificaciones
                        </a>
                        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl">
                            <i class="fas fa-palette mr-3"></i>
                            Apariencia
                        </a>
                        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl">
                            <i class="fas fa-cog mr-3"></i>
                            Sistema
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="xl:col-span-3 space-y-6">
                <!-- Profile Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-xl font-semibold text-gray-900">Información del Perfil</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center space-x-6 mb-6">
                            <img src="<?= $usuario['avatar'] ?>" alt="<?= $usuario['nombre'] ?>"
                                 class="w-24 h-24 rounded-2xl object-cover border-4 border-gray-100">
                            <div>
                                <button class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors mb-2">
                                    <i class="fas fa-camera mr-2"></i>
                                    Cambiar Foto
                                </button>
                                <p class="text-sm text-gray-500">JPG, GIF o PNG. Máx 2MB</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre Completo</label>
                                <input type="text" value="<?= $usuario['nombre'] ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <input type="email" value="<?= $usuario['email'] ?? 'admin@comfachoco.com' ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Rol</label>
                                <input type="text" value="<?= $usuario['rol'] ?>" disabled
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 text-gray-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Empresa</label>
                                <input type="text" value="<?= $usuario['empresa'] ?>" disabled
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 text-gray-500">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Password Change -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-xl font-semibold text-gray-900">Cambiar Contraseña</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña Actual</label>
                                <input type="password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nueva Contraseña</label>
                                <input type="password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                                <input type="password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
                                Actualizar Contraseña
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notifications Settings -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-xl font-semibold text-gray-900">Preferencias de Notificación</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <div>
                                    <p class="font-medium text-gray-900">Notificaciones por Email</p>
                                    <p class="text-sm text-gray-500">Recibir actualizaciones importantes por correo</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <div>
                                    <p class="font-medium text-gray-900">Notificaciones Push</p>
                                    <p class="text-sm text-gray-500">Recibir notificaciones en el navegador</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3">
                                <div>
                                    <p class="font-medium text-gray-900">Resumen Semanal</p>
                                    <p class="text-sm text-gray-500">Recibir resumen de actividad semanal</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-red-50 border border-red-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-red-200">
                        <h3 class="text-xl font-semibold text-red-900">Zona de Peligro</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-red-900 mb-1">Eliminar Cuenta</p>
                                <p class="text-sm text-red-700">Una vez eliminada, no podrás recuperar tu cuenta.</p>
                            </div>
                            <button class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors">
                                Eliminar Cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
