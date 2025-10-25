<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $conn->prepare("
                SELECT u.id, u.nombre, u.email, u.rol, u.departamento,
                       u.avatar, u.activo, u.tipo_empleado_id, u.fecha_ingreso,
                       u.cedula, u.primer_nombre, u.primer_apellido, u.password,
                       te.nombre as tipo_empleado_nombre
                FROM usuarios u
                LEFT JOIN tipos_empleado te ON te.id = u.tipo_empleado_id
                WHERE u.email = ?
                  AND u.rol = 'admin'
                  AND u.activo = 1
            ");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                
                if (password_verify($password, $usuario['password'])) {
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['user_name'] = $usuario['nombre'];
                    $_SESSION['user_email'] = $usuario['email'];
                    $_SESSION['user_role'] = $usuario['rol'];
                    $_SESSION['user_avatar'] = $usuario['avatar'] ?? '';
                    $_SESSION['user_empresa'] = 'ComfaChoco International';
                    $_SESSION['departamento'] = $usuario['departamento'];
                    $_SESSION['tipo_empleado_id'] = $usuario['tipo_empleado_id'];
                    $_SESSION['tipo_empleado_nombre'] = $usuario['tipo_empleado_nombre'];
                    $_SESSION['fecha_ingreso'] = $usuario['fecha_ingreso'];
                    $_SESSION['cedula'] = $usuario['cedula'];
                    $_SESSION['primer_nombre'] = $usuario['primer_nombre'];
                    $_SESSION['primer_apellido'] = $usuario['primer_apellido'];

                    header('Location: dashboard.php');
                    exit;
                }
            }
            $error = 'Credenciales inválidas';
        } catch (Exception $e) {
            $error = 'Error al procesar la solicitud';
        }
    } else {
        $error = 'Por favor complete todos los campos';
    }
}
?><!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <?php require_once __DIR__ . '/../includes/head.php'; ?>
    <title>Portal Administrativo - ComfaChocó</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0B8A3A 0%, #00582A 100%);
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }
        
        .custom-shadow {
            box-shadow: 0 20px 50px rgba(0, 88, 42, 0.1);
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        
        .animate-scale {
            animation: scale 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes scale {
            from { transform: scale(0.95); }
            to { transform: scale(1); }
        }
        
        .bg-gradient {
            background: var(--primary-gradient);
        }
        
        .floating {
            animation: floating 6s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .bg-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(circle at center, #0B8A3A 1px, transparent 1px);
            background-size: 30px 30px;
            background-position: 0 0, 15px 15px;
            opacity: 0.05;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .input-icon {
            top: 50%;
            transform: translateY(-50%);
        }
        
        .hover-scale {
            transition: transform 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="h-full overflow-hidden">
    <!-- Fondo con patrón y gradiente -->
    <div class="fixed inset-0 bg-pattern"></div>
    <div class="fixed inset-0 bg-gradient opacity-10"></div>
    
    <!-- Contenedor principal -->
    <div class="relative min-h-screen flex items-center justify-center p-4 mx-auto container">
        <!-- Círculos decorativos animados -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary opacity-5 rounded-full filter blur-3xl animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-accent opacity-5 rounded-full filter blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        </div>
        
        <!-- Contenedor del formulario -->
        <div class="login-container max-w-md w-full mx-auto space-y-8 p-12 rounded-3xl custom-shadow animate-fade-in glass-effect hover-scale transform-gpu">
            <!-- Logo y Encabezado -->
            <div class="text-center space-y-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center justify-center filter blur-sm opacity-50">
                        <img class="h-28 w-auto" src="../assets/images/logo-comfachoco-no-lema.svg" alt="">
                    </div>
                    <img class="relative mx-auto h-28 w-auto floating" 
                         src="../assets/images/logo-comfachoco-no-lema.svg" 
                         alt="ComfaChocó">
                </div>
                <div class="space-y-3">
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight animate-scale">
                        Portal Administrativo
                    </h2>
                    <p class="text-sm text-gray-600">
                        Sistema de Gestión y Control
                    </p>
                </div>
            </div>

            <!-- Mensaje de Error -->
            <?php if (!empty($error)): ?>
            <div class="animate-fade-in rounded-lg bg-red-50 p-4 border-l-4 border-red-400">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form class="mt-8 space-y-6" method="POST">
                <div class="space-y-5">
                    <div class="space-y-1">
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Correo Electrónico
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200 input-icon">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                            </div>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   autocomplete="email" 
                                   required 
                                   class="pl-12 block w-full rounded-xl border-gray-200 bg-white/70 focus:bg-white focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 text-sm shadow-sm"
                                   placeholder="admin@comfachoco.com">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Contraseña
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200 input-icon">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="current-password" 
                                   required 
                                   class="pl-12 block w-full rounded-xl border-gray-200 bg-white/70 focus:bg-white focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 text-sm shadow-sm"
                                   placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <div class="flex items-center">
                        <input id="remember_me" 
                               name="remember_me" 
                               type="checkbox"
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded transition-colors duration-200">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-600 select-none">
                            Recordarme
                        </label>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient shadow-lg shadow-primary/30 hover:shadow-primary/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 hover:-translate-y-0.5">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-white/90 group-hover:text-white transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M14.595 10.5a.75.75 0 01-.147.847l-4.5 4.5a.75.75 0 11-1.061-1.061l3.72-3.72H4.75a.75.75 0 010-1.5h7.857l-3.72-3.72a.75.75 0 111.06-1.06l4.5 4.5a.75.75 0 01.148.213z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Iniciar Sesión
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="mt-6">
                <p class="text-center text-xs text-gray-600">
                    © <?php echo date('Y'); ?> ComfaChocó. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</body>
</html>