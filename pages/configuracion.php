<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/user_actions.php';

requireLogin();
$usuario = getCurrentUser();

if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://ui-avatars.com/api/?name=' . urlencode($usuario['nombre']) . '&background=0B8A3A&color=fff&size=128';
}

$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

// Manejar actualizaciones
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $nombre = trim($_POST['nombre'] ?? '');
                $email = trim($_POST['email'] ?? '');

                if (empty($nombre) || empty($email)) {
                    $error = 'Todos los campos son requeridos';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Email inválido';
                } else {
                    $result = updateUserProfile($usuario['id'], $nombre, $email);
                    if ($result['success']) {
                        $success = $result['message'];
                        $usuario = getCurrentUser(); // Recargar datos
                    } else {
                        $error = $result['message'];
                    }
                }
                break;

            case 'change_password':
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    $error = 'Todos los campos de contraseña son requeridos';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Las contraseñas nuevas no coinciden';
                } else {
                    $validation = validatePassword($newPassword);
                    if (!$validation['valid']) {
                        $error = $validation['message'];
                    } else {
                        $result = changePassword($usuario['id'], $currentPassword, $newPassword);
                        if ($result['success']) {
                            $success = $result['message'];
                        } else {
                            $error = $result['message'];
                        }
                    }
                }
                break;

            case 'update_avatar':
                $avatarUrl = trim($_POST['avatar_url'] ?? '');
                if (!empty($avatarUrl) && filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
                    $result = updateUserAvatar($usuario['id'], $avatarUrl);
                    if ($result['success']) {
                        $success = $result['message'];
                        $usuario = getCurrentUser();
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = 'URL de avatar inválida';
                }
                break;
        }
    }
}

$page_title = 'Configuración';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8" x-data="configPage()">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Configuración del Sistema</h1>
            <p class="text-gray-600">Administra las preferencias y configuraciones de tu cuenta y del sistema.</p>
        </div>

        <!-- Mensajes de Feedback -->
        <?php if ($success): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl animate-fade-in" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span class="font-medium"><?= htmlspecialchars($success) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl animate-fade-in" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- Sidebar Navigation -->
            <div class="xl:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sticky top-24">
                    <nav class="space-y-2">
                        <button @click="activeTab = 'profile'"
                                :class="activeTab === 'profile' ? 'bg-primary/10 text-primary' : 'text-gray-700 hover:bg-gray-50'"
                                class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all">
                            <i class="fas fa-user mr-3"></i>
                            Perfil
                        </button>
                        <button @click="activeTab = 'security'"
                                :class="activeTab === 'security' ? 'bg-primary/10 text-primary' : 'text-gray-700 hover:bg-gray-50'"
                                class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all">
                            <i class="fas fa-lock mr-3"></i>
                            Seguridad
                        </button>
                        <button @click="activeTab = 'notifications'"
                                :class="activeTab === 'notifications' ? 'bg-primary/10 text-primary' : 'text-gray-700 hover:bg-gray-50'"
                                class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all">
                            <i class="fas fa-bell mr-3"></i>
                            Notificaciones
                        </button>
                        <button @click="activeTab = 'preferences'"
                                :class="activeTab === 'preferences' ? 'bg-primary/10 text-primary' : 'text-gray-700 hover:bg-gray-50'"
                                class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all">
                            <i class="fas fa-sliders-h mr-3"></i>
                            Preferencias
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="xl:col-span-3 space-y-6">
                <!-- Profile Information -->
                <div x-show="activeTab === 'profile'" x-transition class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-primary/5 to-transparent">
                        <h3 class="text-lg font-semibold text-gray-900">Información del Perfil</h3>
                        <p class="text-xs text-gray-500 mt-1">Actualiza tu información personal y foto de perfil</p>
                    </div>
                    <form method="POST" action="" class="p-5">
                        <input type="hidden" name="action" value="update_profile">

                        <!-- Avatar Section -->
                        <div class="flex items-center space-x-4 mb-5 p-4 bg-gradient-to-br from-primary/5 to-primary/10 rounded-xl border border-primary/20">
                            <img src="<?= htmlspecialchars($usuario['avatar']) ?>"
                                 alt="<?= htmlspecialchars($usuario['nombre']) ?>"
                                 class="w-20 h-20 rounded-xl object-cover border-3 border-white shadow-md ring-2 ring-primary/20"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre']) ?>&background=0B8A3A&color=fff&size=128'">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 text-sm mb-1">Foto de Perfil</h4>
                                <p class="text-xs text-gray-500 mb-2">JPG, GIF o PNG. Máx 2MB</p>
                                <button type="button"
                                        @click="showAvatarModal = true"
                                        class="px-3 py-1.5 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors shadow-sm">
                                    <i class="fas fa-camera mr-1.5 text-xs"></i>
                                    Cambiar Foto
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Nombre Completo <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="text"
                                           name="nombre"
                                           value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                           required
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="email"
                                           name="email"
                                           value="<?= htmlspecialchars($usuario['email']) ?>"
                                           required
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Rol</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-id-badge text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="text"
                                           value="<?= htmlspecialchars($usuario['rol']) ?>"
                                           disabled
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Empresa</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-building text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="text"
                                           value="<?= htmlspecialchars($usuario['empresa']) ?>"
                                           disabled
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info Section -->
                        <div class="mt-5 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-info-circle text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-blue-900 mb-1">Información de la Cuenta</h4>
                                    <div class="grid grid-cols-2 gap-3 text-xs">
                                        <div>
                                            <span class="text-blue-600 font-medium">Fecha de registro:</span>
                                            <p class="text-blue-800">15 de Enero, 2024</p>
                                        </div>
                                        <div>
                                            <span class="text-blue-600 font-medium">Último acceso:</span>
                                            <p class="text-blue-800">Hoy, 03:15 PM</p>
                                        </div>
                                        <div>
                                            <span class="text-blue-600 font-medium">Solicitudes creadas:</span>
                                            <p class="text-blue-800">24 solicitudes</p>
                                        </div>
                                        <div>
                                            <span class="text-blue-600 font-medium">Estado de cuenta:</span>
                                            <p class="text-blue-800"><span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-1"></span>Activa</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end space-x-2">
                            <button type="button"
                                    onclick="window.location.reload()"
                                    class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors shadow-md">
                                <i class="fas fa-save mr-1.5 text-xs"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Settings -->
                <div x-show="activeTab === 'security'" x-transition class="space-y-4">
                    <!-- Change Password -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-red-50 to-transparent">
                            <h3 class="text-lg font-semibold text-gray-900">Cambiar Contraseña</h3>
                            <p class="text-xs text-gray-500 mt-1">Asegúrate de usar una contraseña segura</p>
                        </div>
                        <form method="POST" action="" class="p-5" x-data="passwordForm()">
                            <input type="hidden" name="action" value="change_password">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        Contraseña Actual <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-lock text-gray-400 text-sm"></i>
                                        </div>
                                        <input :type="showCurrentPassword ? 'text' : 'password'"
                                               name="current_password"
                                               required
                                               class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                        <button type="button"
                                                @click="showCurrentPassword = !showCurrentPassword"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                            <i :class="showCurrentPassword ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        Nueva Contraseña <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-key text-gray-400 text-sm"></i>
                                        </div>
                                        <input :type="showNewPassword ? 'text' : 'password'"
                                               name="new_password"
                                               required
                                               x-model="newPassword"
                                               @input="validatePasswordStrength"
                                               class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                        <button type="button"
                                                @click="showNewPassword = !showNewPassword"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                            <i :class="showNewPassword ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                                        </button>
                                    </div>
                                    <!-- Password Strength Indicator -->
                                    <div x-show="newPassword.length > 0" class="mt-1.5">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                <div :class="passwordStrengthColor"
                                                     :style="`width: ${passwordStrength}%`"
                                                     class="h-full transition-all duration-300"></div>
                                            </div>
                                            <span class="text-xs font-medium" :class="passwordStrengthColor" x-text="passwordStrengthText"></span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        Confirmar Nueva Contraseña <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-check-circle text-gray-400 text-sm"></i>
                                        </div>
                                        <input :type="showConfirmPassword ? 'text' : 'password'"
                                               name="confirm_password"
                                               required
                                               x-model="confirmPassword"
                                               class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                                               :class="confirmPassword && newPassword !== confirmPassword ? 'border-red-500' : ''">
                                        <button type="button"
                                                @click="showConfirmPassword = !showConfirmPassword"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                            <i :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'" class="fas text-sm"></i>
                                        </button>
                                    </div>
                                    <p x-show="confirmPassword && newPassword !== confirmPassword"
                                       class="text-xs text-red-500 mt-1">
                                        Las contraseñas no coinciden
                                    </p>
                                </div>
                            </div>

                            <!-- Password Requirements -->
                            <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Requisitos de contraseña:</p>
                                <ul class="text-xs text-gray-600 space-y-1">
                                    <li class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-2 text-xs"></i>
                                        Mínimo 8 caracteres
                                    </li>
                                    <li class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-2 text-xs"></i>
                                        Al menos una mayúscula y una minúscula
                                    </li>
                                    <li class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-2 text-xs"></i>
                                        Al menos un número
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button type="submit"
                                        :disabled="!newPassword || !confirmPassword || newPassword !== confirmPassword"
                                        :class="!newPassword || !confirmPassword || newPassword !== confirmPassword ? 'opacity-50 cursor-not-allowed' : ''"
                                        class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors shadow-md">
                                    <i class="fas fa-shield-alt mr-1.5 text-xs"></i>
                                    Actualizar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Stats & Tips -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Security Score -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-xs font-medium text-green-700">Puntuación de Seguridad</p>
                                    <p class="text-2xl font-bold text-green-900">85/100</p>
                                </div>
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-shield-alt text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="w-full bg-green-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: 85%"></div>
                            </div>
                            <p class="text-xs text-green-700 mt-2">Tu cuenta está bien protegida</p>
                        </div>

                        <!-- Last Password Change -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-xs font-medium text-blue-700">Último Cambio</p>
                                    <p class="text-lg font-bold text-blue-900">Hace 45 días</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clock text-white text-xl"></i>
                                </div>
                            </div>
                            <p class="text-xs text-blue-700">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Se recomienda cambiar cada 90 días
                            </p>
                        </div>
                    </div>

                    <!-- Security Tips -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-lightbulb text-white"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-purple-900 mb-2">Consejos de Seguridad</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-purple-800">
                                    <div class="flex items-start">
                                        <i class="fas fa-check-circle text-purple-600 mr-2 mt-0.5"></i>
                                        <span>Usa contraseñas únicas para cada cuenta</span>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-check-circle text-purple-600 mr-2 mt-0.5"></i>
                                        <span>Cambia tu contraseña regularmente</span>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-check-circle text-purple-600 mr-2 mt-0.5"></i>
                                        <span>No compartas tu contraseña con nadie</span>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-check-circle text-purple-600 mr-2 mt-0.5"></i>
                                        <span>Cierra sesión en dispositivos públicos</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Settings -->
                <div x-show="activeTab === 'notifications'" x-transition class="space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-yellow-50 to-transparent">
                            <h3 class="text-lg font-semibold text-gray-900">Preferencias de Notificación</h3>
                            <p class="text-xs text-gray-500 mt-1">Controla cómo y cuándo recibes notificaciones</p>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Email Notifications -->
                                <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-envelope text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-blue-900">Notificaciones por Email</h4>
                                                <p class="text-xs text-blue-700 mt-1">Recibir actualizaciones importantes por correo</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" checked class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Push Notifications -->
                                <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-bell text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-purple-900">Notificaciones Push</h4>
                                                <p class="text-xs text-purple-700 mt-1">Recibir notificaciones en el navegador</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Weekly Summary -->
                                <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-calendar-week text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-green-900">Resumen Semanal</h4>
                                                <p class="text-xs text-green-700 mt-1">Recibir resumen de actividad semanal</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" checked class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- New Requests -->
                                <div class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-xl">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-file-alt text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-orange-900">Solicitudes Nuevas</h4>
                                                <p class="text-xs text-orange-700 mt-1">Notificar cuando hay solicitudes pendientes</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" checked class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-orange-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500">Notificaciones Hoy</p>
                                    <p class="text-xl font-bold text-gray-900">12</p>
                                </div>
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-bell text-blue-600"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500">No Leídas</p>
                                    <p class="text-xl font-bold text-gray-900">5</p>
                                </div>
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-envelope-open text-orange-600"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500">Esta Semana</p>
                                    <p class="text-xl font-bold text-gray-900">47</p>
                                </div>
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-chart-line text-green-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div x-show="activeTab === 'preferences'" x-transition class="space-y-4">
                    <!-- System Preferences -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-transparent">
                            <h3 class="text-lg font-semibold text-gray-900">Preferencias del Sistema</h3>
                            <p class="text-xs text-gray-500 mt-1">Personaliza tu experiencia en la plataforma</p>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Language -->
                                <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl">
                                    <div class="flex items-start space-x-3 mb-3">
                                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-language text-white"></i>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-sm font-semibold text-blue-900 mb-1">Idioma</label>
                                            <p class="text-xs text-blue-700 mb-2">Selecciona tu idioma preferido</p>
                                            <select class="w-full px-3 py-2 text-sm border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 bg-white">
                                                <option value="es" selected>🇪🇸 Español</option>
                                                <option value="en">🇺🇸 English</option>
                                                <option value="pt">🇧🇷 Português</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timezone -->
                                <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl">
                                    <div class="flex items-start space-x-3 mb-3">
                                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-globe-americas text-white"></i>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-sm font-semibold text-green-900 mb-1">Zona Horaria</label>
                                            <p class="text-xs text-green-700 mb-2">Ajusta tu zona horaria</p>
                                            <select class="w-full px-3 py-2 text-sm border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/50 bg-white">
                                                <option value="-5" selected>Colombia (UTC-5)</option>
                                                <option value="-6">México (UTC-6)</option>
                                                <option value="-3">Argentina (UTC-3)</option>
                                                <option value="0">UTC</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date Format -->
                                <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl">
                                    <div class="flex items-start space-x-3 mb-3">
                                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-calendar-alt text-white"></i>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-sm font-semibold text-purple-900 mb-1">Formato de Fecha</label>
                                            <p class="text-xs text-purple-700 mb-2">Cómo se muestran las fechas</p>
                                            <select class="w-full px-3 py-2 text-sm border border-purple-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500/50 bg-white">
                                                <option value="dd/mm/yyyy" selected>DD/MM/YYYY</option>
                                                <option value="mm/dd/yyyy">MM/DD/YYYY</option>
                                                <option value="yyyy-mm-dd">YYYY-MM-DD</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Theme (Future feature) -->
                                <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-xl">
                                    <div class="flex items-start space-x-3 mb-3">
                                        <div class="w-10 h-10 bg-gray-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-palette text-white"></i>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-sm font-semibold text-gray-900 mb-1">Tema</label>
                                            <p class="text-xs text-gray-700 mb-2">Apariencia de la interfaz</p>
                                            <select class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500/50 bg-white">
                                                <option value="light" selected>☀️ Claro</option>
                                                <option value="dark">🌙 Oscuro</option>
                                                <option value="auto">🔄 Automático</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Preferences -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">Preferencias de Visualización</h3>
                            <p class="text-xs text-gray-500 mt-1">Personaliza cómo ves la información</p>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Mostrar Avatares</p>
                                        <p class="text-xs text-gray-500">En listas y tablas</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Animaciones</p>
                                        <p class="text-xs text-gray-500">Efectos de transición</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Sidebar Compacto</p>
                                        <p class="text-xs text-gray-500">Menú lateral reducido</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Tooltips</p>
                                        <p class="text-xs text-gray-500">Mostrar ayuda al pasar</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div x-show="activeTab === 'security'" x-transition class="bg-red-50 border border-red-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-red-200">
                        <h3 class="text-xl font-semibold text-red-900">Zona de Peligro</h3>
                        <p class="text-sm text-red-700 mt-1">Acciones irreversibles</p>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-red-900 mb-1">Eliminar Cuenta</p>
                                <p class="text-sm text-red-700">Una vez eliminada, no podrás recuperar tu cuenta ni tus datos.</p>
                            </div>
                            <button @click="confirmDelete = true"
                                    class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors">
                                Eliminar Cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Update Modal -->
    <div x-show="showAvatarModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.away="showAvatarModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-white bg-opacity-80 backdrop-blur-sm transition-opacity"></div>

            <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-elegant-lg border border-gray-200"
                 @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Actualizar Avatar</h3>
                    <button @click="showAvatarModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_avatar">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">URL de la Imagen</label>
                        <input type="url"
                               name="avatar_url"
                               placeholder="https://ejemplo.com/imagen.jpg"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <p class="text-xs text-gray-500 mt-2">Ingresa la URL de tu imagen de perfil</p>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button"
                                @click="showAvatarModal = false"
                                class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="confirmDelete"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.away="confirmDelete = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-white bg-opacity-80 backdrop-blur-sm transition-opacity"></div>

            <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-elegant-lg border border-gray-200"
                 @click.stop>
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">¿Eliminar tu cuenta?</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        Esta acción no se puede deshacer. Todos tus datos serán eliminados permanentemente.
                    </p>

                    <div class="flex space-x-3">
                        <button @click="confirmDelete = false"
                                class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors">
                            Sí, Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function configPage() {
    return {
        activeTab: 'profile',
        showAvatarModal: false,
        confirmDelete: false
    }
}

function passwordForm() {
    return {
        showCurrentPassword: false,
        showNewPassword: false,
        showConfirmPassword: false,
        newPassword: '',
        confirmPassword: '',
        passwordStrength: 0,
        passwordStrengthText: '',
        passwordStrengthColor: '',

        validatePasswordStrength() {
            const password = this.newPassword;
            let strength = 0;

            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 10;
            if (/[a-z]/.test(password)) strength += 15;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 20;

            this.passwordStrength = Math.min(strength, 100);

            if (this.passwordStrength < 40) {
                this.passwordStrengthText = 'Débil';
                this.passwordStrengthColor = 'text-red-500';
            } else if (this.passwordStrength < 70) {
                this.passwordStrengthText = 'Media';
                this.passwordStrengthColor = 'text-yellow-500';
            } else {
                this.passwordStrengthText = 'Fuerte';
                this.passwordStrengthColor = 'text-green-500';
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }

.password-strength-weak { background-color: #EF4444; }
.password-strength-medium { background-color: #F59E0B; }
.password-strength-strong { background-color: #10B981; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
