<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'] ?? '';
    $primer_nombre = $_POST['primer-nombre'] ?? '';
    $primer_apellido = $_POST['primer-apellido'] ?? '';

    // Crear sesión automáticamente sin validar credenciales
    if (!empty($cedula) && !empty($primer_nombre) && !empty($primer_apellido)) {
        // Crear sesión con los datos proporcionados
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = "$primer_nombre $primer_apellido";
        $_SESSION['user_email'] = strtolower($primer_nombre) . '.' . strtolower($primer_apellido) . '@comfachoco.com';
        $_SESSION['user_role'] = 'empleado';
        $_SESSION['user_avatar'] = '';
        $_SESSION['user_empresa'] = 'ComfaChoco International';
        $_SESSION['cedula'] = $cedula;
        
        // Redirigir al dashboard de empleado
        header('Location: empleado_dashboard.php');
        exit();
    } else {
        $error = 'Por favor completa todos los campos.';
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
    <style>
        :root {
            --primary: #0B8A3A;
            --primary-dark: #00582A;
            --accent: #FFD400;
            --accent-dark: #E6BF00;
        }

        .login-container {
            width: 100%;
            max-width: 380px;
        }

        .login-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 30px 24px;
            text-align: center;
            color: white;
        }

        .login-header h3 {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            opacity: 0.95;
            line-height: 1.4;
        }

        .login-header p {
            font-size: 13px;
            opacity: 0.85;
            font-weight: 300;
        }

        .login-form {
            padding: 32px 24px;
            position: relative;
            transition: min-height 0.4s ease-out;
        }

        .login-form.step-1 {
            min-height: 200px;
        }

        .login-form.step-2,
        .login-form.step-3 {
            min-height: 280px;
        }

        .login-form.step-4 {
            min-height: 200px;
        }

        .form-step {
            display: none;
            animation: slideIn 0.3s ease-out;
        }

        .form-step.active {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #666;
        }

        .step-number {
            background: var(--primary);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .step-total {
            color: #999;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group label i {
            color: var(--primary);
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #999;
            font-size: 14px;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(11, 138, 58, 0.1);
        }

        .form-group input:focus + .input-icon,
        .form-group input:focus ~ .input-icon {
            color: var(--primary);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .btn-next, .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 6px;
            box-shadow: 0 3px 10px rgba(11, 138, 58, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-next:hover, .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11, 138, 58, 0.4);
        }

        .btn-next:active, .btn-submit:active {
            transform: translateY(0);
        }

        .btn-next:focus, .btn-submit:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(11, 138, 58, 0.3);
        }

        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-submit.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .welcome-step {
            text-align: center;
        }

        .welcome-content {
            padding: 20px 0;
        }

        .welcome-icon {
            font-size: 64px;
            color: var(--primary);
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .welcome-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .welcome-message {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }

        .btn-back {
            width: 100%;
            padding: 10px;
            background: transparent;
            color: #666;
            border: 1.5px solid #e0e0e0;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: #f8f9fa;
            border-color: #ccc;
            color: #333;
        }

        .login-footer {
            padding: 16px 24px;
            text-align: center;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .login-footer p {
            font-size: 13px;
            color: #666;
        }

        .help-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .help-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-header {
                padding: 24px 20px;
            }
            
            .login-header h3 {
                font-size: 13px;
            }
            
            .login-form {
                padding: 28px 20px;
            }
            
            .login-form.step-1 {
                min-height: 180px;
            }
            
            .login-form.step-2,
            .login-form.step-3 {
                min-height: 240px;
            }
            
            .login-form.step-4 {
                min-height: 180px;
            }
        }
    </style>
</head>
<body class="h-full">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="fixed inset-0 gradient-bg opacity-5 pointer-events-none"></div>

        <div class="login-container">
            <div class="login-box">
                <div class="login-header">
                    <img src="../assets/images/logo-comfachoco-no-lema.svg"
                         alt="ComfaChoco Logo"
                         class="h-16 w-auto mx-auto mb-4">
                    <h3>Sistema de gestión de permisos y licencias.</h3>
                <p>Ingresa tus datos para acceder</p>

                </div>
                
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 mx-4 mt-4 rounded-xl" role="alert">
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

                <form class="login-form step-1" action="" method="POST" id="loginForm">
                    <!-- Paso 1: Cédula -->
                    <div class="form-step active" data-step="1">
                        <div class="step-indicator">
                            <span class="step-number">1</span>
                            <span class="step-total">de 3</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="cedula">
                                <i class="fas fa-id-card"></i>
                                Número de cédula
                            </label>
                            <div class="input-wrapper">
                                <i class="fas fa-id-card input-icon"></i>
                                <input 
                                    type="text" 
                                    id="cedula" 
                                    name="cedula" 
                                    placeholder="Ingresa tu número de cédula"
                                    required
                                    value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>"
                                >
                            </div>
                        </div>
                        
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Continuar
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                    <!-- Paso 2: Primer Nombre -->
                    <div class="form-step" data-step="2">
                        <div class="step-indicator">
                            <span class="step-number">2</span>
                            <span class="step-total">de 3</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="primer-nombre">
                                <i class="fas fa-user"></i>
                                Primer nombre
                            </label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input 
                                    type="text" 
                                    id="primer-nombre" 
                                    name="primer-nombre" 
                                    placeholder="Ingresa tu primer nombre"
                                    required
                                    value="<?= htmlspecialchars($_POST['primer-nombre'] ?? '') ?>"
                                >
                            </div>
                        </div>
                        
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Continuar
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        
                        <button type="button" class="btn-back" onclick="previousStep()">
                            <i class="fas fa-arrow-left"></i>
                            Volver
                        </button>
                    </div>

                    <!-- Paso 3: Primer Apellido -->
                    <div class="form-step" data-step="3">
                        <div class="step-indicator">
                            <span class="step-number">3</span>
                            <span class="step-total">de 3</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="primer-apellido">
                                <i class="fas fa-signature"></i>
                                Primer apellido
                            </label>
                            <div class="input-wrapper">
                                <i class="fas fa-signature input-icon"></i>
                                <input 
                                    type="text" 
                                    id="primer-apellido" 
                                    name="primer-apellido" 
                                    placeholder="Ingresa tu primer apellido"
                                    required
                                    value="<?= htmlspecialchars($_POST['primer-apellido'] ?? '') ?>"
                                >
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-sign-in-alt"></i>
                            Iniciar sesión
                        </button>
                        
                        <button type="button" class="btn-back" onclick="previousStep()">
                            <i class="fas fa-arrow-left"></i>
                            Volver
                        </button>
                    </div>
                </form>
                
                <div class="login-footer">
                    <p>¿Necesitas ayuda? <a href="#" class="help-link">Contáctanos</a></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-500">
                    &copy; 2024 ComfaChoco International. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function showStep(step) {
            // Ocultar todos los pasos
            document.querySelectorAll('.form-step').forEach(stepElement => {
                stepElement.classList.remove('active');
            });
            
            // Mostrar el paso actual
            const currentStepElement = document.querySelector(`[data-step="${step}"]`);
            if (currentStepElement) {
                currentStepElement.classList.add('active');
            }
            
            // Actualizar las clases del formulario para controlar la altura
            const loginForm = document.querySelector('.login-form');
            loginForm.classList.remove('step-1', 'step-2', 'step-3', 'step-4');
            loginForm.classList.add(`step-${step}`);
        }

        function nextStep() {
            const currentInput = getCurrentInput();
            
            if (currentInput) {
                // Validar el campo actual
                if (!currentInput.checkValidity()) {
                    currentInput.reportValidity();
                    return;
                }
                
                // Si es válido, avanzar al siguiente paso
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                    
                    // Enfocar el siguiente campo
                    setTimeout(() => {
                        const nextInput = getCurrentInput();
                        if (nextInput) {
                            nextInput.focus();
                        }
                    }, 300);
                }
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
                
                // Enfocar el campo anterior
                setTimeout(() => {
                    const currentInput = getCurrentInput();
                    if (currentInput) {
                        currentInput.focus();
                    }
                }, 300);
            }
        }

        function getCurrentInput() {
            const stepElement = document.querySelector(`[data-step="${currentStep}"]`);
            if (stepElement) {
                return stepElement.querySelector('input');
            }
            return null;
        }

        // Manejar el envío del formulario
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // Validar todos los campos
            const inputs = this.querySelectorAll('input');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Mostrar el primer campo con error
                inputs.forEach(input => {
                    if (!input.checkValidity()) {
                        const step = input.closest('.form-step').dataset.step;
                        currentStep = parseInt(step);
                        showStep(currentStep);
                        input.focus();
                        input.reportValidity();
                        return;
                    }
                });
            }
        });

        // Permitir avanzar con Enter
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const currentInput = getCurrentInput();
                if (currentInput && currentInput.value.trim() !== '') {
                    e.preventDefault();
                    nextStep();
                }
            }
        });

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);
        });
    </script>
</body>
</html>