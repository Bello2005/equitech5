<?php
require_once __DIR__ . '/../config/session.php';

requireLogin();
$usuario = getCurrentUser();

if (empty($usuario['avatar'])) {
    $usuario['avatar'] = 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80';
}

$nombre_partes = explode(' ', $usuario['nombre']);
$usuario['iniciales'] = substr($nombre_partes[0], 0, 1) . (isset($nombre_partes[1]) ? substr($nombre_partes[1], 0, 1) : '');

$page_title = 'Analytics';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 overflow-y-auto focus:outline-none py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Analytics y Reportes</h1>
                <p class="text-gray-600">Análisis detallado del rendimiento y métricas clave de la organización.</p>
            </div>
            <span class="px-4 py-2 bg-gradient-to-r from-primary to-primary-dark text-white rounded-xl text-sm font-semibold">
                <i class="fas fa-star mr-2"></i>Nueva Función
            </span>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div id="kpi-productividad-card" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <span class="text-sm bg-white/20 px-3 py-1 rounded-full">+12%</span>
                </div>
                <p id="kpi-productividad" class="text-3xl font-bold mb-2">87%</p>
                <p class="text-blue-100">Productividad General</p>
            </div>

            <div id="kpi-satisfaccion-card" class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-smile text-2xl"></i>
                    </div>
                    <span class="text-sm bg-white/20 px-3 py-1 rounded-full">+5%</span>
                </div>
                <p id="kpi-satisfaccion" class="text-3xl font-bold mb-2">92%</p>
                <p class="text-green-100">Satisfacción Empleados</p>
            </div>

            <div id="kpi-horas-card" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <span class="text-sm bg-white/20 px-3 py-1 rounded-full">-3%</span>
                </div>
                <p id="kpi-horas" class="text-3xl font-bold mb-2">8.2h</p>
                <p class="text-purple-100">Promedio Horas/Día</p>
            </div>

            <div id="kpi-retencion-card" class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <span class="text-sm bg-white/20 px-3 py-1 rounded-full">+8%</span>
                </div>
                <p id="kpi-retencion" class="text-3xl font-bold mb-2">94%</p>
                <p class="text-orange-100">Tasa de Retención</p>
            </div>
        </div>

        <!-- Real-time data panel -->
        <div id="realtime-panel" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Datos en tiempo real</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Usuarios registrados</p>
                    <p id="rt-usuarios" class="text-2xl font-bold text-gray-900">-</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Solicitudes pendientes</p>
                    <p id="rt-pendientes" class="text-2xl font-bold text-gray-900">-</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Solicitudes aprobadas</p>
                    <p id="rt-aprobadas" class="text-2xl font-bold text-gray-900">-</p>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="text-sm font-semibold text-gray-800 mb-2">Próximos eventos</h4>
                <div id="rt-eventos" class="space-y-2 text-sm text-gray-600">Cargando...</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
            <!-- Productivity Chart -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Tendencia de Productividad</h3>
                <div class="h-64 flex items-center justify-center bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl">
                    <div class="text-center">
                        <i class="fas fa-chart-area text-6xl text-blue-500 mb-4"></i>
                        <p class="text-gray-600">Gráfico de tendencias</p>
                        <p class="text-sm text-gray-500">Últimos 12 meses</p>
                    </div>
                </div>
            </div>

            <!-- Department Performance -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Rendimiento por Departamento</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Desarrollo</span>
                            <span class="text-sm font-semibold text-gray-900">95%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Diseño</span>
                            <span class="text-sm font-semibold text-gray-900">88%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full" style="width: 88%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Marketing</span>
                            <span class="text-sm font-semibold text-gray-900">82%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full" style="width: 82%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Recursos Humanos</span>
                            <span class="text-sm font-semibold text-gray-900">90%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-orange-500 to-orange-600 h-3 rounded-full" style="width: 90%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Infraestructura</span>
                            <span class="text-sm font-semibold text-gray-900">93%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-3 rounded-full" style="width: 93%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">Reportes Disponibles</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button id="btn-reporte-mensual" class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary hover:bg-primary/5 transition-all group">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-blue-200">
                            <i class="fas fa-file-excel text-blue-600"></i>
                        </div>
                        <span class="font-semibold text-gray-900">Reporte Mensual</span>
                    </div>
                    <i class="fas fa-download text-gray-400 group-hover:text-primary"></i>
                </button>

                <button id="btn-reporte-trimestral" class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary hover:bg-primary/5 transition-all group">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-green-200">
                            <i class="fas fa-file-pdf text-green-600"></i>
                        </div>
                        <span class="font-semibold text-gray-900">Reporte Trimestral</span>
                    </div>
                    <i class="fas fa-download text-gray-400 group-hover:text-primary"></i>
                </button>

                <button id="btn-reporte-anual" class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary hover:bg-primary/5 transition-all group">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-purple-200">
                            <i class="fas fa-chart-pie text-purple-600"></i>
                        </div>
                        <span class="font-semibold text-gray-900">Reporte Anual</span>
                    </div>
                    <i class="fas fa-download text-gray-400 group-hover:text-primary"></i>
                </button>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Real-time polling for analytics data
const analyticsEndpoint = 'api/analytics_data.php';

async function fetchAnalytics() {
    try {
        const res = await fetch(analyticsEndpoint, {cache: 'no-store'});
        if (!res.ok) throw new Error('Error al obtener datos');
        const data = await res.json();

        // Update KPIs
        if (data.productividad !== null) {
            document.getElementById('kpi-productividad').textContent = data.productividad + '%';
        }
        // If not available, keep existing satisfaction/hours/retention

        document.getElementById('rt-usuarios').textContent = data.total_usuarios;
        document.getElementById('rt-pendientes').textContent = data.solicitudes.pendiente ?? 0;
        document.getElementById('rt-aprobadas').textContent = data.solicitudes.aprobado ?? 0;

        // Próximos eventos
        const eventosEl = document.getElementById('rt-eventos');
        eventosEl.innerHTML = '';
        if (data.eventos_proximos && data.eventos_proximos.length) {
            data.eventos_proximos.forEach(ev => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between';
                const left = document.createElement('div');
                left.innerHTML = `<div class="text-sm font-medium text-gray-900">${escapeHtml(ev.titulo)}</div><div class="text-xs text-gray-500">${escapeHtml(ev.fecha)}</div>`;
                const badge = document.createElement('div');
                badge.className = 'inline-block px-2 py-1 rounded-full text-xs font-semibold';
                // color by tipo
                const colorMap = { 'vacaciones': 'bg-green-100 text-green-800', 'permiso': 'bg-amber-100 text-amber-800', 'reunion': 'bg-blue-100 text-blue-800', 'teletrabajo': 'bg-purple-100 text-purple-800' };
                const cls = colorMap[ev.tipo] || 'bg-gray-100 text-gray-800';
                badge.className += ' ' + cls;
                badge.textContent = ev.tipo || '';
                div.appendChild(left);
                div.appendChild(badge);
                eventosEl.appendChild(div);
            });
        } else {
            eventosEl.textContent = 'No hay eventos próximos';
        }

    } catch (err) {
        console.error('fetchAnalytics error', err);
    }
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Start polling every 10 seconds
fetchAnalytics();
setInterval(fetchAnalytics, 10000);

// Download report helpers
document.getElementById('btn-reporte-mensual').addEventListener('click', function() {
    window.location = 'api/export_report.php?report=monthly&format=csv';
});
document.getElementById('btn-reporte-trimestral').addEventListener('click', function() {
    window.location = 'api/export_report.php?report=trimestral&format=csv';
});
document.getElementById('btn-reporte-anual').addEventListener('click', function() {
    window.location = 'api/export_report.php?report=anual&format=csv';
});
</script>
