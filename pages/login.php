<?php
require_once __DIR__ . '/../config/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (authenticate($email, $password)) {
        // Redirigir al dashboard usando la ruta correcta
        header('Location: /Comfachoco/pages/dashboard.php');
        exit();
    } else {
        $error = 'Credenciales incorrectas. Por favor intenta de nuevo.';
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ComfaChoco International</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/tailwind-config.js"></script>
    <link href="../assets/css/variables.css" rel="stylesheet">
    <link href="../assets/css/components.css" rel="stylesheet">
    <link href="../assets/css/animations.css" rel="stylesheet">
</head>
<body class="h-full">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="fixed inset-0 gradient-bg opacity-5 pointer-events-none"></div>

        <div class="max-w-md w-full space-y-8 relative z-10">
            <!-- Logo y título -->
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <img src="../assets/images/logo-comfachoco-no-lema.svg"
                         alt="ComfaChoco Logo"
                         class="h-24 w-auto">
                </div>
                <h2 class="text-3xl font-bold text-gray-900">
                    ComfaChoco International
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Sistema de Gestión de Recursos Humanos
                </p>
            </div>

            <!-- Formulario de login -->
            <div class="bg-white rounded-2xl shadow-elegant border border-gray-100 p-8">
                <form class="space-y-6" method="POST" action="">
                    <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Correo electrónico
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" required
                                   class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                   placeholder="tu@email.com">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" required
                                   class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Recordarme
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="#" class="font-medium text-primary hover:text-primary-dark transition-colors">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Iniciar sesión
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500">Credenciales de prueba</span>
                        </div>
                    </div>

                    <div class="mt-4 bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <p class="text-xs text-gray-600 mb-2">
                            <strong>Email:</strong> admin@comfachoco.com
                        </p>
                        <p class="text-xs text-gray-600">
                            <strong>Contraseña:</strong> admin123
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-sm text-gray-500">
                    &copy; 2024 ComfaChoco International. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
