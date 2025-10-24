<?php
// Mock data - TODO: Reemplazar con llamadas a APIs reales o base de datos

$kpis = [
    'saldo_dias' => ['valor' => 15, 'tendencia' => '+2', 'icono' => 'calendar', 'meta' => 20, 'color' => 'green'],
    'solicitudes_pendientes' => ['valor' => 8, 'tendencia' => '-3', 'icono' => 'file-alt', 'meta' => 5, 'color' => 'yellow'],
    'proxima_ausencia' => ['valor' => '25 Nov', 'tendencia' => '', 'icono' => 'plane', 'meta' => '', 'color' => 'blue'],
    'satisfaccion_equipo' => ['valor' => '92%', 'tendencia' => '+5%', 'icono' => 'star', 'meta' => 95, 'color' => 'purple'],
    'productividad' => ['valor' => '87%', 'tendencia' => '+2%', 'icono' => 'chart-line', 'meta' => 90, 'color' => 'indigo'],
    'presupuesto' => ['valor' => '98%', 'tendencia' => '-1%', 'icono' => 'dollar-sign', 'meta' => 100, 'color' => 'emerald']
];

$solicitudes = [
    ['id' => 1, 'empleado' => 'Carlos Ruiz', 'avatar' => 'CR', 'tipo' => 'Vacaciones', 'fecha' => '2024-01-15', 'dias' => 5, 'estado' => 'pendiente', 'prioridad' => 'alta'],
    ['id' => 2, 'empleado' => 'Ana Mendoza', 'avatar' => 'AM', 'tipo' => 'Permiso médico', 'fecha' => '2024-01-12', 'dias' => 2, 'estado' => 'aprobado', 'prioridad' => 'media'],
    ['id' => 3, 'empleado' => 'David Torres', 'avatar' => 'DT', 'tipo' => 'Teletrabajo', 'fecha' => '2024-01-10', 'dias' => 1, 'estado' => 'rechazado', 'prioridad' => 'baja'],
    ['id' => 4, 'empleado' => 'Laura Silva', 'avatar' => 'LS', 'tipo' => 'Vacaciones', 'fecha' => '2024-01-08', 'dias' => 10, 'estado' => 'pendiente', 'prioridad' => 'alta'],
    ['id' => 5, 'empleado' => 'Miguel Santos', 'avatar' => 'MS', 'tipo' => 'Capacitación', 'fecha' => '2024-01-05', 'dias' => 3, 'estado' => 'aprobado', 'prioridad' => 'media']
];

$actividad_reciente = [
    ['usuario' => 'Sofia Ramirez', 'accion' => 'Aprobó solicitud de vacaciones', 'tiempo' => 'Hace 2 horas', 'tipo' => 'aprobacion'],
    ['usuario' => 'Miguel Ángel', 'accion' => 'Envió nueva solicitud de permisos', 'tiempo' => 'Hace 4 horas', 'tipo' => 'solicitud'],
    ['usuario' => 'Sistema', 'accion' => 'Recordatorio: Revisión trimestral', 'tiempo' => 'Hace 1 día', 'tipo' => 'recordatorio'],
    ['usuario' => 'Carlos López', 'accion' => 'Completó evaluación de desempeño', 'tiempo' => 'Hace 2 días', 'tipo' => 'completado']
];

$eventos_calendario = [
    ['titulo' => 'Carlos - Vacaciones', 'fecha' => '2024-01-15', 'tipo' => 'vacaciones', 'empleado' => 'Carlos Ruiz'],
    ['titulo' => 'Ana - Permiso médico', 'fecha' => '2024-01-12', 'tipo' => 'permiso', 'empleado' => 'Ana Mendoza'],
    ['titulo' => 'Reunión equipo RH', 'fecha' => '2024-01-10', 'tipo' => 'reunion', 'empleado' => 'Todos'],
    ['titulo' => 'David - Teletrabajo', 'fecha' => '2024-01-10', 'tipo' => 'teletrabajo', 'empleado' => 'David Torres'],
    ['titulo' => 'Evaluaciones trimestrales', 'fecha' => '2024-01-20', 'tipo' => 'evaluacion', 'empleado' => 'Equipo completo']
];
?>
