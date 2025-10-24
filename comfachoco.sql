-- Crear base de datos
CREATE DATABASE IF NOT EXISTS comfachoco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE comfachoco;

-- Tabla de tipos de permisos
CREATE TABLE IF NOT EXISTS tipos_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    dias_maximos INT DEFAULT NULL,
    requiere_documentacion BOOLEAN DEFAULT FALSE,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de políticas
CREATE TABLE IF NOT EXISTS politicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    categoria ENUM('vacaciones', 'permisos', 'teletrabajo', 'general') NOT NULL,
    aplicable_a ENUM('todos', 'gerentes', 'empleados') DEFAULT 'todos',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'gerente', 'empleado') DEFAULT 'empleado',
    avatar VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de solicitudes
CREATE TABLE IF NOT EXISTS solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_permiso_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    dias INT NOT NULL,
    motivo TEXT,
    documentacion_url VARCHAR(255) DEFAULT NULL,
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    prioridad ENUM('alta', 'media', 'baja') DEFAULT 'media',
    aprobador_id INT DEFAULT NULL,
    fecha_aprobacion TIMESTAMP NULL,
    comentarios TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (aprobador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (tipo_permiso_id) REFERENCES tipos_permisos(id) ON DELETE RESTRICT,
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de actividad
CREATE TABLE IF NOT EXISTS actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('aprobacion', 'solicitud', 'recordatorio', 'completado') NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de eventos del calendario
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    tipo ENUM('vacaciones', 'permiso', 'reunion', 'teletrabajo', 'evaluacion', 'otro') NOT NULL,
    usuario_id INT DEFAULT NULL,
    color VARCHAR(7) DEFAULT '#0B8A3A',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_fecha_inicio (fecha_inicio),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tipos de permisos predefinidos
INSERT INTO tipos_permisos (nombre, descripcion, dias_maximos, requiere_documentacion) VALUES
('Vacaciones anuales', 'Periodo de descanso remunerado anual', 15, FALSE),
('Permiso médico', 'Ausencia por razones de salud', NULL, TRUE),
('Licencia por maternidad', 'Permiso por nacimiento o adopción', 84, TRUE),
('Licencia por paternidad', 'Permiso por nacimiento o adopción', 10, TRUE),
('Teletrabajo', 'Trabajo remoto desde casa', NULL, FALSE),
('Capacitación', 'Ausencia por formación profesional', 5, TRUE),
('Duelo', 'Permiso por fallecimiento de familiar', 5, TRUE),
('Permiso personal', 'Ausencia por asuntos personales', 3, FALSE);

-- Insertar políticas predefinidas
INSERT INTO politicas (nombre, descripcion, categoria, aplicable_a) VALUES
('Política de vacaciones', 'Las vacaciones deben solicitarse con al menos 2 semanas de anticipación. Se debe mantener un mínimo de personal activo del 70% en cada departamento.', 'vacaciones', 'todos'),
('Política de teletrabajo', 'El teletrabajo debe ser aprobado por el supervisor directo. Se requiere conectividad y disponibilidad durante el horario laboral.', 'teletrabajo', 'todos'),
('Documentación médica', 'Los permisos médicos de más de 2 días requieren certificado médico oficial.', 'permisos', 'todos'),
('Límite de permisos personales', 'Máximo 3 días de permisos personales por trimestre.', 'permisos', 'empleados'),
('Aprobación de vacaciones', 'Los gerentes tienen prioridad en la selección de fechas de vacaciones.', 'vacaciones', 'gerentes'),
('Política de turnos', 'Debe mantenerse una cobertura mínima del 50% en cada área durante períodos vacacionales.', 'general', 'todos');

-- Insertar usuario administrador por defecto
-- Contraseña: admin123
INSERT INTO usuarios (nombre, email, password, rol, avatar, activo)
VALUES (
    'Bello González',
    'admin@comfachoco.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80',
    1
);

-- Insertar usuarios de prueba
INSERT INTO usuarios (nombre, email, password, rol, activo)
VALUES
('Carlos Ruiz', 'carlos.ruiz@comfachoco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 1),
('Ana Mendoza', 'ana.mendoza@comfachoco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 1),
('David Torres', 'david.torres@comfachoco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 1),
('Laura Silva', 'laura.silva@comfachoco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gerente', 1),
('Miguel Santos', 'miguel.santos@comfachoco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 1);

-- Insertar solicitudes de prueba
INSERT INTO solicitudes (usuario_id, tipo, fecha_inicio, fecha_fin, dias, motivo, estado, prioridad)
VALUES
(2, 'vacaciones', '2024-01-15', '2024-01-20', 5, 'Vacaciones familiares', 'pendiente', 'alta'),
(3, 'permiso_medico', '2024-01-12', '2024-01-14', 2, 'Consulta médica', 'aprobado', 'media'),
(4, 'teletrabajo', '2024-01-10', '2024-01-10', 1, 'Trabajo remoto', 'rechazado', 'baja'),
(5, 'vacaciones', '2024-01-08', '2024-01-18', 10, 'Vacaciones de verano', 'pendiente', 'alta'),
(6, 'capacitacion', '2024-01-05', '2024-01-08', 3, 'Curso de liderazgo', 'aprobado', 'media');

-- Insertar actividad de prueba
INSERT INTO actividad (usuario_id, tipo, descripcion)
VALUES
(1, 'aprobacion', 'Aprobó solicitud de vacaciones'),
(2, 'solicitud', 'Envió nueva solicitud de permisos'),
(1, 'recordatorio', 'Recordatorio: Revisión trimestral'),
(3, 'completado', 'Completó evaluación de desempeño');

-- Insertar eventos del calendario
INSERT INTO eventos (titulo, descripcion, fecha_inicio, fecha_fin, tipo, usuario_id, color)
VALUES
('Carlos - Vacaciones', 'Vacaciones familiares', '2024-01-15', '2024-01-20', 'vacaciones', 2, '#0B8A3A'),
('Ana - Permiso médico', 'Consulta médica', '2024-01-12', '2024-01-14', 'permiso', 3, '#FFD400'),
('Reunión equipo RH', 'Reunión mensual del equipo', '2024-01-10', '2024-01-10', 'reunion', NULL, '#3B82F6'),
('David - Teletrabajo', 'Trabajo remoto', '2024-01-10', '2024-01-10', 'teletrabajo', 4, '#8B5CF6'),
('Evaluaciones trimestrales', 'Evaluaciones de desempeño del equipo', '2024-01-20', '2024-01-20', 'evaluacion', NULL, '#F59E0B');
