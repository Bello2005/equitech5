<!-- Mobile sidebar backdrop -->
<div x-show="sidebarOpen && window.innerWidth < 1024"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:leave="transition-opacity ease-linear duration-300">
        </div>

        <!-- Enhanced Sidebar -->
        <div x-show="sidebarOpen" class="fixed inset-y-0 left-0 z-50 w-80 sidebar-transition lg:relative lg:inset-0"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:leave="transition ease-in-out duration-300 transform">
            <div class="flex flex-col h-full bg-white shadow-xl border-r border-gray-100">
                <!-- Logo section -->
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        <img src="../assets/images/logo-comfachoco-no-lema.svg"
                             alt="ComfaChoco Logo"
                             class="h-10 w-auto">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">ComfaChoco</h1>
                            <p class="text-xs text-gray-500">International</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <!-- User profile -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <img class="w-12 h-12 rounded-2xl object-cover border-2 border-white shadow-md"
                                 src="<?= htmlspecialchars($usuario['avatar'] ?? '') ?>"
                                 alt="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre'] ?? 'User') ?>&background=0B8A3A&color=fff&size=128'">
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white rounded-full"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($usuario['nombre'] ?? '') ?></p>
                            <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($usuario['rol'] ?? '') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-6 space-y-2 overflow-y-auto scrollbar-thin">
                    <?php
                    // Detectar página actual
                    $current_page = basename($_SERVER['PHP_SELF']);

                    $menu_items = [
                        ['icon' => 'fas fa-th-large', 'text' => 'Dashboard', 'badge' => '', 'link' => 'dashboard.php'],
                        ['icon' => 'fas fa-file-alt', 'text' => 'Solicitudes', 'badge' => '', 'link' => 'solicitudes.php'],
                        ['icon' => 'fas fa-calendar', 'text' => 'Calendario', 'badge' => '', 'link' => 'calendario.php'],
                        ['icon' => 'fas fa-users', 'text' => 'Empleados', 'badge' => '', 'link' => 'empleados.php'],
                        ['icon' => 'fas fa-book', 'text' => 'Políticas', 'badge' => '', 'link' => 'politicas.php'],
                        ['icon' => 'fas fa-cog', 'text' => 'Configuración', 'badge' => '', 'link' => 'configuracion.php']
                    ];

                    foreach ($menu_items as $item) {
                        $is_active = ($current_page == $item['link']);
                        $active_class = $is_active ? 'bg-gradient-to-r from-primary to-primary-dark text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-primary';
                        $badge_class = $is_active ? 'bg-white text-primary' : 'bg-primary text-white';
                        echo "
                        <a href=\"{$item['link']}\" class=\"group flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {$active_class}\">
                            <div class=\"flex items-center\">
                                <i class=\"{$item['icon']} mr-3 w-5 h-5\"></i>
                                {$item['text']}
                            </div>
                            " . ($item['badge'] ? "<span class=\"px-2 py-1 text-xs font-bold rounded-full {$badge_class}\">{$item['badge']}</span>" : "") . "
                        </a>";
                    }
                    ?>
                </nav>
            </div>
        </div>
