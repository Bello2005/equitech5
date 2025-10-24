-- Script para agregar un usuario empleado de prueba
-- Usar este script si necesitas agregar el usuario empleado

USE comfachoco;

-- Insertar usuario empleado de prueba
-- Email: empleado@comfachoco.com
-- Contraseña: empleado123
INSERT INTO usuarios (nombre, email, password, rol, avatar, activo)
VALUES (
    'Juan Pérez',
    'empleado@comfachoco.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'empleado',
    'https://ui-avatars.com/api/?name=Juan+Perez&background=0B8A3A&color=fff&size=128',
    1
)
ON DUPLICATE KEY UPDATE
    nombre = 'Juan Pérez',
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    rol = 'empleado',
    activo = 1;

-- Verificar que el usuario fue creado
SELECT id, nombre, email, rol FROM usuarios WHERE email = 'empleado@comfachoco.com';
