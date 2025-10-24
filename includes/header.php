        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Enhanced Top navbar -->
            <header class="glass-effect border-b border-gray-100/50 z-30 sticky top-0">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <div class="flex items-center">
                            <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl hover:bg-gray-100/50 transition-colors mr-2">
                                <i class="fas fa-bars text-gray-600"></i>
                            </button>
                            <div class="hidden md:block">
                                <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-primary-dark bg-clip-text text-transparent">
                                    <?= $page_title ?? 'Executive Dashboard' ?>
                                </h1>
                            </div>
                        </div>

                        <!-- Search bar -->
                        <div class="flex-1 max-w-2xl mx-8">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                                </div>
                                <input type="text"
                                       class="block w-full pl-12 pr-4 py-3 bg-white/80 border border-gray-200 rounded-2xl placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 backdrop-blur-sm"
                                       placeholder="Buscar empleados, solicitudes, reportes...">
                            </div>
                        </div>

                        <!-- Right actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Theme toggle -->
                            <button @click="darkMode = !darkMode"
                                    class="p-3 rounded-xl hover:bg-gray-100/50 transition-colors relative group">
                                <i class="fas" :class="darkMode ? 'fa-sun text-orange-500' : 'fa-moon text-indigo-500'"></i>
                            </button>

                            <!-- Notifications -->
                            <div class="relative">
                                <button @click="notificationOpen = !notificationOpen"
                                        class="p-3 rounded-xl hover:bg-gray-100/50 transition-colors relative group">
                                    <i class="far fa-bell text-gray-600"></i>
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">3</span>
                                </button>

                                <!-- Notification dropdown -->
                                <div x-show="notificationOpen"
                                     x-cloak
                                     @click.away="notificationOpen = false"
                                     class="absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-elegant-lg border border-gray-100 py-2 z-50"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:leave="transition ease-in duration-150">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <div class="flex justify-between items-center">
                                            <h3 class="text-lg font-semibold text-gray-900">Notificaciones</h3>
                                            <span class="text-xs text-primary font-medium">3 nuevas</span>
                                        </div>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <div class="p-4 border-b border-gray-100 hover:bg-gray-50/50 cursor-pointer transition-colors">
                                            <div class="flex space-x-3">
                                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-user-plus text-blue-600"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900">Nuevo empleado</p>
                                                    <p class="text-xs text-gray-500 mt-1">Carlos Ruiz se unió al equipo</p>
                                                    <p class="text-xs text-gray-400 mt-1">Hace 5 minutos</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-2 border-t border-gray-100">
                                        <a href="#" class="block text-center text-sm text-primary font-medium py-2 hover:bg-gray-50 rounded-xl transition-colors">
                                            Ver todas las notificaciones
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- User menu -->
                            <div class="relative">
                                <button @click="userMenuOpen = !userMenuOpen"
                                        class="flex items-center space-x-3 p-2 rounded-xl hover:bg-gray-100/50 transition-colors">
                                    <img class="w-8 h-8 rounded-xl object-cover"
                                         src="<?= htmlspecialchars($usuario['avatar'] ?? '') ?>"
                                         alt="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre'] ?? 'User') ?>&background=0B8A3A&color=fff&size=128'">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </button>

                                <!-- User dropdown -->
                                <div x-show="userMenuOpen"
                                     x-cloak
                                     @click.away="userMenuOpen = false"
                                     class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-elegant-lg border border-gray-100 py-2 z-50"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:leave="transition ease-in duration-150">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-gray-900"><?= $usuario['nombre'] ?></p>
                                        <p class="text-xs text-gray-500"><?= $usuario['rol'] ?></p>
                                    </div>
                                    <div class="py-2">
                                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-user mr-3 w-5"></i> Mi perfil
                                        </a>
                                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-cog mr-3 w-5"></i> Configuración
                                        </a>
                                    </div>
                                    <div class="py-2 border-t border-gray-100">
                                        <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <i class="fas fa-sign-out-alt mr-3 w-5"></i> Cerrar sesión
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
