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
    <div class="min-h-screen flex items-center justify-center p-1 sm:p-2 md:p-3 lg:p-4" x-data="premiumChat()" x-init="window.premiumChatInstance = $data">
        <!-- Main Container -->
        <div class="w-full max-w-xs sm:max-w-md md:max-w-xl lg:max-w-2xl xl:max-w-3xl h-[90vh] sm:h-[92vh] md:h-[93vh] glass-card rounded-lg sm:rounded-xl md:rounded-2xl shadow-2xl overflow-hidden flex flex-col fade-scale">

            <!-- Header Premium -->
            <div class="relative bg-white border-b border-gray-100 px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                <div class="flex items-center justify-between">
                    <!-- Logo y título -->
                    <div class="flex items-center space-x-2 sm:space-x-3 md:space-x-4">
                        <div class="relative">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg border-2 border-gray-100 dark-mode-logo">
                                <img src="../assets/images/logo-comfachoco-no-lema.svg" alt="ComfaChocó Logo" class="w-6 h-6 sm:w-8 sm:h-8 object-contain">
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-green-400 rounded-full border-2 border-white status-badge"></div>
                        </div>
                        <div>
                            <h1 class="text-sm sm:text-base md:text-lg font-bold text-gray-900">Asistente Permisos ComfaChocó</h1>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 flex items-center">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                <span class="hidden sm:inline">Disponible</span>
                                <span class="sm:hidden">✓</span>
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
                <div class="max-w-7xl mx-auto px-2 sm:px-3 md:px-4 py-2 sm:py-3 md:py-4">

                    <!-- Welcome Screen -->
                    <div class="slide-up">
                        <!-- Saludo -->
                        <div class="max-w-4xl mx-auto mb-4 sm:mb-6 md:mb-8">
                            <div class="flex items-start space-x-2 sm:space-x-3 md:space-x-4">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-white rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0 p-2">
                                        <img src="../assets/images/logo-comfachoco-no-lema.svg" alt="ComfaChoco" class="w-full h-full object-contain">
                                    </div>
                                    <div>
                                    <h2 class="text-sm sm:text-base md:text-lg font-bold text-gray-900 mb-1">
                                            ¡Hola <?= htmlspecialchars($primer_nombre) ?>! 👋
                                        <span class="text-primary font-semibold text-xs sm:text-sm block sm:inline sm:ml-2">Tienes 15 días disponibles.</span>
                                        </h2>
                                    <p class="text-xs sm:text-sm text-gray-600 mb-2">
                                            Soy el asistente de ComfaChoco. Estoy aquí para ayudarte con tus permisos y vacaciones de forma sencilla.
                                        </p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-3 max-w-3xl mx-auto">
                            <button @click="quickMessage('Quiero solicitar permisos')"
                                    class="card-hover bg-white border-2 border-primary/20 rounded-lg p-2 sm:p-3 text-left shadow-lg hover:border-primary">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="fas fa-clipboard-list text-white text-sm sm:text-base"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-xs sm:text-sm mb-0.5">Solicitar Permisos</h3>
                                        <p class="text-gray-600 text-xs">Gestiona tus permisos y vacaciones</p>
                                    </div>
                                </div>
                            </button>

                            <button @click="quickMessage('¿Cuántos días de permiso tengo disponibles?')"
                                    class="card-hover bg-white border-2 border-primary/20 rounded-lg p-2 sm:p-3 text-left shadow-lg hover:border-primary">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="fas fa-calendar-check text-white text-lg sm:text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-xs sm:text-sm mb-0.5">Ver Mis Días Disponibles</h3>
                                        <p class="text-gray-600 text-xs sm:text-sm">Consulta tu saldo completo</p>
                                    </div>
                                </div>
                            </button>

                            <button @click="quickMessage('Estado de mis solicitudes')"
                                    class="card-hover bg-white border-2 border-primary/20 rounded-lg p-2 sm:p-3 text-left shadow-lg hover:border-primary">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-orange-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="fas fa-list-check text-white text-lg sm:text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-xs sm:text-sm mb-0.5">Revisar Mis Solicitudes</h3>
                                        <p class="text-gray-600 text-xs sm:text-sm">Estado de tus permisos</p>
                                    </div>
                                </div>
                            </button>

                            <button @click="quickMessage('Quiero conocer las políticas de permisos')"
                                    class="card-hover bg-white border-2 border-primary/20 rounded-lg p-2 sm:p-3 text-left shadow-lg hover:border-primary">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="fas fa-book text-white text-lg sm:text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-xs sm:text-sm mb-0.5">Políticas y Reglas</h3>
                                        <p class="text-gray-600 text-xs sm:text-sm">Normas sobre vacaciones</p>
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
                                            <div class="bg-white rounded-2xl rounded-tl-md px-3 sm:px-4 py-2 sm:py-3 shadow-md border border-gray-100">
                                                <div class="text-gray-800 text-xs sm:text-sm text-readable leading-relaxed" x-html="message.text"></div>
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
                                                <div class="bg-primary rounded-2xl rounded-tr-md px-3 sm:px-4 py-2 sm:py-3 shadow-lg">
                                                    <p class="text-white text-xs sm:text-sm leading-relaxed" x-text="message.text"></p>
                                                    <!-- Archivos adjuntos -->
                                                    <div x-show="message.files && message.files.length > 0" class="mt-3 pt-3 border-t border-white/20">
                                                        <div class="flex flex-wrap gap-2">
                                                            <template x-for="(file, index) in message.files" :key="index">
                                                                <div class="bg-white/20 rounded-lg px-3 py-2 flex items-center space-x-2">
                                                                    <i class="fas fa-file text-white text-sm"></i>
                                                                    <span class="text-white text-sm font-medium" x-text="file.name"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
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
            <div class="bg-white border-t border-gray-100 px-2 sm:px-3 md:px-4 py-2 sm:py-3 md:py-4">
                <form @submit.prevent="sendMessage()">
                    <!-- Archivos adjuntos -->
                    <div x-show="attachedFiles.length > 0" class="mb-3 sm:mb-4">
                        <div class="flex items-center space-x-2 flex-wrap gap-2">
                            <template x-for="(file, index) in attachedFiles" :key="index">
                                <div class="flex items-center space-x-2 bg-blue-50 border border-blue-200 rounded-lg px-2 sm:px-3 py-1.5 sm:py-2">
                                    <i class="fas fa-file text-blue-600 text-xs sm:text-sm"></i>
                                    <span class="text-xs sm:text-sm font-medium text-gray-700" x-text="file.name"></span>
                                    <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-times text-xs sm:text-sm"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-end space-x-2 sm:space-x-3 md:space-x-4">
                        <div class="flex-1">
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">Escribe tu consulta:</label>
                            <div class="relative">
                                <input type="text"
                                       x-model="currentMessage"
                                       placeholder="Ejemplo: Quiero solicitar permisos..."
                                       class="input-focus w-full px-3 py-2.5 sm:py-3 text-sm border-2 border-gray-200 rounded-lg bg-gray-50 focus:bg-white transition-all duration-300"
                                       autofocus>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-keyboard text-base"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1 sm:space-y-2">
                            <button type="button"
                                    @click="$refs.fileInput.click()"
                                    class="px-3 py-3 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center text-gray-600 transition-all duration-300 shadow-md hover:shadow-lg">
                                <i class="fas fa-paperclip text-base"></i>
                            </button>
                            <input type="file" 
                                   x-ref="fileInput" 
                                   @change="handleFileSelect($event)"
                                   multiple
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                   class="hidden">
                        </div>
                        <button type="submit"
                                :disabled="!currentMessage.trim() && attachedFiles.length === 0"
                                class="btn-primary px-4 sm:px-6 py-3 text-white font-semibold rounded-lg flex items-center space-x-2 shadow-lg text-sm">
                            <span class="hidden sm:inline">Enviar</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <div class="flex items-center justify-center mt-4">
                        <button @click="volverAlMenu()" 
                                class="flex items-center space-x-2 text-gray-600 hover:text-primary transition-colors duration-300 px-4 py-2 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-home"></i>
                            <span class="text-sm font-medium">Volver al menú principal</span>
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
                attachedFiles: [],

                init() {
                    this.scrollToBottom();
                    // Guardar referencia global
                    window.chatInstance = this;

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
                    if (!this.currentMessage.trim() && this.attachedFiles.length === 0) return;

                    const now = new Date();
                    const time = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });

                    // Mensaje con archivos adjuntos
                    let messageText = this.currentMessage;
                    if (this.attachedFiles.length > 0) {
                        const filesInfo = this.attachedFiles.map(f => f.name).join(', ');
                        messageText += ` (Archivos adjuntos: ${filesInfo})`;
                    }

                    this.messages.push({
                        type: 'user',
                        text: messageText,
                        files: this.attachedFiles.map(f => ({ name: f.name, size: f.size, type: f.type })),
                        time: time
                    });

                    const userMessage = this.currentMessage.toLowerCase();
                    this.currentMessage = '';
                    this.attachedFiles = []; // Limpiar archivos después de enviar
                    this.isTyping = true;
                    this.scrollToBottom();

                    setTimeout(() => {
                        this.isTyping = false;
                        // Obtener archivos del último mensaje enviado
                        const lastMessage = this.messages[this.messages.length - 1];
                        const files = lastMessage.files || [];
                        this.messages.push({
                            type: 'bot',
                            text: this.getBotResponse(userMessage, files),
                            time: time
                        });
                        this.scrollToBottom();
                    }, 1000 + Math.random() * 800);
                },

                quickMessage(message) {
                    this.currentMessage = message;
                    this.sendMessage();
                },

                handleFileSelect(event) {
                    const files = Array.from(event.target.files);
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    
                    files.forEach(file => {
                        // Validar tipo de archivo
                        if (!allowedTypes.includes(file.type)) {
                            alert(`El archivo "${file.name}" no es un tipo válido. Solo se permiten PDF, JPG, PNG, DOC, DOCX`);
                            return;
                        }
                        
                        // Validar tamaño
                        if (file.size > maxSize) {
                            alert(`El archivo "${file.name}" es demasiado grande. El tamaño máximo es 5MB`);
                            return;
                        }
                        
                        // Agregar archivo a la lista
                        this.attachedFiles.push({
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            file: file
                        });
                    });
                    
                    // Limpiar el input
                    event.target.value = '';
                },

                removeFile(index) {
                    this.attachedFiles.splice(index, 1);
                },

                getBotResponse(message, files = []) {
                    // Detectar si hay archivos adjuntos y mostrar aprobación automática
                    if (files && files.length > 0) {
                        return `
                            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>✓ Solicitud Aprobada Automáticamente</strong>
                                </p>
                                <p class="text-sm text-green-700 mt-2 ml-5">Documentos recibidos correctamente. Tu solicitud ha sido procesada.</p>
                            </div>
                            <p class="text-gray-600">Solicitud completada exitosamente. Recibirás notificación por correo.</p>
                            ${this.getActionButtons()}
                        `;
                    }

                    // Detectar solicitud de vacaciones con fechas (sin archivos)
                    if (message.includes('vacaciones del') && message.includes('al')) {
                        return `
                            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>✓ Solicitud de Vacaciones Aprobada Automáticamente</strong>
                                </p>
                                <p class="text-sm text-green-700 mt-2 ml-5">Tu solicitud ha sido procesada exitosamente.</p>
                            </div>
                            <p class="text-gray-600">Tu solicitud de vacaciones será revisada y recibirás notificación por correo.</p>
                            ${this.getActionButtons()}
                        `;
                    }

                    // Detectar solicitud de licencia de paternidad con fechas
                    if (message.includes('licencia de paternidad del') && message.includes('al')) {
                        return `
                            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>✓ Solicitud de Licencia de Paternidad Aprobada Automáticamente</strong>
                                </p>
                                <p class="text-sm text-green-700 mt-2 ml-5">Tu solicitud ha sido procesada exitosamente.</p>
                            </div>
                            <p class="text-gray-600">Tu solicitud será revisada y recibirás notificación por correo. Recuerda adjuntar el certificado.</p>
                            ${this.getActionButtons()}
                        `;
                    }

                    // Detectar solicitud de licencia de maternidad con fechas
                    if (message.includes('licencia de maternidad del') && message.includes('al')) {
                        return `
                            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>✓ Solicitud de Licencia de Maternidad Aprobada Automáticamente</strong>
                                </p>
                                <p class="text-sm text-green-700 mt-2 ml-5">Tu solicitud ha sido procesada exitosamente.</p>
                            </div>
                            <p class="text-gray-600">Tu solicitud será revisada y recibirás notificación por correo. Recuerda adjuntar el certificado.</p>
                            ${this.getActionButtons()}
                        `;
                    }

                    // Detectar solicitud de permiso médico con fechas
                    if (message.includes('permiso médico del') && message.includes('al')) {
                        return `
                            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>✓ Solicitud de Permiso Médico Aprobada Automáticamente</strong>
                                </p>
                                <p class="text-sm text-green-700 mt-2 ml-5">Tu solicitud ha sido procesada exitosamente.</p>
                            </div>
                            <p class="text-gray-600">Tu solicitud será revisada y recibirás notificación por correo.</p>
                            ${this.getActionButtons()}
                        `;
                    }

                    if ((message.includes('solicitar') || message.includes('pedir') || message.includes('quiero')) &&
                        (message.includes('permiso') || message.includes('vacacion'))) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">¡Perfecto! Te ayudaré a solicitar tu permiso 😊</p>
                            <p class="mb-3 text-xs">Por favor, selecciona el tipo de permiso que necesitas:</p>
                            <div class="bg-primary/5 rounded-xl p-2 sm:p-3 space-y-1.5 sm:space-y-2 my-2">
                                <div data-option="1" class="permission-option w-full flex items-center space-x-2 p-2 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-7 h-7 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform text-xs">1</span>
                                    <span class="font-medium text-sm sm:text-base text-left flex-1">Vacaciones (15 días hábiles/año)</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <div data-option="2" class="permission-option w-full flex items-center space-x-2 p-2 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-7 h-7 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform text-xs">2</span>
                                    <span class="font-medium text-sm sm:text-base text-left flex-1">Licencia de Paternidad/Maternidad</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <div data-option="3" class="permission-option w-full flex items-center space-x-2 p-2 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-7 h-7 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform text-xs">3</span>
                                    <span class="font-medium text-sm sm:text-base text-left flex-1">Permiso Médico</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <div data-option="4" class="permission-option w-full flex items-center space-x-2 p-2 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-7 h-7 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform text-xs">4</span>
                                    <span class="font-medium text-sm sm:text-base text-left flex-1">Otras Causas</span>
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
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">📊 Aquí está tu saldo actual de permisos:</p>
                            <div class="space-y-3 my-3">
                                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-2 sm:p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-umbrella-beach text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Vacaciones</p>
                                                <p class="text-xl sm:text-base sm:text-lg font-bold text-blue-600">15 días</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-2 sm:p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-baby text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Licencia de Paternidad</p>
                                                <p class="text-xl sm:text-base sm:text-lg font-bold text-green-600">15 días</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-2 sm:p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-baby-carriage text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Licencia de Maternidad</p>
                                                <p class="text-xl sm:text-base sm:text-lg font-bold text-purple-600">4 meses</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-2 sm:p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-file-medical text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Permiso por Enfermedad</p>
                                                <p class="text-xl sm:text-base sm:text-lg font-bold text-red-600">Con orden médica</p>
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
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">📋 Estado de tus solicitudes de permisos:</p>
                            <div class="space-y-3 my-3">
                                <div class="bg-green-50 border border-green-200 rounded-xl p-2 sm:p-3 shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-lg font-bold text-green-900">✓ Solicitudes Aprobadas</span>
                                        <span class="text-xl sm:text-base sm:text-lg font-bold text-green-600">3</span>
                                    </div>
                                    <p class="text-sm text-green-700">Última aprobada: Vacaciones del 15-20 Diciembre 2023</p>
                                </div>
                                <div class="bg-orange-50 border border-orange-200 rounded-xl p-2 sm:p-3 shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-lg font-bold text-orange-900">⏳ En Revisión</span>
                                        <span class="text-xl sm:text-base sm:text-lg font-bold text-orange-600">1</span>
                                    </div>
                                    <p class="text-sm text-orange-700">Permiso médico por 2 días - En proceso de aprobación</p>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-2 sm:p-3 shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-lg font-bold text-gray-700">✗ Rechazadas</span>
                                        <span class="text-xl sm:text-base sm:text-lg font-bold text-gray-600">0</span>
                                    </div>
                                    <p class="text-sm text-gray-600">¡Excelente! No tienes solicitudes rechazadas</p>
                                </div>
                            </div>
                            <p class="text-gray-600 bg-blue-50 rounded-lg p-3 mt-4">
                                <i class="fas fa-bell text-blue-500 mr-2"></i>
                                Recibirás una notificación por correo cuando tu solicitud sea procesada.
                            </p>
                            ${this.getActionButtons()}
                        `;
                    }

                    if (message.includes('política') || message.includes('politica') || message.includes('regla') || message.includes('norma')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">📖 Políticas de Permisos de ComfaChoco:</p>
                            <div class="bg-primary/5 rounded-xl p-2 sm:p-3 space-y-1.5 sm:space-y-2 my-2">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-base mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Vacaciones:</p>
                                        <p class="text-gray-700">15 días hábiles por período de un año, acumulables hasta 2 períodos. Se solicitan 1 mes posterior al cumplimiento del período. Los sábados cuentan como día hábil.</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-base mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Maternidad/Paternidad:</p>
                                        <p class="text-gray-700">Mujeres: 4 meses | Hombres: 15 días. Requiere certificado.</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-base mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Permisos Médicos:</p>
                                        <p class="text-gray-700">Requieren órdenes médicas y todos los anexos relacionados con la enfermedad.</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-base mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Aprobación:</p>
                                        <p class="text-gray-700">Menos de 2 días: con jefe inmediato | Más de 3 días: con Recursos Humanos</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-base mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Gestión de Reemplazo:</p>
                                        <p class="text-gray-700">70% de funcionarios deben estar disponibles. Personas de la misma dependencia no pueden salir al mismo tiempo.</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-check-circle text-primary text-base mt-1"></i>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Otras Causas:</p>
                                        <p class="text-gray-700">Incluye: muerte de familiar (certificado de defunción), miembro/jurado (documento acreditativo), y otras con anexo correspondiente</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-600">¿Necesitas más información sobre alguna política en específico?</p>
                            ${this.getActionButtons()}
                        `;
                    }

                    if (message.includes('1') || message.includes('vacaciones')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">🏖️ Solicitud de Vacaciones</p>
                            <p class="mb-1.5 text-xs">Tienes <strong class="text-primary text-xs">15 días disponibles</strong> para tomar.</p>
                            <div class="bg-blue-50 rounded-xl p-2 sm:p-3 my-2">
                                <p class="font-semibold mb-1.5 text-xs">Selecciona las fechas de tus vacaciones:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                            Fecha de inicio
                                        </label>
                                        <input type="date" id="fecha-inicio" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="document.getElementById('fecha-fin').min = this.value; calcularDiasVacaciones();">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                                            Fecha de fin
                                        </label>
                                        <input type="date" id="fecha-fin" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="calcularDiasVacaciones()">
                                    </div>
                                </div>
                                <div id="dias-resultado" class="mt-2 px-2 py-1.5 bg-blue-100 border border-blue-300 rounded-lg text-center hidden">
                                    <p class="text-xs font-semibold text-blue-800">
                                        <i class="fas fa-calendar-week mr-2"></i>
                                        <span id="dias-texto">0 días</span> de vacaciones solicitados
                                    </p>
                                </div>
                                <button onclick="procesarVacaciones()" class="mt-1.5 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Enviar Solicitud de Vacaciones
                                </button>
                            </div>
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-yellow-800 mb-2">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Importante:</strong> Las vacaciones deben solicitarse 1 mes posterior al cumplimiento del período
                                </p>
                                <p class="text-sm text-yellow-800 ml-6">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Los sábados cuentan como día hábil. Los días son acumulables hasta 2 períodos.
                                </p>
                            </div>
                        `;
                    }

                    if (message.includes('2') || message.includes('licencia') && !message.includes('paternidad') && !message.includes('maternidad')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">👨‍👶👩‍👶 Licencia de Paternidad/Maternidad</p>
                            <p class="mb-3">Por favor, selecciona el tipo de licencia que necesitas:</p>
                            <div class="bg-primary/5 rounded-xl p-2 sm:p-3 space-y-1.5 sm:space-y-2 my-2">
                                <div data-option="paternidad" class="permission-option w-full flex items-center space-x-2 p-2 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-7 h-7 bg-green-500 text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform text-xs">P</span>
                                    <span class="font-medium text-sm sm:text-base text-left flex-1">Licencia de Paternidad (15 días)</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <div data-option="maternidad" class="permission-option w-full flex items-center space-x-2 p-2 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-7 h-7 bg-purple-500 text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform text-xs">M</span>
                                    <span class="font-medium text-sm sm:text-base text-left flex-1">Licencia de Maternidad (4 meses)</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 text-center">
                                <i class="fas fa-hand-pointer text-primary mr-2"></i>
                                Haz clic en el tipo de licencia que necesitas
                            </p>
                        `;
                    }

                    if (message.includes('paternidad')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">👨‍👶 Licencia de Paternidad</p>
                            <p class="mb-1.5 text-xs">Tienes derecho a <strong class="text-primary text-xs">15 días</strong> de licencia de paternidad.</p>
                            <div class="bg-green-50 rounded-xl p-2 sm:p-3 my-2">
                                <p class="font-semibold mb-1.5 text-xs">Selecciona las fechas de tu licencia:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                                            Fecha de inicio
                                        </label>
                                        <input type="date" id="fecha-inicio-paternidad" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="document.getElementById('fecha-fin-paternidad').min = this.value; calcularDiasLicencia('paternidad');">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-check text-green-600 mr-2"></i>
                                            Fecha de fin
                                        </label>
                                        <input type="date" id="fecha-fin-paternidad" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="calcularDiasLicencia('paternidad');">
                                    </div>
                                </div>
                                <div id="dias-resultado-paternidad" class="mt-2 px-2 py-1.5 bg-green-100 border border-green-300 rounded-lg text-center hidden">
                                    <p class="text-xs font-semibold text-green-800">
                                        <i class="fas fa-calendar-week mr-2"></i>
                                        <span id="dias-texto-paternidad">0 días</span> de licencia solicitados
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <p class="font-semibold mb-2 text-xs">Documentos necesarios:</p>
                                    <ul class="space-y-2 mb-3">
                                    <li class="flex items-center space-x-2">
                                            <i class="fas fa-certificate text-green-600"></i>
                                            <span>Certificado de nacimiento o adopción</span>
                                    </li>
                                    </ul>
                                    <div class="border-2 border-dashed border-green-300 rounded-lg p-2 bg-green-50/50">
                                        <label class="flex flex-col items-center justify-center cursor-pointer">
                                            <input type="file" 
                                                   id="archivo-paternidad" 
                                                   onchange="handleFileSelectLicencia('paternidad', this)"
                                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                   class="hidden">
                                            <i class="fas fa-cloud-upload-alt text-green-600 text-xl mb-1"></i>
                                            <span class="text-xs font-medium text-gray-700">Adjuntar certificado</span>
                                            <span class="text-xs text-gray-500 mt-0.5">PDF, JPG, PNG, DOCX (máx. 5MB)</span>
                                        </label>
                                    </div>
                                    <div id="archivos-paternidad" class="mt-3 flex flex-wrap gap-2"></div>
                                </div>
                                <button onclick="procesarLicencia('paternidad')" class="mt-1.5 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-1.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg text-xs">
                                    <i class="fas fa-paper-plane mr-1.5"></i>
                                    Enviar Solicitud
                                </button>
                            </div>
                        `;
                    }

                    if (message.includes('maternidad')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">👩‍👶 Licencia de Maternidad</p>
                            <p class="mb-1.5 text-xs">Tienes derecho a <strong class="text-primary text-xs">4 meses</strong> de licencia de maternidad.</p>
                            <div class="bg-purple-50 rounded-xl p-2 sm:p-3 my-2">
                                <p class="font-semibold mb-1.5 text-xs">Selecciona las fechas de tu licencia:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                                            Fecha de inicio
                                        </label>
                                        <input type="date" id="fecha-inicio-maternidad" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="document.getElementById('fecha-fin-maternidad').min = this.value; calcularDiasLicencia('maternidad');">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-check text-purple-600 mr-2"></i>
                                            Fecha de fin
                                        </label>
                                        <input type="date" id="fecha-fin-maternidad" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="calcularDiasLicencia('maternidad');">
                                    </div>
                                </div>
                                <div id="dias-resultado-maternidad" class="mt-2 px-2 py-1.5 bg-purple-100 border border-purple-300 rounded-lg text-center hidden">
                                    <p class="text-xs font-semibold text-purple-800">
                                        <i class="fas fa-calendar-week mr-2"></i>
                                        <span id="dias-texto-maternidad">0 días</span> de licencia solicitados
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <p class="font-semibold mb-2 text-xs">Documentos necesarios:</p>
                                    <ul class="space-y-2 mb-3">
                                    <li class="flex items-center space-x-2">
                                            <i class="fas fa-certificate text-purple-600"></i>
                                            <span>Certificado de nacimiento o adopción</span>
                                    </li>
                                </ul>
                                    <div class="border-2 border-dashed border-purple-300 rounded-lg p-2 bg-purple-50/50">
                                        <label class="flex flex-col items-center justify-center cursor-pointer">
                                            <input type="file" 
                                                   id="archivo-maternidad" 
                                                   onchange="handleFileSelectLicencia('maternidad', this)"
                                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                   class="hidden">
                                            <i class="fas fa-cloud-upload-alt text-purple-600 text-xl mb-1"></i>
                                            <span class="text-xs font-medium text-gray-700">Adjuntar certificado</span>
                                            <span class="text-xs text-gray-500 mt-0.5">PDF, JPG, PNG, DOCX (máx. 5MB)</span>
                                        </label>
                            </div>
                                    <div id="archivos-maternidad" class="mt-3 flex flex-wrap gap-2"></div>
                                </div>
                                <button onclick="procesarLicencia('maternidad')" class="mt-1.5 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-1.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg text-xs">
                                    <i class="fas fa-paper-plane mr-1.5"></i>
                                    Enviar Solicitud
                                </button>
                            </div>
                        `;
                    }

                    if (message.includes('3') || message.includes('enfermedad') || message.includes('médico') || message.includes('medico')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">🏥 Permiso Médico</p>
                            <p class="mb-1.5 text-xs">Este permiso requiere documentación médica completa.</p>
                            <div class="bg-red-50 rounded-xl p-2 sm:p-3 my-2">
                                <p class="font-semibold mb-1.5 text-xs">Selecciona las fechas del permiso:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-alt text-red-600 mr-2"></i>
                                            Fecha de inicio
                                        </label>
                                        <input type="date" id="fecha-inicio-medico" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="document.getElementById('fecha-fin-medico').min = this.value; calcularDiasLicencia('medico');">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-check text-red-600 mr-2"></i>
                                            Fecha de fin
                                        </label>
                                        <input type="date" id="fecha-fin-medico" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="calcularDiasLicencia('medico');">
                                    </div>
                                </div>
                                <div id="dias-resultado-medico" class="mb-4 px-4 py-2 bg-red-100 border border-red-300 rounded-lg text-center hidden">
                                    <p class="text-sm font-semibold text-red-800">
                                        <i class="fas fa-calendar-week mr-2"></i>
                                        <span id="dias-texto-medico">0 días</span> de permiso solicitados
                                    </p>
                                </div>
                                <p class="font-semibold mb-2 text-xs">Documentos necesarios:</p>
                                <ul class="space-y-2 mb-3">
                                    <li class="flex items-center space-x-2">
                                        <i class="fas fa-file-medical text-red-600"></i>
                                        <span>Orden médica o incapacidad médica</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <i class="fas fa-folder-open text-red-600"></i>
                                        <span>Todos los anexos relacionados con la enfermedad</span>
                                    </li>
                                </ul>
                                <div class="border-2 border-dashed border-red-300 rounded-lg p-2 bg-red-50/50">
                                    <label class="flex flex-col items-center justify-center cursor-pointer">
                                        <input type="file" 
                                               id="archivo-medico" 
                                               onchange="handleFileSelectLicencia('medico', this)"
                                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                               multiple
                                               class="hidden">
                                        <i class="fas fa-cloud-upload-alt text-red-600 text-3xl mb-2"></i>
                                        <span class="text-xs font-medium text-gray-700">Adjuntar documentos médicos</span>
                                        <span class="text-xs text-gray-500 mt-0.5">PDF, JPG, PNG, DOCX (máx. 5MB)</span>
                                    </label>
                            </div>
                                <div id="archivos-medico" class="mt-3 flex flex-wrap gap-2"></div>
                                <button onclick="procesarPermisoMedico()" class="mt-1.5 w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-1.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg text-xs">
                                    <i class="fas fa-paper-plane mr-1.5"></i>
                                    Enviar Solicitud
                                </button>
                            </div>
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-yellow-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Nota:</strong> Menos de 2 días se aprueba con jefe inmediato | Más de 3 días con Recursos Humanos
                                </p>
                            </div>
                        `;
                    }

                    if (message.includes('4') || message.includes('otras') || message.includes('otra')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">📋 Otras Causas</p>
                            <p class="mb-3">Selecciona el tipo específico de permiso:</p>
                            <div class="bg-primary/5 rounded-xl p-2 sm:p-3 space-y-1.5 sm:space-y-2 my-2">
                                <div data-option="muerte-familiar" class="permission-option w-full flex items-center space-x-2 p-2 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-7 h-7 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform text-xs">A</span>
                                    <span class="font-medium text-sm sm:text-base text-left flex-1">Permiso por Muerte de Familiar</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </div>
                                <div data-option="miembro-jurado" class="permission-option w-full flex items-center space-x-2 p-2 bg-white rounded-lg shadow-sm hover:shadow-lg hover:bg-primary/5 hover:border-2 hover:border-primary transition-all duration-300 cursor-pointer group" style="user-select: none;">
                                    <span class="w-7 h-7 bg-primary text-white rounded-lg flex items-center justify-center font-bold group-hover:scale-110 transition-transform text-xs">B</span>
                                    <span class="font-medium text-sm sm:text-base text-left flex-1">Permiso Miembro/Jurado</span>
                                    <i class="fas fa-chevron-right text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 text-center">
                                <i class="fas fa-hand-pointer text-primary mr-2"></i>
                                Haz clic en la opción que necesitas
                            </p>
                        `;
                    }

                    if (message.includes('muerte') || message.includes('familiar') || message.includes('certificado defunción')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">💔 Permiso por Muerte de Familiar</p>
                            <p class="mb-1.5 text-xs">Este permiso es para situaciones de duelo.</p>
                            <div class="bg-gray-50 rounded-xl p-2 sm:p-3 my-2">
                                <p class="font-semibold mb-1.5 text-xs">Selecciona las fechas de tu permiso:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-alt text-gray-600 mr-2"></i>
                                            Fecha de inicio
                                        </label>
                                        <input type="date" id="fecha-inicio-muerte" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-gray-500 focus:ring-2 focus:ring-gray-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="document.getElementById('fecha-fin-muerte').min = this.value; calcularDiasLicencia('muerte');">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-check text-gray-600 mr-2"></i>
                                            Fecha de fin
                                        </label>
                                        <input type="date" id="fecha-fin-muerte" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-gray-500 focus:ring-2 focus:ring-gray-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="calcularDiasLicencia('muerte');">
                                    </div>
                                </div>
                                <div id="dias-resultado-muerte" class="mt-2 px-2 py-1.5 bg-gray-100 border border-gray-300 rounded-lg text-center hidden">
                                    <p class="text-xs font-semibold text-gray-800">
                                        <i class="fas fa-calendar-week mr-2"></i>
                                        <span id="dias-texto-muerte">0 días</span> de permiso solicitados
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <p class="font-semibold mb-2 text-xs">Documentos necesarios:</p>
                                    <ul class="space-y-1.5 mb-2">
                                        <li class="flex items-center space-x-1.5">
                                            <i class="fas fa-certificate text-gray-600"></i>
                                            <span>Certificado de defunción</span>
                                    </li>
                                        <li class="flex items-center space-x-2">
                                            <i class="fas fa-users text-gray-600"></i>
                                            <span>Certificado de parentesco</span>
                                    </li>
                                </ul>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-2 bg-gray-50/50">
                                            <label class="flex flex-col items-center justify-center cursor-pointer">
                                                <input type="file" 
                                                       id="archivo-defuncion" 
                                                       onchange="handleFileSelectLicencia('defuncion', this)"
                                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                       class="hidden">
                                                <i class="fas fa-cloud-upload-alt text-gray-600 text-3xl mb-2"></i>
                                                <span class="text-sm font-medium text-gray-700">Certificado de defunción</span>
                                                <span class="text-xs text-gray-500 mt-0.5">PDF, JPG, PNG, DOCX (máx. 5MB)</span>
                                            </label>
                                        </div>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-2 bg-gray-50/50">
                                            <label class="flex flex-col items-center justify-center cursor-pointer">
                                                <input type="file" 
                                                       id="archivo-parentesco" 
                                                       onchange="handleFileSelectLicencia('parentesco', this)"
                                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                       class="hidden">
                                                <i class="fas fa-cloud-upload-alt text-gray-600 text-3xl mb-2"></i>
                                                <span class="text-sm font-medium text-gray-700">Certificado de parentesco</span>
                                                <span class="text-xs text-gray-500 mt-0.5">PDF, JPG, PNG, DOCX (máx. 5MB)</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="archivos-defuncion" class="mt-3 flex flex-wrap gap-2"></div>
                                    <div id="archivos-parentesco" class="mt-2 flex flex-wrap gap-2"></div>
                                </div>
                                <button onclick="procesarLicencia('muerte')" class="mt-1.5 w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-1.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg text-xs">
                                    <i class="fas fa-paper-plane mr-1.5"></i>
                                    Enviar Solicitud
                                </button>
                            </div>
                        `;
                    }

                    if (message.includes('miembro') || message.includes('jurado')) {
                        return `
                            <p class="text-xs sm:text-sm font-semibold mb-1.5">⚖️ Permiso por Miembro/Jurado</p>
                            <p class="mb-1.5 text-xs">Este permiso es para funciones como miembro de jurado o similar.</p>
                            <div class="bg-indigo-50 rounded-xl p-2 sm:p-3 my-2">
                                <p class="font-semibold mb-1.5 text-xs">Selecciona las fechas de tu permiso:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>
                                            Fecha de inicio
                                        </label>
                                        <input type="date" id="fecha-inicio-jurado" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="document.getElementById('fecha-fin-jurado').min = this.value; calcularDiasLicencia('jurado');">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-0.5">
                                            <i class="fas fa-calendar-check text-indigo-600 mr-2"></i>
                                            Fecha de fin
                                        </label>
                                        <input type="date" id="fecha-fin-jurado" class="w-full px-2 py-1.5 border-2 text-xs border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all" min="${new Date().toISOString().split('T')[0]}" onchange="calcularDiasLicencia('jurado');">
                                    </div>
                                </div>
                                <div id="dias-resultado-jurado" class="mt-2 px-2 py-1.5 bg-indigo-100 border border-indigo-300 rounded-lg text-center hidden">
                                    <p class="text-sm font-semibold text-indigo-800">
                                        <i class="fas fa-calendar-week mr-2"></i>
                                        <span id="dias-texto-jurado">0 días</span> de permiso solicitados
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <p class="font-semibold mb-3">Documento necesario:</p>
                                    <ul class="space-y-1.5 mb-2">
                                        <li class="flex items-center space-x-1.5">
                                            <i class="fas fa-file-alt text-indigo-600"></i>
                                            <span>Documento que acredite la función</span>
                                        </li>
                                    </ul>
                                    <div class="border-2 border-dashed border-indigo-300 rounded-lg p-2 bg-indigo-50/50">
                                        <label class="flex flex-col items-center justify-center cursor-pointer">
                                            <input type="file" 
                                                   id="archivo-jurado" 
                                                   onchange="handleFileSelectLicencia('jurado', this)"
                                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                   class="hidden">
                                            <i class="fas fa-cloud-upload-alt text-indigo-600 text-xl mb-1"></i>
                                            <span class="text-xs font-medium text-gray-700">Adjuntar documento</span>
                                            <span class="text-xs text-gray-500 mt-0.5">PDF, JPG, PNG, DOCX (máx. 5MB)</span>
                                        </label>
                                    </div>
                                    <div id="archivos-jurado" class="mt-3 flex flex-wrap gap-2"></div>
                                </div>
                                <button onclick="procesarLicencia('jurado')" class="mt-1.5 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-1.5 px-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg text-xs">
                                    <i class="fas fa-paper-plane mr-1.5"></i>
                                    Enviar Solicitud
                                </button>
                            </div>
                        `;
                    }

                    if (message.includes('hola') || message.includes('buenos') || message.includes('buenas')) {
                        return `<p class="text-lg">¡Hola <strong><?= htmlspecialchars($primer_nombre) ?></strong>! 👋</p><p class="mt-3 text-gray-700">Soy tu asistente virtual de Recursos Humanos de ComfaChoco. Estoy aquí para ayudarte con todo lo relacionado a tus permisos y vacaciones.</p><p class="mt-3 text-gray-700">¿En qué puedo ayudarte hoy?</p>${this.getActionButtons()}`;
                    }

                    if (message.includes('gracias')) {
                        return `<p class="text-lg">¡De nada, <strong><?= htmlspecialchars($primer_nombre) ?></strong>! 😊</p><p class="mt-3 text-gray-700">Es un placer ayudarte. Estoy disponible siempre que me necesites.</p>${this.getActionButtons()}`;
                    }

                    return `
                        <p class="text-gray-600">¿En qué más puedo ayudarte hoy?</p>
                        ${this.getActionButtons()}
                    `;
                },

                getActionButtons() {
                    return `
                        <div class="mt-6 flex justify-center">
                            <button onclick="window.chatInstance.volverAlMenu()" class="bg-primary hover:bg-primary-dark text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg">
                                <i class="fas fa-home mr-2"></i>
                                Volver al menú principal
                            </button>
                            </div>
                    `;
                },

                volverAlMenu() {
                    // Limpiar mensajes para mostrar el menú principal
                    this.messages = [];
                    this.currentMessage = '';
                    this.attachedFiles = [];
                    // Hacer scroll al inicio
                    this.$nextTick(() => {
                        const container = this.$refs.chatContainer;
                        if (container) {
                            container.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        }
                    });
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

        // Función para manejar archivos en formularios de licencias
        window.handleFileSelectLicencia = function(tipo, input) {
            const files = Array.from(input.files);
            if (!files || files.length === 0) return;

            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            
            files.forEach(file => {
                // Validar tipo de archivo
                if (!allowedTypes.includes(file.type)) {
                    alert(`El archivo "${file.name}" no es un tipo válido. Solo se permiten PDF, JPG, PNG, DOC, DOCX`);
                    return;
                }
                
                // Validar tamaño
                if (file.size > maxSize) {
                    alert(`El archivo "${file.name}" es demasiado grande. El tamaño máximo es 5MB`);
                    return;
                }
                
                // Mostrar archivo adjunto en el recuadro
                const archivosDiv = document.getElementById(`archivos-${tipo}`);
                const archivoDiv = document.createElement('div');
                archivoDiv.className = 'flex items-center space-x-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2';
                archivoDiv.innerHTML = `
                    <i class="fas fa-file text-blue-600"></i>
                    <span class="text-sm font-medium text-gray-700">${file.name}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                archivosDiv.appendChild(archivoDiv);
                
                // Guardar referencia al archivo
                archivoDiv.dataset.fileName = file.name;
                archivoDiv.dataset.fileSize = file.size;
                archivoDiv.dataset.fileType = file.type;
            });
            
            // Limpiar input
            input.value = '';
        }

        // Función global para procesar permiso médico
        window.procesarPermisoMedico = function() {
            const fechaInicio = document.getElementById('fecha-inicio-medico').value;
            const fechaFin = document.getElementById('fecha-fin-medico').value;
            
            if (!fechaInicio || !fechaFin) {
                alert('Por favor selecciona ambas fechas');
                return;
            }
            
            if (fechaFin < fechaInicio) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                return;
            }
            
            // Formatear fechas para mostrar
            const fechaInicioFormateada = new Date(fechaInicio).toLocaleDateString('es-CO', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const fechaFinFormateada = new Date(fechaFin).toLocaleDateString('es-CO', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            // Verificar si hay archivos adjuntos
            const archivosDiv = document.getElementById('archivos-medico');
            const archivosAdjuntos = archivosDiv.querySelectorAll('div');
            
            // Crear array de archivos para el chat
            const files = [];
            archivosAdjuntos.forEach(archivoDiv => {
                if (archivoDiv.dataset.fileName) {
                    files.push({
                        name: archivoDiv.dataset.fileName,
                        size: archivoDiv.dataset.fileSize,
                        type: archivoDiv.dataset.fileType
                    });
                }
            });
            
            // MOSTRAR APROBACIÓN AUTOMÁTICA DIRECTAMENTE (MODO PRUEBA)
            const chatElement = document.querySelector('[x-data^="premiumChat"]');
            if (chatElement && chatElement.__x) {
                const chat = chatElement.__x.$data;
                const now = new Date();
                const time = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
                
                // Agregar mensaje del usuario
                chat.messages.push({
                    type: 'user',
                    text: `Quiero solicitar permiso médico del ${fechaInicioFormateada} al ${fechaFinFormateada}`,
                    files: files,
                    time: time
                });
                
                // Mostrar respuesta de aprobación automática
                setTimeout(() => {
                    chat.messages.push({
                        type: 'bot',
                        text: `
                            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>✓ Solicitud de Permiso Médico Aprobada Automáticamente</strong>
                                </p>
                                <p class="text-sm text-green-700 mt-2 ml-5">Tu solicitud ha sido procesada exitosamente.</p>
                            </div>
                            <p class="text-gray-600">Tu solicitud será revisada y recibirás notificación por correo.</p>
                            ${chat.getActionButtons()}
                        `,
                        time: time
                    });
                    chat.scrollToBottom();
                }, 500);
            }
        }

        // Guardar referencia global
        window.chatInstance = null;

        // Función para calcular días de licencias
        window.calcularDiasLicencia = function(tipo) {
            const fechaInicio = document.getElementById(`fecha-inicio-${tipo}`).value;
            const fechaFin = document.getElementById(`fecha-fin-${tipo}`).value;
            const diasResultado = document.getElementById(`dias-resultado-${tipo}`);
            const diasTexto = document.getElementById(`dias-texto-${tipo}`);
            
            if (fechaInicio && fechaFin) {
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);
                const diffTime = Math.abs(fin - inicio);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir ambos días
                
                diasTexto.textContent = `${diffDays} ${diffDays === 1 ? 'día' : 'días'}`;
                diasResultado.classList.remove('hidden');
            } else {
                diasResultado.classList.add('hidden');
            }
        }

        // Función global para procesar licencias
        window.procesarLicencia = function(tipo) {
            const fechaInicio = document.getElementById(`fecha-inicio-${tipo}`).value;
            const fechaFin = document.getElementById(`fecha-fin-${tipo}`).value;
            
            if (!fechaInicio || !fechaFin) {
                alert('Por favor selecciona ambas fechas');
                return;
            }
            
            if (fechaFin < fechaInicio) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                return;
            }
            
            // Formatear fechas para mostrar
            const fechaInicioFormateada = new Date(fechaInicio).toLocaleDateString('es-CO', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const fechaFinFormateada = new Date(fechaFin).toLocaleDateString('es-CO', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            let tipoTexto = '';
            let tipoTextoMensaje = '';
            
            if (tipo === 'paternidad') {
                tipoTexto = 'licencia de paternidad';
                tipoTextoMensaje = 'Licencia de Paternidad';
            } else if (tipo === 'maternidad') {
                tipoTexto = 'licencia de maternidad';
                tipoTextoMensaje = 'Licencia de Maternidad';
            } else if (tipo === 'muerte') {
                tipoTexto = 'permiso por muerte de familiar';
                tipoTextoMensaje = 'Permiso por Muerte de Familiar';
            } else if (tipo === 'jurado') {
                tipoTexto = 'permiso por miembro/jurado';
                tipoTextoMensaje = 'Permiso por Miembro/Jurado';
            }
            
            // Verificar si hay archivos adjuntos (manejo especial para muerte)
            let files = [];
            if (tipo === 'muerte') {
                // Recopilar archivos de defunción
                const archivosDefuncion = document.getElementById('archivos-defuncion');
                if (archivosDefuncion) {
                    archivosDefuncion.querySelectorAll('div').forEach(archivoDiv => {
                        if (archivoDiv.dataset.fileName) {
                            files.push({
                                name: 'Defunción: ' + archivoDiv.dataset.fileName,
                                size: archivoDiv.dataset.fileSize,
                                type: archivoDiv.dataset.fileType
                            });
                        }
                    });
                }
                // Recopilar archivos de parentesco
                const archivosParentesco = document.getElementById('archivos-parentesco');
                if (archivosParentesco) {
                    archivosParentesco.querySelectorAll('div').forEach(archivoDiv => {
                        if (archivoDiv.dataset.fileName) {
                            files.push({
                                name: 'Parentesco: ' + archivoDiv.dataset.fileName,
                                size: archivoDiv.dataset.fileSize,
                                type: archivoDiv.dataset.fileType
                            });
                        }
                    });
                }
            } else {
                // Recopilar archivos normales para otros tipos
                const archivosDiv = document.getElementById(`archivos-${tipo}`);
                if (archivosDiv) {
                    archivosDiv.querySelectorAll('div').forEach(archivoDiv => {
                        if (archivoDiv.dataset.fileName) {
                            files.push({
                                name: archivoDiv.dataset.fileName,
                                size: archivoDiv.dataset.fileSize,
                                type: archivoDiv.dataset.fileType
                            });
                        }
                    });
                }
            }
            
            // MOSTRAR APROBACIÓN AUTOMÁTICA DIRECTAMENTE (MODO PRUEBA)
            const chatElement = document.querySelector('[x-data^="premiumChat"]');
            if (chatElement && chatElement.__x) {
                const chat = chatElement.__x.$data;
                const now = new Date();
                const time = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
                
                // Agregar mensaje del usuario
                chat.messages.push({
                    type: 'user',
                    text: `Solicitud de ${tipoTexto} del ${fechaInicioFormateada} al ${fechaFinFormateada}`,
                    files: files,
                    time: time
                });
                
                // Mostrar respuesta de aprobación automática
                setTimeout(() => {
                    chat.messages.push({
                        type: 'bot',
                        text: `
                            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>✓ Solicitud de ${tipoTextoMensaje} Aprobada Automáticamente</strong>
                                </p>
                                <p class="text-sm text-green-700 mt-2 ml-5">Tu solicitud ha sido procesada exitosamente.</p>
                            </div>
                            <p class="text-gray-600">Tu solicitud será revisada y recibirás notificación por correo.${tipo === 'muerte' ? ' Recuerda adjuntar ambos certificados.' : tipo !== 'muerte' ? ' Recuerda adjuntar el certificado.' : ''}</p>
                            ${chat.getActionButtons()}
                        `,
                        time: time
                    });
                    chat.scrollToBottom();
                }, 500);
            }
        }

        // Función para calcular días de vacaciones
        window.calcularDiasVacaciones = function() {
            const fechaInicio = document.getElementById('fecha-inicio').value;
            const fechaFin = document.getElementById('fecha-fin').value;
            const diasResultado = document.getElementById('dias-resultado');
            const diasTexto = document.getElementById('dias-texto');
            
            if (fechaInicio && fechaFin) {
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);
                const diffTime = Math.abs(fin - inicio);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir ambos días
                
                diasTexto.textContent = `${diffDays} ${diffDays === 1 ? 'día' : 'días'}`;
                diasResultado.classList.remove('hidden');
            } else {
                diasResultado.classList.add('hidden');
            }
        }

        // Función global para procesar vacaciones
        window.procesarVacaciones = function() {
            const fechaInicio = document.getElementById('fecha-inicio').value;
            const fechaFin = document.getElementById('fecha-fin').value;
            
            if (!fechaInicio || !fechaFin) {
                alert('Por favor selecciona ambas fechas');
                return;
            }
            
            if (fechaFin < fechaInicio) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                return;
            }
            
            // Formatear fechas para mostrar
            const fechaInicioFormateada = new Date(fechaInicio).toLocaleDateString('es-CO', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const fechaFinFormateada = new Date(fechaFin).toLocaleDateString('es-CO', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            // MOSTRAR APROBACIÓN AUTOMÁTICA DIRECTAMENTE (MODO PRUEBA)
            const chatElement = document.querySelector('[x-data^="premiumChat"]');
            if (chatElement && chatElement.__x) {
                const chat = chatElement.__x.$data;
                const now = new Date();
                const time = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
                
                // Agregar mensaje del usuario
                chat.messages.push({
                    type: 'user',
                    text: `Quiero vacaciones del ${fechaInicioFormateada} al ${fechaFinFormateada}`,
                    time: time
                });
                
                // Mostrar respuesta de aprobación automática
                setTimeout(() => {
                    chat.messages.push({
                        type: 'bot',
                        text: `
                            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 my-4">
                                <p class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>✓ Solicitud de Vacaciones Aprobada Automáticamente</strong>
                                </p>
                                <p class="text-sm text-green-700 mt-2 ml-5">Tu solicitud ha sido procesada exitosamente.</p>
                            </div>
                            <p class="text-gray-600">Tu solicitud de vacaciones será revisada y recibirás notificación por correo.</p>
                            ${chat.getActionButtons()}
                        `,
                        time: time
                    });
                    chat.scrollToBottom();
                }, 500);
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
