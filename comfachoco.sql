-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 25-10-2025 a las 16:47:11
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `comfachoco`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad`
--

CREATE TABLE `actividad` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('aprobacion','solicitud','recordatorio','completado') NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `actividad`
--

INSERT INTO `actividad` (`id`, `usuario_id`, `tipo`, `descripcion`, `fecha_creacion`) VALUES
(1, 1, 'aprobacion', 'Aprobó solicitud de vacaciones', '2025-10-24 07:22:09'),
(2, 2, 'solicitud', 'Envió nueva solicitud de permisos', '2025-10-24 07:22:09'),
(3, 1, 'recordatorio', 'Recordatorio: Revisión trimestral', '2025-10-24 07:22:09'),
(4, 3, 'completado', 'Completó evaluación de desempeño', '2025-10-24 07:22:09'),
(5, 2, 'aprobacion', 'Aprobó solicitud ID 1', '2025-10-24 23:03:45'),
(6, 2, 'aprobacion', 'Rechazó solicitud ID 4', '2025-10-24 23:36:27'),
(7, 1, 'solicitud', 'Envió nueva solicitud ID 6', '2025-10-25 02:09:07'),
(8, 1, 'aprobacion', 'Rechazó solicitud ID 13', '2025-10-25 13:05:12'),
(9, 1, 'aprobacion', 'Rechazó solicitud ID 12', '2025-10-25 13:05:15'),
(10, 1, 'aprobacion', 'Rechazó solicitud ID 14', '2025-10-25 13:17:31'),
(11, 1, 'aprobacion', 'Creó nuevo empleado: Deiner Bello (deiner.bello@comfachoco.com)', '2025-10-25 14:42:59'),
(12, 1, 'aprobacion', 'Eliminó empleado: Deiner Bello (deiner.bello@comfachoco.com)', '2025-10-25 14:46:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `tipo` enum('vacaciones','permiso','reunion','teletrabajo','evaluacion','otro') NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#0B8A3A',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_fin`, `tipo`, `usuario_id`, `color`, `fecha_creacion`) VALUES
(1, 'Carlos - Vacaciones', 'Vacaciones familiares', '2024-01-15', '2024-01-20', 'vacaciones', 2, '#0B8A3A', '2025-10-24 07:22:09'),
(2, 'Ana - Permiso médico', 'Consulta médica', '2024-01-12', '2024-01-14', 'permiso', 3, '#FFD400', '2025-10-24 07:22:09'),
(3, 'Reunión equipo RH', 'Reunión mensual del equipo', '2024-01-10', '2024-01-10', 'reunion', NULL, '#3B82F6', '2025-10-24 07:22:09'),
(4, 'David - Teletrabajo', 'Trabajo remoto', '2024-01-10', '2024-01-10', 'teletrabajo', 4, '#8B5CF6', '2025-10-24 07:22:09'),
(5, 'Evaluaciones trimestrales', 'Evaluaciones de desempeño del equipo', '2024-01-20', '2024-01-20', 'evaluacion', NULL, '#F59E0B', '2025-10-24 07:22:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_vacaciones`
--

CREATE TABLE `historial_vacaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `dias_ganados` decimal(5,2) NOT NULL,
  `dias_usados` decimal(5,2) DEFAULT 0.00,
  `dias_disponibles` decimal(5,2) NOT NULL,
  `solicitud_id` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `politicas`
--

CREATE TABLE `politicas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `categoria` enum('vacaciones','permisos','teletrabajo','general') NOT NULL,
  `aplicable_a` enum('todos','gerentes','empleados') DEFAULT 'todos',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `politicas`
--

INSERT INTO `politicas` (`id`, `nombre`, `descripcion`, `categoria`, `aplicable_a`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Política de vacaciones', 'Las vacaciones deben solicitarse con al menos 2 semanas de anticipación. Se debe mantener un mínimo de personal activo del 70% en cada departamento.', 'vacaciones', 'todos', 1, '2025-10-24 23:00:26', '2025-10-24 23:00:26'),
(2, 'Política de teletrabajo', 'El teletrabajo debe ser aprobado por el supervisor directo. Se requiere conectividad y disponibilidad durante el horario laboral.', 'teletrabajo', 'todos', 1, '2025-10-24 23:00:26', '2025-10-24 23:00:26'),
(3, 'Documentación médica', 'Los permisos médicos de más de 2 días requieren certificado médico oficial.', 'permisos', 'todos', 1, '2025-10-24 23:00:26', '2025-10-24 23:00:26'),
(4, 'Límite de permisos personales', 'Máximo 3 días de permisos personales por trimestre.', 'permisos', 'empleados', 1, '2025-10-24 23:00:26', '2025-10-24 23:00:26'),
(5, 'Aprobación de vacaciones', 'Los gerentes tienen prioridad en la selección de fechas de vacaciones.', 'vacaciones', 'gerentes', 1, '2025-10-24 23:00:26', '2025-10-24 23:00:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `politicas_vacaciones`
--

CREATE TABLE `politicas_vacaciones` (
  `id` int(11) NOT NULL,
  `tipo_empleado_id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `dias_por_periodo` decimal(5,2) NOT NULL DEFAULT 15.00,
  `periodo_dias` int(11) DEFAULT 360 COMMENT 'Días para completar un periodo (360 = 1 año)',
  `es_acumulable` tinyint(1) DEFAULT 1,
  `max_periodos_acumulables` int(11) DEFAULT 2,
  `requiere_antiguedad_minima` tinyint(1) DEFAULT 0,
  `antiguedad_minima_dias` int(11) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `politicas_vacaciones`
--

INSERT INTO `politicas_vacaciones` (`id`, `tipo_empleado_id`, `nombre`, `dias_por_periodo`, `periodo_dias`, `es_acumulable`, `max_periodos_acumulables`, `requiere_antiguedad_minima`, `antiguedad_minima_dias`, `observaciones`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 'Vacaciones personal administrativo', 15.00, 360, 1, 2, 0, 0, 'Se puede solicitar apenas cumpla el periodo. Los sábados cuentan como día hábil.', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16'),
(2, 2, 'Vacaciones contadores', 15.00, 360, 1, 2, 0, 0, 'Acumulables hasta 2 períodos. Sábados hábiles.', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16'),
(3, 3, 'Vacaciones servicios generales', 15.00, 360, 1, 2, 0, 0, 'Días acumulables. Máximo 2 períodos.', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16'),
(4, 4, 'Vacaciones técnicos', 15.00, 360, 1, 2, 0, 0, 'Acumulación de hasta 2 períodos.', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16'),
(5, 5, 'Vacaciones personal gerencial', 15.00, 360, 1, 2, 0, 0, 'Prioridad en selección de fechas.', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reglas_aprobacion`
--

CREATE TABLE `reglas_aprobacion` (
  `id` int(11) NOT NULL,
  `tipo_permiso` varchar(50) NOT NULL COMMENT 'vacaciones, medico, maternidad, paternidad, otro',
  `dias_minimos` int(11) DEFAULT 0,
  `dias_maximos` int(11) DEFAULT NULL,
  `aprobador_rol` varchar(50) NOT NULL COMMENT 'jefe_inmediato, rrhh, admin',
  `requiere_documento` tinyint(1) DEFAULT 0,
  `tipo_documento` varchar(100) DEFAULT NULL COMMENT 'certificado_medico, certificado_defuncion, etc',
  `dias_anticipacion_minima` int(11) DEFAULT 0 COMMENT 'Días de anticipación para solicitar',
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reglas_aprobacion`
--

INSERT INTO `reglas_aprobacion` (`id`, `tipo_permiso`, `dias_minimos`, `dias_maximos`, `aprobador_rol`, `requiere_documento`, `tipo_documento`, `dias_anticipacion_minima`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(19, 'vacaciones', 1, 999, 'rrhh', 0, NULL, 30, 'Vacaciones: 15 días hábiles por periodo (1 año). Acumulables hasta 2 periodos. Se solicita inmediatamente al cumplir el periodo, pero debe tomarse con 30 días de anticipación', 1, '2025-10-25 04:06:04'),
(20, 'permiso_general', 0, 2, 'jefe_inmediato', 0, NULL, 0, 'Permisos de 0-2 días: Aprueba jefe inmediato', 1, '2025-10-25 04:06:04'),
(21, 'permiso_general', 3, 999, 'rrhh', 0, NULL, 0, 'Permisos de 3+ días: Aprueba RRHH', 1, '2025-10-25 04:06:04'),
(22, 'permiso_medico', 0, 2, 'jefe_inmediato', 1, 'ordenes_medicas_y_anexos', 0, 'Permisos médicos 0-2 días con jefe inmediato. Requiere órdenes médicas y todos los anexos relacionados', 1, '2025-10-25 04:06:04'),
(23, 'permiso_medico', 3, 999, 'rrhh', 1, 'ordenes_medicas_y_anexos', 0, 'Permisos médicos 3+ días con RRHH. Requiere órdenes médicas y todos los anexos relacionados', 1, '2025-10-25 04:06:04'),
(24, 'maternidad', 0, 120, 'rrhh', 1, 'certificado_medico', 0, 'Maternidad: 4 meses (120 días). Requiere certificado', 1, '2025-10-25 04:06:04'),
(25, 'paternidad', 0, 15, 'rrhh', 1, 'registro_civil', 0, 'Paternidad: 15 días. Requiere certificado', 1, '2025-10-25 04:06:04'),
(26, 'cita_medica_hijo', 0, 2, 'jefe_inmediato', 1, 'cita_medica', 0, 'Padre acompañando hijo a cita médica. Requiere documento de cita', 1, '2025-10-25 04:06:04'),
(27, 'duelo', 0, 5, 'jefe_inmediato', 1, 'certificado_defuncion', 0, 'Muerte de familiar. Requiere certificado de defunción', 1, '2025-10-25 04:06:04'),
(28, 'jurado', 0, 999, 'rrhh', 1, 'documento_acreditativo', 0, 'Permiso por ser miembro o jurado. Requiere documento acreditativo', 1, '2025-10-25 04:06:04'),
(29, 'otro', 0, 2, 'jefe_inmediato', 1, 'anexo_correspondiente', 0, 'Otras causas: se adjunta el anexo correspondiente', 1, '2025-10-25 04:06:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restricciones_disponibilidad`
--

CREATE TABLE `restricciones_disponibilidad` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `departamento` varchar(100) DEFAULT NULL COMMENT 'NULL = aplica a todos',
  `porcentaje_minimo_disponible` decimal(5,2) DEFAULT 70.00,
  `max_simultaneos` int(11) DEFAULT NULL COMMENT 'Máximo de personas del mismo dpto al mismo tiempo',
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restricciones_disponibilidad`
--

INSERT INTO `restricciones_disponibilidad` (`id`, `nombre`, `departamento`, `porcentaje_minimo_disponible`, `max_simultaneos`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'Disponibilidad mínima general', NULL, 70.00, NULL, 'El 70% del personal debe estar disponible', 1, '2025-10-24 23:46:16'),
(2, 'Un empleado por departamento', NULL, NULL, 1, 'Máximo 1 por departamento simultáneamente', 1, '2025-10-24 23:46:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dias` int(11) NOT NULL,
  `dias_habiles` decimal(5,2) DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `documento_adjunto` varchar(260) DEFAULT NULL,
  `tipo_documento` varchar(100) DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `prioridad` enum('alta','media','baja') DEFAULT 'media',
  `es_reemplazo` tinyint(1) DEFAULT 0,
  `usuario_reemplazo_id` int(11) DEFAULT NULL,
  `validacion_disponibilidad` text DEFAULT NULL,
  `periodo_solicitud` int(11) DEFAULT 1,
  `aprobador_id` int(11) DEFAULT NULL,
  `aprobador_rol` varchar(50) DEFAULT NULL,
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id`, `usuario_id`, `departamento`, `tipo`, `fecha_inicio`, `fecha_fin`, `dias`, `dias_habiles`, `motivo`, `documento_adjunto`, `tipo_documento`, `estado`, `prioridad`, `es_reemplazo`, `usuario_reemplazo_id`, `validacion_disponibilidad`, `periodo_solicitud`, `aprobador_id`, `aprobador_rol`, `fecha_aprobacion`, `comentarios`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(12, 2, NULL, 'maternidad', '2025-08-12', '2026-02-12', 159, 159.00, 'Maternidad', NULL, NULL, 'rechazado', 'media', 0, NULL, NULL, 1, 1, NULL, '2025-10-25 13:05:15', NULL, '2025-10-25 12:48:23', '2025-10-25 13:05:15'),
(13, 2, NULL, 'permiso_medico', '2025-08-10', '2025-12-12', 107, 107.00, 'me siento enfermo', NULL, NULL, 'rechazado', 'media', 0, NULL, NULL, 1, 1, NULL, '2025-10-25 13:05:12', NULL, '2025-10-25 12:58:30', '2025-10-25 13:05:12'),
(14, 2, NULL, 'otro', '2025-08-10', '2025-08-25', 13, 13.00, 'necesito descansar, la u me está matando', NULL, NULL, 'rechazado', 'media', 0, NULL, NULL, 1, 1, NULL, '2025-10-25 13:17:31', NULL, '2025-10-25 13:12:35', '2025-10-25 13:17:31'),
(15, 1, NULL, 'maternidad', '2025-08-10', '2026-01-12', 133, 133.00, 'Maternidad', NULL, NULL, 'pendiente', 'media', 0, NULL, NULL, 1, 3, NULL, NULL, NULL, '2025-10-25 13:21:59', '2025-10-25 13:21:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_empleado`
--

CREATE TABLE `tipos_empleado` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_empleado`
--

INSERT INTO `tipos_empleado` (`id`, `nombre`, `descripcion`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Administrativo', 'Personal administrativo y de oficina', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16'),
(2, 'Contador', 'Personal del área contable y financiera', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16'),
(3, 'Servicios Generales', 'Personal de aseo, mantenimiento y servicios', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16'),
(4, 'Técnico', 'Personal técnico especializado', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16'),
(5, 'Gerencial', 'Personal de gerencia y dirección', 1, '2025-10-24 23:46:16', '2025-10-24 23:46:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_permisos`
--

CREATE TABLE `tipos_permisos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `dias_maximos` int(11) DEFAULT NULL,
  `requiere_documentacion` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_permisos`
--

INSERT INTO `tipos_permisos` (`id`, `nombre`, `descripcion`, `dias_maximos`, `requiere_documentacion`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Vacaciones anuales', 'Periodo de descanso remunerado anual', 15, 0, 1, '2025-10-24 23:00:36', '2025-10-24 23:00:36'),
(2, 'Permiso médico', 'Ausencia por razones de salud', NULL, 1, 1, '2025-10-24 23:00:36', '2025-10-24 23:00:36'),
(3, 'Licencia por maternidad', 'Permiso por nacimiento o adopción', 84, 1, 1, '2025-10-24 23:00:36', '2025-10-24 23:00:36'),
(4, 'Licencia por paternidad', 'Permiso por nacimiento o adopción', 10, 1, 1, '2025-10-24 23:00:36', '2025-10-24 23:00:36'),
(5, 'Teletrabajo', 'Trabajo remoto desde casa', NULL, 0, 1, '2025-10-24 23:00:36', '2025-10-24 23:00:36'),
(6, 'Capacitación', 'Ausencia por formación profesional', 5, 1, 1, '2025-10-24 23:00:36', '2025-10-24 23:00:36'),
(7, 'Duelo', 'Permiso por fallecimiento de familiar', 5, 1, 1, '2025-10-24 23:00:36', '2025-10-24 23:00:36'),
(8, 'Permiso personal', 'Ausencia por asuntos personales', 3, 0, 1, '2025-10-24 23:00:36', '2025-10-24 23:00:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `primer_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','gerente','empleado') DEFAULT 'empleado',
  `tipo_empleado_id` int(11) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `dias_vacaciones_acumulados` decimal(5,2) DEFAULT 0.00,
  `periodos_acumulados` int(11) DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `cedula`, `nombre`, `primer_nombre`, `primer_apellido`, `email`, `password`, `rol`, `tipo_empleado_id`, `departamento`, `fecha_ingreso`, `dias_vacaciones_acumulados`, `periodos_acumulados`, `avatar`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, '1000001', 'Bello González', 'Bello', 'González', 'admin@comfachoco.com', '$2y$10$OcSn3FEnBv2.LnpDmIldOO4jLNB7Y2zz04i5k2Q45g4DxktZ9Uqjm', 'admin', 1, 'Administración', '2024-10-24', 0.00, 0, 'https://ezink.co/cdn/shop/files/Markofsacrificetemporarytattoo.png?v=1757310036&width=1445', 1, '2025-10-24 07:22:09', '2025-10-25 02:50:17'),
(2, '1000002', 'Carlos Ruiz', 'Carlos', 'Ruiz', 'carlos.ruiz@comfachoco.com', '$2y$10$OcSn3FEnBv2.LnpDmIldOO4jLNB7Y2zz04i5k2Q45g4DxktZ9Uqjm', 'empleado', 1, 'Desarrollo', '2023-10-24', 0.00, 0, NULL, 1, '2025-10-24 07:22:09', '2025-10-25 02:50:17'),
(3, '1000003', 'Ana Mendoza', 'Ana', 'Mendoza', 'ana.mendoza@comfachoco.com', '$2y$10$OcSn3FEnBv2.LnpDmIldOO4jLNB7Y2zz04i5k2Q45g4DxktZ9Uqjm', 'empleado', 1, 'Diseño', '2024-10-24', 0.00, 0, NULL, 1, '2025-10-24 07:22:09', '2025-10-25 02:50:17'),
(4, '1000004', 'David Torres', 'David', 'Torres', 'david.torres@comfachoco.com', '$2y$10$OcSn3FEnBv2.LnpDmIldOO4jLNB7Y2zz04i5k2Q45g4DxktZ9Uqjm', 'empleado', 1, 'Gestión', '2022-10-24', 0.00, 0, NULL, 1, '2025-10-24 07:22:09', '2025-10-25 02:50:17'),
(5, '1000005', 'Laura Silva', 'Laura', 'Silva', 'laura.silva@comfachoco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gerente', 5, 'Gerencia', '2021-10-24', 0.00, 0, NULL, 1, '2025-10-24 07:22:09', '2025-10-25 02:50:17'),
(6, '1000006', 'Miguel Santos', 'Miguel', 'Santos', 'miguel.santos@comfachoco.com', '$2y$10$OcSn3FEnBv2.LnpDmIldOO4jLNB7Y2zz04i5k2Q45g4DxktZ9Uqjm', 'empleado', 4, 'Infraestructura', '2024-10-24', 0.00, 0, NULL, 1, '2025-10-24 07:22:09', '2025-10-25 02:50:17'),
(8, '1077430750', 'Deiner Bello', 'Deiner', 'Bello', 'deiner.bello@comfachoco.com', '$2y$10$UVkv6f13xkDzKuNehrnQXu/KG.RDW/e1UBAXC4sow8NJxjz1xh8le', 'empleado', 2, 'Recursos Humanos', '2025-10-25', 0.00, 0, NULL, 0, '2025-10-25 14:42:59', '2025-10-25 14:46:23');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_fecha` (`fecha_creacion`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_fecha_inicio` (`fecha_inicio`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `historial_vacaciones`
--
ALTER TABLE `historial_vacaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_periodo` (`periodo_inicio`,`periodo_fin`);

--
-- Indices de la tabla `politicas`
--
ALTER TABLE `politicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `politicas_vacaciones`
--
ALTER TABLE `politicas_vacaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo_empleado` (`tipo_empleado_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `reglas_aprobacion`
--
ALTER TABLE `reglas_aprobacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo` (`tipo_permiso`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `restricciones_disponibilidad`
--
ALTER TABLE `restricciones_disponibilidad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_departamento` (`departamento`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aprobador_id` (`aprobador_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_inicio` (`fecha_inicio`);

--
-- Indices de la tabla `tipos_empleado`
--
ALTER TABLE `tipos_empleado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `tipos_permisos`
--
ALTER TABLE `tipos_permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `tipo_empleado_id` (`tipo_empleado_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historial_vacaciones`
--
ALTER TABLE `historial_vacaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `politicas`
--
ALTER TABLE `politicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `politicas_vacaciones`
--
ALTER TABLE `politicas_vacaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `reglas_aprobacion`
--
ALTER TABLE `reglas_aprobacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `restricciones_disponibilidad`
--
ALTER TABLE `restricciones_disponibilidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tipos_empleado`
--
ALTER TABLE `tipos_empleado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipos_permisos`
--
ALTER TABLE `tipos_permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD CONSTRAINT `actividad_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial_vacaciones`
--
ALTER TABLE `historial_vacaciones`
  ADD CONSTRAINT `historial_vacaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `politicas_vacaciones`
--
ALTER TABLE `politicas_vacaciones`
  ADD CONSTRAINT `politicas_vacaciones_ibfk_1` FOREIGN KEY (`tipo_empleado_id`) REFERENCES `tipos_empleado` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`tipo_empleado_id`) REFERENCES `tipos_empleado` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;