<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$error = '';

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
                    // Credenciales válidas - Crear sesión
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
?>

<!DOCTYPE html>
<html lang="es" class="h-full bg-white">
<head>
    <?php require_once __DIR__ . '/../includes/head.php'; ?>
    <title>Login Administrador - ComfaChocó</title>
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-10 w-auto" src="../assets/images/logo.png" alt="ComfaChocó">
            <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Login Administrador</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <?php if (!empty($error)): ?>
                <div class="rounded-md bg-red-50 p-4 mb-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800"><?php echo htmlspecialchars($error); ?></h3>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="space-y-6" method="POST">
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Contraseña</label>
                    <div class="mt-2">
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>