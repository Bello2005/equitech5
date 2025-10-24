<?php
require_once __DIR__ . '/../config/session.php';

// Requerir autenticación
requireLogin();

// Obtener datos del usuario actual
$usuario = getCurrentUser();

// Si no tiene rol, asignar empleado por defecto
if (empty($usuario['rol'])) {
    $_SESSION['user_role'] = 'empleado';
    $usuario['rol'] = 'empleado';
}

// Si no hay avatar, usar uno por defecto
if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://ui-avatars.com/api/?name+=' . urlencode($usuario['nombre']) . '&background=0B8A3A&color=fff&size=128';
}

// Obtener iniciales del nombre
$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');
$primer_nombre = $nombre_partes[0];

// Detectar género por nombre (solo para íconos)
$nombres_mujer = ['maria', 'ana', 'laura', 'mayra', 'sandra', 'patricia', 'carolina', 'carmen', 'diana', 'natalia', 'juliana', 'paula', 'andrea', 'valentina', 'camila', 'sofia', 'isabella', 'mariana', 'daniela', 'valeria'];
$primer_nombre_lower = strtolower($primer_nombre);
$es_mujer = in_array($primer_nombre_lower, $nombres_mujer);

$page_title = 'Asistente Virtual - ComfaChoco';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        [x-cloak] { display: none !important; }

        body {
            background: #f8faf9;
        }

        /* Glassmorphism Premium */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(11, 138, 58, 0.1);
        }

        /* Scroll elegante */
        .chat-scroll::-webkit-scrollbar {
            width: 10px;
        }

        .chat-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-scroll::-webkit-scrollbar-thumb {
            background: rgba(11, 138, 58, 0.2);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .chat-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(11, 138, 58, 0.3);
            background-clip: padding-box;
        }

        /* Animaciones suaves y elegantes */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 0.4s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .fade-scale {
            animation: fadeInScale 0.3s ease-out;
        }

        /* Typing indicator premium */
        .typing-dot {
            animation: typingBounce 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) {
            animation-delay: 0s;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typingBounce {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-12px);
            }
        }

        /* Botón pulse sutil */
        @keyframes pulse-soft {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(11, 138, 58, 0.4);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(11, 138, 58, 0);
            }
        }

        .pulse-ring {
            animation: pulse-soft 2s infinite;
        }

        /* Hover effects premium */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(11, 138, 58, 0.15);
        }

        .card-hover:active {
            transform: translateY(-2px);
        }

        /* Mensajes con elevación */
        .message-bot {
            animation: slideUp 0.4s ease-out;
        }

        .message-user {
            animation: slideUp 0.4s ease-out;
        }

        /* Focus state premium */
        .input-focus:focus {
            outline: none;
            border-color: #0B8A3A;
            box-shadow: 0 0 0 4px rgba(11, 138, 58, 0.1);
        }

        /* Gradiente sutil de fondo */
        .bg-pattern {
            background-image:
                radial-gradient(at 40% 20%, rgba(11, 138, 58, 0.03) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(11, 138, 58, 0.02) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(11, 138, 58, 0.02) 0px, transparent 50%);
        }

        /* Botón premium */
        .btn-primary {
            background: #0B8A3A;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary:hover:not(:disabled) {
            background: #096B2E;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(11, 138, 58, 0.3);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Badge premium */
        .status-badge {
            animation: pulse-soft 2s infinite;
        }

        /* Texto con mejor legibilidad */
        .text-readable {
            line-height: 1.7;
            letter-spacing: 0.01em;
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0B8A3A',
                        'primary-dark': '#096B2E',
                        'primary-light': '#0ea94e',
                    },
                    fontSize: {
                        'xxl': '1.75rem',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-pattern min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4 md:p-8" x-data="premiumChat()" x-init="window.premiumChatInstance = $data">
        <!-- Main Container -->
        <div class="w-full max-w-5xl h-[85vh] md:h-[90vh] glass-card rounded-3xl shadow-2xl overflow-hidden flex flex-col fade-scale">

            <!-- Header Premium -->
            <div class="relative bg-white border-b border-gray-100 px-6 md:px-8 py-5">
                <div class="flex items-center justify-between">
                    <!-- Logo y título -->
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-lg border-2 border-gray-100 dark-mode-logo">
                                <img src="../assets/images/logo-comfachoco-no-lema.svg" alt="ComfaChocó Logo" class="w-10 h-10 object-contain">
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 rounded-full border-3 border-white status-badge"></div>
                        </div>
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Asistente Permisos ComfaChocó</h1>
                            <p class="text-sm text-gray-500 mt-0.5 flex items-center">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                Siempre disponible para ayudarte.
                            </p>
                        </div>
                    </div>

                    <!-- User info -->
                    <div class="hidden md:flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($primer_nombre) ?></p>
                            <p class="text-xs text-gray-500">Empleado</p>
                        </div>
                        <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center text-white text-xl shadow-md ring-2 ring-primary/20">
                            <?php if ($es_mujer): ?>
                                <i class="fas fa-user-circle"></i>
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <a href="../config/logout.php"
                           class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 transition-all duration-300"
                           title="Cerrar sesión">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>

                    <!-- Mobile logout -->
                    <a href="../config/logout.php" class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-600">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 overflow-y-auto chat-scroll bg-transparent" x-ref="chatContainer">
                <div class="max-w-7xl mx-auto px-4 md:px-8 py-6">

                    <!-- Welcome Screen -->
                    <div x-show="messages.length === 0" x-cloak class="slide-up">
                        <!-- Saludo -->
                        <div class="max-w-4xl mx-auto mb-10">
                            <div class="flex items-start space-x-4">
                                <div class="w-20 h-20 bg-primary rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                                    <span class="text-white text-3xl font-bold">
                                        <?= htmlspecialchars($usuario['iniciales']) ?>
                                    </span>
                                </div>
                                <div>
                                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                                        ¡Hola <?= htmlspecialchars($primer_nombre) ?>! 👋
                                    </h2>
                                    <p class="text-lg md:text-xl text-gray-600 mb-2">
                                        Soy el asistente de ComfaChocó. Estoy aquí para ayudarte con tus permisos y vacaciones de forma sencilla.
                                    </p>
                                    <p class="text-lg text-gray-700">
                                        Tienes <span class="text-primary font-semibold text-2xl">15 días</span> de vacaciones disponibles.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl mx-auto">
                            <button @click="quickMessage('Quiero solicitar vacaciones')"
                                    class="card-hover bg-white border-2 border-primary/20 rounded-xl p-5 text-left shadow-lg hover:border-primary">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="fas fa-umbrella-beach text-white text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-base mb-1">Solicitar Vacaciones</h3>
                                        <p class="text-gray-600 text-sm">Gestiona tus días de descanso</p>
                                    </div>
                                </div>
                            </button>

                            <button @click="quickMessage('¿Cuántos días de permiso tengo disponibles?')"
                                    class="card-hover bg-white border-2 border-primary/20 rounded-xl p-5 text-left shadow-lg hover:border-primary">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 bg-green-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="fas fa-calendar-check text-white text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-base mb-1">Ver Mis Días Disponibles</h3>
                                        <p class="text-gray-600 text-sm">Consulta tu saldo completo</p>
                                    </div>
                                </div>
                            </button>

                            <button @click="quickMessage('Estado de mis solicitudes')"
                                    class="card-hover bg-white border-2 border-primary/20 rounded-xl p-5 text-left shadow-lg hover:border-primary">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 bg-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="fas fa-list-check text-white text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-base mb-1">Revisar Mis Solicitudes</h3>
                                        <p class="text-gray-600 text-sm">Estado de tus permisos</p>
                                    </div>
                                </div>
                            </button>

                            <button @click="quickMessage('Quiero conocer las políticas de permisos')"
                                    class="card-hover bg-white border-2 border-primary/20 rounded-xl p-5 text-left shadow-lg hover:border-primary">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 bg-purple-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="fas fa-book text-white text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-base mb-1">Políticas y Reglas</h3>
                                        <p class="text-gray-600 text-sm">Normas sobre vacaciones</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div x-show="messages.length > 0">
                        <template x-for="(message, index) in messages" :key="index">
                            <div class="mb-8">
                                <!-- Bot Message -->
                                <template x-if="message.type === 'bot'">
                                    <div class="flex items-start space-x-4 message-bot">
                                        <div class="flex-shrink-0">
                                            <div class="w-11 h-11 bg-primary rounded-xl flex items-center justify-center shadow-md">
                                                <i class="fas fa-robot text-white text-lg"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="bg-white rounded-2xl rounded-tl-md px-6 py-4 shadow-md border border-gray-100">
                                                <div class="text-gray-800 text-base text-readable leading-relaxed" x-html="message.text"></div>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-2 ml-4" x-text="message.time"></p>
                                        </div>
                                    </div>
                                </template>

                                <!-- User Message -->
                                <template x-if="message.type === 'user'">
                                    <div class="flex items-start justify-end space-x-4 message-user">
                                        <div class="flex-1 flex justify-end">
                                            <div class="max-w-xl">
                                                <div class="bg-primary rounded-2xl rounded-tr-md px-6 py-4 shadow-lg">
                                                    <p class="text-white text-base leading-relaxed" x-text="message.text"></p>
                                                </div>
                                                <p class="text-xs text-gray-400 mt-2 mr-4 text-right" x-text="message.time"></p>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="w-11 h-11 bg-primary rounded-xl flex items-center justify-center text-white shadow-md ring-2 ring-primary/20">
                                                <?php if ($es_mujer): ?>
                                                    <i class="fas fa-user-circle text-xl"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-user text-xl"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Typing Indicator -->
                        <div x-show="isTyping" x-cloak class="flex items-start space-x-4 mb-8 message-bot">
                            <div class="flex-shrink-0">
                                <div class="w-11 h-11 bg-primary rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-robot text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="bg-white rounded-2xl rounded-tl-md px-6 py-4 shadow-md border border-gray-100">
                                <div class="flex space-x-2">
                                    <div class="w-3 h-3 bg-primary rounded-full typing-dot"></div>
                                    <div class="w-3 h-3 bg-primary rounded-full typing-dot"></div>
                                    <div class="w-3 h-3 bg-primary rounded-full typing-dot"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area Premium -->
            <div class="bg-white border-t border-gray-100 px-6 md:px-8 py-6">
                <form @submit.prevent="sendMessage()">
                    <div class="flex items-end space-x-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Escribe tu consulta:</label>
                            <div class="relative">
                                <input type="text"
                                       x-model="currentMessage"
                                       placeholder="Ejemplo: Quiero solicitar vacaciones del 15 al 20 de diciembre..."
                                       class="input-focus w-full px-5 py-4 text-base border-2 border-gray-200 rounded-xl bg-gray-50 focus:bg-white transition-all duration-300"
                                       autofocus>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-keyboard text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                                :disabled="!currentMessage.trim()"
                                class="btn-primary px-8 py-4 text-white font-semibold rounded-xl flex items-center space-x-3 shadow-lg text-base">
                            <span>Enviar</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mt-3 text-center">
                        💡 <strong>Consejo:</strong> Escribe con tus propias palabras, te entenderé perfectamente
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        function premiumChat() {
            return {
                messages: [],
                currentMessage: '',
                isTyping: false,

                init() {
                    this.scrollToBottom();

                    // Event delegation para los botones de opciones
                    document.addEventListener('click', (e) => {
                        const option = e.target.closest('.permission-option');
                        if (option) {
                            e.preventDefault();
                            e.stopPropagation();
                            const optionNumber = option.getAttribute('data-option');
                            if (optionNumber) {
                                this.quickMessage(optionNumber);
                            }
                        }
                    });
                },

                sendMessage() {
                    if (!this.currentMessage.trim()) return;

                    const now = new Date();
                    const time = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });

                    this.messages.push({
                        type: 'user',
                        text: this.currentMessage,
                        time: time
                    });

                    const userMessage = this.currentMessage.toLowerCase();
                    this.currentMessage = '';
                    this.isTyping = true;
                    this.scrollToBottom();

                    setTimeout(() => {
                        this.isTyping = false;
                        this.messages.push({
                            type: 'bot',
                            text: this.getBotResponse(userMessage),
                            time: time
                        });
                        this.scrollToBottom();
                    }, 1000 + Math.random() * 800);
                },

                quickMessage(message) {
                    this.currentMessage = message;
                    this.sendMessage();
                },

                getBotResponse(message) {
                    if ((message.includes('solicitar') || message.includes('pedir') || message.includes('quiero')) &&
                        (message.includes('permiso') || message.includes('vacacion'))) {
                        return `
                            <p class="text-lg font-semibold mb-4">¡Perfecto! Te ayudaré a solicitar tu permiso 😊</p>
                            <p class="mb-4">Por favor, selecciona el tipo de permiso que necesitas:</p>
                            <div class="bg-primary/5 rounded-xl p-5 space-y-3 my-4">
                                <div data-option="1" class="permission-option w-full flex items-center space-x-3 p-3 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform">1</span>
                                    <span class="font-medium text-lg text-left flex-1">Vacaciones</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <div data-option="2" class="permission-option w-full flex items-center space-x-3 p-3 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform">2</span>
                                    <span class="font-medium text-lg text-left flex-1">Permiso médico</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <div data-option="3" class="permission-option w-full flex items-center space-x-3 p-3 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform">3</span>
                                    <span class="font-medium text-lg text-left flex-1">Permiso personal</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <div data-option="4" class="permission-option w-full flex items-center space-x-3 p-3 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform">4</span>
                                    <span class="font-medium text-lg text-left flex-1">Calamidad doméstica</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 text-center">
                                <i class="fas fa-hand-pointer text-primary mr-2"></i>
                                Haz clic en la opción que necesitas o escribe el número
                            </p>
                        `;
                    }

                    if (message.includes('saldo') || message.includes('disponible') || message.includes('días') ||
                        message.includes('dias') || message.includes('cuántos') || message.includes('cuantos')) {
                        return `
                            <p class="text-lg font-semibold mb-4">📊 Aquí está tu saldo actual de permisos:</p>
                            <div class="space-y-4 my-4">
                                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-5 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-umbrella-beach text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Vacaciones</p>
                                                <p class="text-3xl font-bold text-blue-600">12 días</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-5 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-heartbeat text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Permiso médico</p>
                                                <p class="text-3xl font-bold text-green-600">5 días</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-5 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-clock text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Permiso personal</p>
                                                <p class="text-3xl font-bold text-purple-600">3 días</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-600 bg-gray-50 rounded-lg p-3 mt-4">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Estos días están disponibles para usar durante el año 2024.
                            </p>
                            ${this.getActionButtons()}
                        `;
                    }

                    if (message.includes('solicitud') || message.includes('estado') || message.includes('revisar')) {
                        return `
                            <p class="text-lg font-semibold mb-4">📋 Estado de tus solicitudes de permisos:</p>
                            <div class="space-y-4 my-4">
                                <div class="bg-green-50 border border-green-200 rounded-xl p-5 shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-lg font-bold text-green-900">✓ Solicitudes Aprobadas</span>
                                        <span class="text-3xl font-bold text-green-600">3</span>
                                    </div>
                                    <p class="text-sm text-green-700">Última aprobada: Vacaciones del 15-20 Diciembre 2023</p>
                                </div>
                                <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-lg font-bold text-orange-900">⏳ En Revisión</span>
                                        <span class="text-3xl font-bold text-orange-600">1</span>
                                    </div>
                                    <p class="text-sm text-orange-700">Permiso médico por 2 días - En proceso de aprobación</p>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-lg font-bold text-gray-700">✗ Rechazadas</span>
                                        <span class="text-3xl font-bold text-gray-600">0</span>
                                    </div>
                                    <p class="text-sm text-gray-600">¡Excelente! No tienes solicitudes rechazadas</p>
                                </div>
                            </div>
                            <p class="text-gray-600 bg-blue-50 rounded-lg p-3 mt-4">
                                <i class="fas fa-bell text-blue-500 mr-2"></i>
                                Recibirás una notificación por correo cuando tu solicitud sea procesada.
                            </p>
                        `;
                    }

                    if (message.includes('política') || message.includes('politica') || message.includes('regla') || message.includes('norma')) {
                        return `
                            <p class="text-lg font-semibold mb-4">📖 Políticas de Permisos de ComfaChoco:</p>
                            <div class="bg-primary/5 rounded-xl p-5 space-y-4 my-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-xl mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Vacaciones:</p>
                                        <p class="text-gray-700">Deben solicitarse con 15 días de anticipación mínimo</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-xl mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Permisos Médicos:</p>
                                        <p class="text-gray-700">Requieren presentar certificado médico original</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-xl mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Permisos Personales:</p>
                                        <p class="text-gray-700">Máximo 3 días al año, solicitar con 3 días de anticipación</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-xl mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Tiempo de Respuesta:</p>
                                        <p class="text-gray-700">Todas las solicitudes se revisan en máximo 48 horas</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-600">¿Necesitas más información sobre alguna política en específico?</p>
                        `;
                    }

                    if (message.includes('1') || message.includes('vacaciones')) {
                        return `
                            <p class="text-lg font-semibold mb-4">🏖️ Solicitud de Vacaciones</p>
                            <p class="mb-3">Actualmente tienes <strong class="text-primary text-xl">12 días disponibles</strong> para tomar.</p>
                            <div class="bg-blue-50 rounded-xl p-5 my-4">
                                <p class="font-semibold mb-3">Para continuar, necesito que me indiques:</p>
                                <ul class="space-y-2">
                                    <li class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-blue-600"></i>
                                        <span><strong>Fecha de inicio:</strong> Día que empieza tus vacaciones</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-blue-600"></i>
                                        <span><strong>Fecha de fin:</strong> Último día de vacaciones</span>
                                    </li>
                                </ul>
                            </div>
                            <p class="text-gray-600 bg-gray-50 rounded-lg p-3">
                                <strong>Ejemplo:</strong> "Quiero vacaciones del 15 al 20 de diciembre"
                            </p>
                        `;
                    }

                    if (message.includes('2') || message.includes('médico') || message.includes('medico')) {
                        return `
                            <p class="text-lg font-semibold mb-4">🏥 Permiso Médico</p>
                            <p class="mb-3">Tienes <strong class="text-primary text-xl">5 días disponibles</strong> para permisos médicos.</p>
                            <div class="bg-green-50 rounded-xl p-5 my-4">
                                <p class="font-semibold mb-3">Documentos necesarios:</p>
                                <ul class="space-y-2">
                                    <li class="flex items-center space-x-2">
                                        <i class="fas fa-file-medical text-green-600"></i>
                                        <span>Certificado médico original</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <i class="fas fa-calendar-day text-green-600"></i>
                                        <span>Fechas del permiso solicitado</span>
                                    </li>
                                </ul>
                            </div>
                            <p class="text-gray-600">¿En qué fechas necesitas el permiso médico?</p>
                        `;
                    }

                    if (message.includes('3') || message.includes('personal')) {
                        return `
                            <p class="text-lg font-semibold mb-4">⏰ Permiso Personal</p>
                            <p class="mb-3">Tienes <strong class="text-primary text-xl">3 días disponibles</strong> de permiso personal este año.</p>
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Importante:</strong> Los permisos personales deben solicitarse con al menos 3 días de anticipación
                                </p>
                            </div>
                            <p class="text-gray-600">Por favor indícame la fecha que necesitas el permiso personal.</p>
                        `;
                    }

                    if (message.includes('4') || message.includes('calamidad')) {
                        return `
                            <p class="text-lg font-semibold mb-4">🚨 Permiso por Calamidad Doméstica</p>
                            <p class="mb-4">Este permiso es para situaciones urgentes e imprevistas que requieren tu atención inmediata.</p>
                            <div class="bg-red-50 rounded-xl p-5 my-4">
                                <p class="font-semibold mb-3">Por favor describe brevemente:</p>
                                <ul class="space-y-2">
                                    <li class="flex items-start space-x-2">
                                        <i class="fas fa-circle text-red-600 text-xs mt-2"></i>
                                        <span>La situación de emergencia</span>
                                    </li>
                                    <li class="flex items-start space-x-2">
                                        <i class="fas fa-circle text-red-600 text-xs mt-2"></i>
                                        <span>Las fechas que necesitas</span>
                                    </li>
                                </ul>
                            </div>
                        `;
                    }

                    if (message.includes('hola') || message.includes('buenos') || message.includes('buenas')) {
                        return `<p class="text-lg">¡Hola <strong><?= htmlspecialchars($primer_nombre) ?></strong>! 👋</p><p class="mt-3 text-gray-700">Soy tu asistente virtual de Recursos Humanos de ComfaChoco. Estoy aquí para ayudarte con todo lo relacionado a tus permisos y vacaciones.</p><p class="mt-3 text-gray-700">¿En qué puedo ayudarte hoy?</p>`;
                    }

                    if (message.includes('gracias')) {
                        return `<p class="text-lg">¡De nada, <strong><?= htmlspecialchars($primer_nombre) ?></strong>! 😊</p><p class="mt-3 text-gray-700">Es un placer ayudarte. Estoy disponible siempre que me necesites.</p>`;
                    }

                    return `
                        <p class="mb-4">Puedo ayudarte con las siguientes opciones:</p>
                        <div class="grid grid-cols-2 gap-3 my-4">
                            <div class="bg-primary/10 rounded-lg p-4 text-center">
                                <i class="fas fa-umbrella-beach text-primary text-2xl mb-2"></i>
                                <p class="font-medium">Solicitar permisos</p>
                            </div>
                            <div class="bg-primary/10 rounded-lg p-4 text-center">
                                <i class="fas fa-calendar-check text-primary text-2xl mb-2"></i>
                                <p class="font-medium">Ver días disponibles</p>
                            </div>
                            <div class="bg-primary/10 rounded-lg p-4 text-center">
                                <i class="fas fa-list-check text-primary text-2xl mb-2"></i>
                                <p class="font-medium">Estado de solicitudes</p>
                            </div>
                            <div class="bg-primary/10 rounded-lg p-4 text-center">
                                <i class="fas fa-book text-primary text-2xl mb-2"></i>
                                <p class="font-medium">Ver políticas</p>
                            </div>
                        </div>
                        <p class="text-gray-600">¿Qué necesitas hacer hoy?</p>
                    `;
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.chatContainer;
                        if (container) {
                            container.scrollTo({
                                top: container.scrollHeight,
                                behavior: 'smooth'
                            });
                        }
                    });
                }
            }
        }
    </script>

    <!-- Botón Flotante de Accesibilidad -->
    <div x-data="{
        open: false,
        fontSize: 'normal',
        highContrast: false,
        darkMode: false,
        screenReader: false,

        init() {
            // Guardar referencia global para el lector
            window.accessibilityPanel = this;
        },

        increaseFontSize() {
            document.documentElement.style.fontSize = '120%';
            this.fontSize = 'large';
            if(this.screenReader) {
                this.speak('Tamaño de texto aumentado');
            }
        },

        decreaseFontSize() {
            document.documentElement.style.fontSize = '80%';
            this.fontSize = 'small';
            if(this.screenReader) {
                this.speak('Tamaño de texto reducido');
            }
        },

        resetFontSize() {
            document.documentElement.style.fontSize = '100%';
            this.fontSize = 'normal';
            if(this.screenReader) {
                this.speak('Tamaño de texto normal');
            }
        },

        toggleContrast() {
            this.highContrast = !this.highContrast;
            if(this.highContrast) {
                document.body.classList.add('high-contrast');
                if(this.screenReader) {
                    this.speak('Alto contraste activado');
                }
            } else {
                document.body.classList.remove('high-contrast');
                if(this.screenReader) {
                    this.speak('Alto contraste desactivado');
                }
            }
        },

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            if(this.darkMode) {
                document.documentElement.classList.add('dark-mode');
                if(this.screenReader) {
                    this.speak('Modo oscuro activado');
                }
            } else {
                document.documentElement.classList.remove('dark-mode');
                if(this.screenReader) {
                    this.speak('Modo oscuro desactivado');
                }
            }
        },

        toggleScreenReader() {
            this.screenReader = !this.screenReader;
            if(this.screenReader) {
                // Solo este debe hablar siempre para confirmar activación
                if('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    const utterance = new SpeechSynthesisUtterance('Lector de pantalla activado. Pasa el mouse sobre los elementos para escuchar su descripción');
                    utterance.lang = 'es-ES';
                    utterance.rate = 0.9;
                    utterance.pitch = 1;
                    window.speechSynthesis.speak(utterance);
                }
                this.enableScreenReader();
            } else {
                // Anunciar desactivación y cancelar
                if('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    const utterance = new SpeechSynthesisUtterance('Lector de pantalla desactivado');
                    utterance.lang = 'es-ES';
                    utterance.rate = 0.9;
                    utterance.pitch = 1;
                    window.speechSynthesis.speak(utterance);
                }
                this.disableScreenReader();
            }
        },

        // Hablar solo si el lector está activado
        speak(text) {
            if(this.screenReader && 'speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'es-ES';
                utterance.rate = 0.9;
                utterance.pitch = 1;
                window.speechSynthesis.speak(utterance);
            }
        },

        enableScreenReader() {
            // Agregar eventos de hover para leer elementos
            setTimeout(() => {
                document.querySelectorAll('button, a, input, .permission-option').forEach(el => {
                    el.addEventListener('mouseenter', (e) => {
                        const elem = e.target;
                        let text = '';

                        if(elem.getAttribute('aria-label')) {
                            text = elem.getAttribute('aria-label');
                        } else if(elem.getAttribute('title')) {
                            text = elem.getAttribute('title');
                        } else if(elem.getAttribute('placeholder')) {
                            text = 'Campo de texto: ' + elem.getAttribute('placeholder');
                        } else {
                            text = elem.innerText || elem.textContent;
                        }

                        if(text.trim() && window.accessibilityPanel) {
                            window.accessibilityPanel.speak(text.trim().substring(0, 200));
                        }
                    });
                });
            }, 100);
        },

        disableScreenReader() {
            if('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        }
    }" class="fixed bottom-6 right-6 z-50">

        <!-- Panel de Opciones de Accesibilidad -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             @click.away="open = false"
             class="absolute bottom-20 right-0 w-72 bg-white rounded-2xl shadow-2xl border-2 border-primary/20 p-5 mb-2">

            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-universal-access text-primary mr-2"></i>
                Accesibilidad
            </h3>

            <!-- Tamaño de Texto -->
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">Tamaño de texto</p>
                <div class="flex space-x-2">
                    <button @click="decreaseFontSize()"
                            class="flex-1 px-4 py-2 rounded-lg border-2 hover:border-primary hover:bg-primary/5 transition-all"
                            :class="fontSize === 'small' ? 'border-primary bg-primary/10' : 'border-gray-200'">
                        <i class="fas fa-text-height text-sm"></i>
                        <span class="block text-xs mt-1">Pequeño</span>
                    </button>
                    <button @click="resetFontSize()"
                            class="flex-1 px-4 py-2 rounded-lg border-2 hover:border-primary hover:bg-primary/5 transition-all"
                            :class="fontSize === 'normal' ? 'border-primary bg-primary/10' : 'border-gray-200'">
                        <i class="fas fa-text-height text-base"></i>
                        <span class="block text-xs mt-1">Normal</span>
                    </button>
                    <button @click="increaseFontSize()"
                            class="flex-1 px-4 py-2 rounded-lg border-2 hover:border-primary hover:bg-primary/5 transition-all"
                            :class="fontSize === 'large' ? 'border-primary bg-primary/10' : 'border-gray-200'">
                        <i class="fas fa-text-height text-lg"></i>
                        <span class="block text-xs mt-1">Grande</span>
                    </button>
                </div>
            </div>

            <!-- Modo Oscuro -->
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">Tema</p>
                <button @click="toggleDarkMode()"
                        class="w-full px-4 py-3 rounded-lg border-2 hover:border-primary transition-all flex items-center justify-between"
                        :class="darkMode ? 'border-primary bg-primary/10' : 'border-gray-200'"
                        aria-label="Activar o desactivar modo oscuro">
                    <span class="flex items-center">
                        <i class="fas fa-moon mr-2"></i>
                        Modo oscuro
                    </span>
                    <i class="fas" :class="darkMode ? 'fa-toggle-on text-primary text-2xl' : 'fa-toggle-off text-gray-400 text-2xl'"></i>
                </button>
            </div>

            <!-- Alto Contraste -->
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">Contraste</p>
                <button @click="toggleContrast()"
                        class="w-full px-4 py-3 rounded-lg border-2 hover:border-primary transition-all flex items-center justify-between"
                        :class="highContrast ? 'border-primary bg-primary/10' : 'border-gray-200'"
                        aria-label="Activar o desactivar alto contraste">
                    <span class="flex items-center">
                        <i class="fas fa-adjust mr-2"></i>
                        Alto contraste
                    </span>
                    <i class="fas" :class="highContrast ? 'fa-toggle-on text-primary text-2xl' : 'fa-toggle-off text-gray-400 text-2xl'"></i>
                </button>
            </div>

            <!-- Lector de Pantalla -->
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">Audio</p>
                <button @click="toggleScreenReader()"
                        class="w-full px-4 py-3 rounded-lg border-2 hover:border-primary transition-all flex items-center justify-between"
                        :class="screenReader ? 'border-primary bg-primary/10' : 'border-gray-200'"
                        aria-label="Activar o desactivar lector de pantalla">
                    <span class="flex items-center">
                        <i class="fas fa-volume-up mr-2"></i>
                        Lector de pantalla
                    </span>
                    <i class="fas" :class="screenReader ? 'fa-toggle-on text-primary text-2xl' : 'fa-toggle-off text-gray-400 text-2xl'"></i>
                </button>
                <p x-show="screenReader" class="text-xs text-primary mt-2 ml-1 animate-fadeIn">
                    <i class="fas fa-info-circle mr-1"></i>
                    Pasa el mouse sobre los elementos para escucharlos
                </p>
            </div>

            <!-- Atajos de Teclado -->
            <div class="pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-600">
                    <i class="fas fa-keyboard mr-1"></i>
                    <strong>Tip:</strong> Usa Tab para navegar entre botones
                </p>
            </div>
        </div>

        <!-- Botón Principal -->
        <button @click="open = !open"
                class="w-16 h-16 bg-primary hover:bg-primary/90 text-white rounded-full shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 group"
                :class="open ? 'rotate-90' : ''"
                aria-label="Opciones de accesibilidad"
                title="Accesibilidad">
            <i class="fas fa-universal-access text-2xl group-hover:scale-110 transition-transform"></i>
        </button>
    </div>

    <!-- Estilos para Alto Contraste y Modo Oscuro -->
    <style>
        /* Alto Contraste */
        body.high-contrast {
            filter: contrast(1.5) !important;
        }

        body.high-contrast * {
            text-shadow: none !important;
        }

        body.high-contrast .bg-gradient-to-br {
            filter: contrast(1.3);
        }

        /* Alto Contraste + Modo Oscuro */
        html.dark-mode body.high-contrast .text-gray-900 {
            color: #ffffff !important;
        }

        html.dark-mode body.high-contrast .text-gray-800 {
            color: #ffffff !important;
        }

        html.dark-mode body.high-contrast .text-gray-700 {
            color: #f0f0f0 !important;
        }

        html.dark-mode body.high-contrast .text-gray-600 {
            color: #e0e0e0 !important;
        }

        html.dark-mode body.high-contrast .text-gray-500 {
            color: #d0d0d0 !important;
        }

        html.dark-mode body.high-contrast .text-gray-400 {
            color: #c0c0c0 !important;
        }

        html.dark-mode body.high-contrast .bg-white {
            background: #000000 !important;
            border-color: #ffffff !important;
            border-width: 2px !important;
        }

        /* Modo Oscuro */
        html.dark-mode {
            background: #0a0a0a;
        }

        html.dark-mode body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
        }

        html.dark-mode .bg-pattern {
            background-color: #0a0a0a;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(11, 138, 58, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(11, 138, 58, 0.12) 0%, transparent 50%);
        }

        /* Contenedor principal */
        html.dark-mode .glass-card {
            background: rgba(15, 15, 15, 0.95) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
        }

        /* Header */
        html.dark-mode .bg-white {
            background: #0f0f0f !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Logo del asistente en modo oscuro */
        html.dark-mode .dark-mode-logo {
            background: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
        }

        /* Logo del asistente en alto contraste */
        body.high-contrast .dark-mode-logo {
            background: #ffffff !important;
            border-color: #000000 !important;
            border-width: 2px !important;
        }

        /* Logo del asistente en modo oscuro + alto contraste */
        html.dark-mode body.high-contrast .dark-mode-logo {
            background: #ffffff !important;
            border-color: #000000 !important;
            border-width: 2px !important;
        }

        /* Textos - mantener visibles */
        html.dark-mode .text-gray-900 {
            color: #ffffff !important;
        }

        html.dark-mode .text-gray-800 {
            color: #f0f0f0 !important;
        }

        html.dark-mode .text-gray-700 {
            color: #e0e0e0 !important;
        }

        html.dark-mode .text-gray-600 {
            color: #c0c0c0 !important;
        }

        html.dark-mode .text-gray-500 {
            color: #a0a0a0 !important;
        }

        html.dark-mode .text-gray-400 {
            color: #808080 !important;
        }

        /* Fondos de mensajes */
        html.dark-mode .message-bot .bg-white {
            background: #1a1a1a !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Input */
        html.dark-mode input {
            background: #1a1a1a !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        html.dark-mode input:focus {
            background: #242424 !important;
            border-color: #0B8A3A !important;
        }

        html.dark-mode input::placeholder {
            color: #808080 !important;
        }

        /* Botones de opciones */
        html.dark-mode .permission-option {
            background: #0f0f0f !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        html.dark-mode .permission-option:hover {
            background: rgba(11, 138, 58, 0.15) !important;
            border-color: #0B8A3A !important;
        }

        /* Tarjetas de saldo */
        html.dark-mode .bg-blue-50,
        html.dark-mode .bg-green-50,
        html.dark-mode .bg-purple-50,
        html.dark-mode .bg-orange-50 {
            background: #1a1a1a !important;
        }

        html.dark-mode .border-blue-200,
        html.dark-mode .border-green-200,
        html.dark-mode .border-purple-200,
        html.dark-mode .border-orange-200 {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Gradientes suaves en modo oscuro */
        html.dark-mode .bg-gradient-to-br {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%) !important;
        }

        /* Panel de accesibilidad en modo oscuro */
        html.dark-mode .fixed .bg-white {
            background: #0f0f0f !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        /* Texto del panel de accesibilidad en modo oscuro */
        html.dark-mode .fixed .bg-white .text-gray-900,
        html.dark-mode .fixed .bg-white .text-gray-700,
        html.dark-mode .fixed .bg-white .text-gray-600,
        html.dark-mode .fixed .bg-white .text-gray-500 {
            color: #ffffff !important;
        }

        html.dark-mode .fixed .bg-white p,
        html.dark-mode .fixed .bg-white span,
        html.dark-mode .fixed .bg-white h3 {
            color: #ffffff !important;
        }

        /* Panel de accesibilidad en modo oscuro + alto contraste */
        html.dark-mode body.high-contrast .fixed .bg-white {
            background: #000000 !important;
            border-color: #ffffff !important;
            border-width: 2px !important;
        }

        html.dark-mode body.high-contrast .fixed .bg-white * {
            color: #ffffff !important;
        }

        html.dark-mode body.high-contrast .fixed .bg-white .text-gray-900,
        html.dark-mode body.high-contrast .fixed .bg-white .text-gray-700,
        html.dark-mode body.high-contrast .fixed .bg-white .text-gray-600,
        html.dark-mode body.high-contrast .fixed .bg-white .text-gray-500 {
            color: #ffffff !important;
        }

        html.dark-mode .border-gray-200 {
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        html.dark-mode .bg-gray-50 {
            background: #1a1a1a !important;
        }

        /* Scrollbar en modo oscuro */
        html.dark-mode .chat-scroll::-webkit-scrollbar-track {
            background: #0f0f0f;
        }

        html.dark-mode .chat-scroll::-webkit-scrollbar-thumb {
            background: #0B8A3A;
        }

        /* Animación de typing indicator */
        html.dark-mode .typing-dot {
            background: #0B8A3A !important;
        }

        /* Transición suave */
        html.dark-mode * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
</body>
</html>
