<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Dashboard' ?> - ComfaChoco International</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php
    // Preferir el CSS compilado localmente en producción. Si no existe, caer al CDN para desarrollo rápido.
    $compiledTailwind = __DIR__ . '/../assets/css/styles.css';
    if (file_exists($compiledTailwind)) {
        echo '<link href="../assets/css/styles.css" rel="stylesheet">\n';
    } else {
        // En desarrollo rápido todavía podemos usar el CDN, pero no recomendado en producción
        echo '<script src="https://cdn.tailwindcss.com"></script>\n';
    }
    ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <!-- FullCalendar locales (so labels like 'today', 'month/week/day' appear in Spanish) -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script src="../assets/js/tailwind-config.js"></script>
    <link href="../assets/css/variables.css" rel="stylesheet">
    <link href="../assets/css/components.css" rel="stylesheet">
    <link href="../assets/css/animations.css" rel="stylesheet">
    <link href="../assets/css/badges.css" rel="stylesheet">
    <link href="../assets/css/scrollbar.css" rel="stylesheet">
    <link href="../assets/css/calendar.css" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full" x-data="{
    sidebarOpen: window.innerWidth >= 1024,
    darkMode: false,
    notificationOpen: false,
    userMenuOpen: false,
    currentView: 'dashboard'
 }" @resize.window="sidebarOpen = window.innerWidth >= 1024">
    <div class="fixed inset-0 gradient-bg opacity-5 pointer-events-none"></div>
    <div class="flex h-full" :class="{ 'dark': darkMode }">
