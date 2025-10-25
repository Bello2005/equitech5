-- NUEVO ESQUEMA DE BASE DE DATOS COMFACHOCO
-- Sistema de gestión de vacaciones y permisos mejorado

USE comfachoco;

-- 1. TABLA DE TIPOS DE EMPLEADO
CREATE TABLE IF NOT EXISTS tipos_empleado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. MODIFICAR TABLA DE USUARIOS para incluir tipo de empleado
ALTER TABLE usuarios
ADD COLUMN tipo_empleado_id INT DEFAULT NULL AFTER rol,
ADD COLUMN departamento VARCHAR(100) DEFAULT NULL AFTER tipo_empleado_id,
ADD COLUMN fecha_ingreso DATE DEFAULT NULL AFTER departamento,
ADD COLUMN dias_vacaciones_acumulados DECIMAL(5,2) DEFAULT 0 AFTER fecha_ingreso,
ADD COLUMN periodos_acumulados INT DEFAULT 0 AFTER dias_vacaciones_acumulados,
ADD FOREIGN KEY (tipo_empleado_id) REFERENCES tipos_empleado(id) ON DELETE SET NULL;

-- 3. TABLA DE POLÍTICAS DE VACACIONES POR TIPO DE EMPLEADO
CREATE TABLE IF NOT EXISTS politicas_vacaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_empleado_id INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    dias_por_periodo DECIMAL(5,2) NOT NULL DEFAULT 15.00,
    periodo_dias INT DEFAULT 360 COMMENT 'Días para completar un periodo (360 = 1 año)',
    es_acumulable TINYINT(1) DEFAULT 1,
    max_periodos_acumulables INT DEFAULT 2,
    requiere_antiguedad_minima TINYINT(1) DEFAULT 0,
    antiguedad_minima_dias INT DEFAULT 0,
    observaciones TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_empleado_id) REFERENCES tipos_empleado(id) ON DELETE CASCADE,
    INDEX idx_tipo_empleado (tipo_empleado_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA DE REGLAS DE APROBACIÓN
CREATE TABLE IF NOT EXISTS reglas_aprobacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_permiso VARCHAR(50) NOT NULL COMMENT 'vacaciones, medico, maternidad, paternidad, otro',
    dias_minimos INT DEFAULT 0,
    dias_maximos INT DEFAULT NULL,
    aprobador_rol VARCHAR(50) NOT NULL COMMENT 'jefe_inmediato, rrhh, admin',
    requiere_documento TINYINT(1) DEFAULT 0,
    tipo_documento VARCHAR(100) DEFAULT NULL COMMENT 'certificado_medico, certificado_defuncion, etc',
    dias_anticipacion_minima INT DEFAULT 0 COMMENT 'Días de anticipación para solicitar',
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo_permiso),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABLA DE RESTRICCIONES DE DISPONIBILIDAD
CREATE TABLE IF NOT EXISTS restricciones_disponibilidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    departamento VARCHAR(100) DEFAULT NULL COMMENT 'NULL = aplica a todos',
    porcentaje_minimo_disponible DECIMAL(5,2) DEFAULT 70.00,
    max_simultaneos INT DEFAULT NULL COMMENT 'Máximo de personas del mismo dpto al mismo tiempo',
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_departamento (departamento),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. MODIFICAR TABLA SOLICITUDES para el nuevo esquema
ALTER TABLE solicitudes
DROP FOREIGN KEY IF EXISTS solicitudes_ibfk_2;

ALTER TABLE solicitudes
MODIFY COLUMN tipo ENUM('vacaciones','permiso_medico','maternidad','paternidad','duelo','jurado','cita_medica_hijo','otro') NOT NULL,
ADD COLUMN departamento VARCHAR(100) DEFAULT NULL AFTER usuario_id,
ADD COLUMN dias_habiles DECIMAL(5,2) DEFAULT NULL AFTER dias COMMENT 'Días hábiles (sábados cuentan)',
ADD COLUMN documento_adjunto VARCHAR(255) DEFAULT NULL AFTER motivo,
ADD COLUMN tipo_documento VARCHAR(100) DEFAULT NULL AFTER documento_adjunto,
ADD COLUMN aprobador_rol VARCHAR(50) DEFAULT NULL AFTER aprobador_id COMMENT 'jefe_inmediato, rrhh',
ADD COLUMN es_reemplazo TINYINT(1) DEFAULT 0 AFTER prioridad,
ADD COLUMN usuario_reemplazo_id INT DEFAULT NULL AFTER es_reemplazo,
ADD COLUMN validacion_disponibilidad TEXT DEFAULT NULL AFTER usuario_reemplazo_id COMMENT 'JSON con validaciones',
ADD COLUMN periodo_solicitud INT DEFAULT 1 AFTER estado COMMENT 'Período de vacaciones que está usando';

-- 7. TABLA DE HISTORIAL DE VACACIONES
CREATE TABLE IF NOT EXISTS historial_vacaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    periodo_inicio DATE NOT NULL,
    periodo_fin DATE NOT NULL,
    dias_ganados DECIMAL(5,2) NOT NULL,
    dias_usados DECIMAL(5,2) DEFAULT 0,
    dias_disponibles DECIMAL(5,2) NOT NULL,
    solicitud_id INT DEFAULT NULL COMMENT 'ID de solicitud que consumió días',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_periodo (periodo_inicio, periodo_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERTAR DATOS INICIALES

-- Tipos de empleado
INSERT INTO tipos_empleado (nombre, descripcion) VALUES
('Administrativo', 'Personal administrativo y de oficina'),
('Contador', 'Personal del área contable y financiera'),
('Servicios Generales', 'Personal de aseo, mantenimiento y servicios'),
('Técnico', 'Personal técnico especializado'),
('Gerencial', 'Personal de gerencia y dirección');

-- Políticas de vacaciones por tipo
INSERT INTO politicas_vacaciones (tipo_empleado_id, nombre, dias_por_periodo, periodo_dias, es_acumulable, max_periodos_acumulables, observaciones) VALUES
(1, 'Vacaciones personal administrativo', 15.00, 360, 1, 2, 'Se puede solicitar apenas cumpla el periodo. Los sábados cuentan como día hábil.'),
(2, 'Vacaciones contadores', 15.00, 360, 1, 2, 'Acumulables hasta 2 períodos. Sábados hábiles.'),
(3, 'Vacaciones servicios generales', 15.00, 360, 1, 2, 'Días acumulables. Máximo 2 períodos.'),
(4, 'Vacaciones técnicos', 15.00, 360, 1, 2, 'Acumulación de hasta 2 períodos.'),
(5, 'Vacaciones personal gerencial', 15.00, 360, 1, 2, 'Prioridad en selección de fechas.');

-- Reglas de aprobación
INSERT INTO reglas_aprobacion (tipo_permiso, dias_minimos, dias_maximos, aprobador_rol, requiere_documento, tipo_documento, dias_anticipacion_minima, descripcion) VALUES
-- Permisos cortos (<= 2 días) - Jefe inmediato
('permiso_medico', 0, 2, 'jefe_inmediato', 1, 'certificado_medico', 0, 'Permisos médicos de hasta 2 días requieren certificado y aprobación de jefe inmediato'),
('cita_medica_hijo', 0, 2, 'jefe_inmediato', 1, 'certificado_cita', 0, 'Citas médicas de hijos requieren certificado'),
('otro', 0, 2, 'jefe_inmediato', 0, NULL, 0, 'Otros permisos cortos con jefe inmediato'),

-- Permisos largos (>= 3 días) - RRHH
('permiso_medico', 3, NULL, 'rrhh', 1, 'certificado_medico', 0, 'Permisos médicos de 3+ días requieren aprobación de RRHH'),
('vacaciones', 1, NULL, 'rrhh', 0, NULL, 30, 'Vacaciones requieren 30 días de anticipación y aprobación de RRHH'),

-- Permisos especiales - RRHH
('maternidad', 0, NULL, 'rrhh', 1, 'certificado_medico', 0, 'Maternidad: 4 meses (120 días) con certificado'),
('paternidad', 0, NULL, 'rrhh', 1, 'certificado_nacimiento', 0, 'Paternidad: 15 días con certificado'),
('duelo', 0, NULL, 'rrhh', 1, 'certificado_defuncion', 0, 'Duelo: requiere certificado de defunción'),
('jurado', 0, NULL, 'rrhh', 1, 'citacion_judicial', 0, 'Servicio como jurado requiere citación');

-- Restricciones de disponibilidad
INSERT INTO restricciones_disponibilidad (nombre, departamento, porcentaje_minimo_disponible, max_simultaneos, descripcion) VALUES
('Disponibilidad mínima general', NULL, 70.00, NULL, 'El 70% del personal debe estar disponible en todo momento'),
('Restricción por departamento', NULL, NULL, 1, 'Máximo 1 persona del mismo departamento puede estar de vacaciones simultáneamente');

COMMIT;
