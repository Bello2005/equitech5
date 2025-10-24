# ComfaChoco International - Sistema de Gestión de Recursos Humanos

Sistema de gestión de recursos humanos desarrollado con PHP puro, diseñado para ComfaChoco International.

## Estructura del Proyecto

```
Comfachoco/
├── assets/
│   ├── css/              # Archivos CSS
│   │   ├── animations.css
│   │   ├── badges.css
│   │   ├── calendar.css
│   │   ├── components.css
│   │   ├── scrollbar.css
│   │   └── variables.css
│   ├── js/               # Archivos JavaScript
│   │   ├── alpine-directives.js
│   │   ├── calendar.js
│   │   └── tailwind-config.js
│   └── images/           # Imágenes del proyecto
├── config/
│   ├── auth.php          # Autenticación de usuarios
│   ├── database.php      # Configuración de base de datos
│   ├── mock_data.php     # Datos de prueba
│   └── session.php       # Manejo de sesiones
├── includes/
│   ├── footer.php        # Footer del sitio
│   ├── head.php          # Head HTML y scripts
│   ├── header.php        # Header/navbar del dashboard
│   └── sidebar.php       # Sidebar de navegación
├── pages/
│   ├── dashboard.php     # Dashboard principal
│   ├── login.php         # Página de login
│   └── logout.php        # Cerrar sesión
├── database.sql          # Script SQL para crear la base de datos
├── index.php             # Punto de entrada (redirige al login)
└── README.md             # Este archivo
```

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx (XAMPP, LAMPP, WAMP, etc.)
- Extensiones PHP requeridas:
  - mysqli
  - session

## Instalación

### 1. Configurar Variables de Entorno

El proyecto usa variables de entorno para una mayor seguridad. Copia el archivo `.env.example` a `.env`:

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus configuraciones:

```env
# Configuración de Base de Datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=comfachoco
DB_USER=root
DB_PASS=

# Configuración de la Aplicación
APP_NAME="ComfaChoco International"
APP_ENV=development          # development, production
APP_DEBUG=true              # true para desarrollo, false para producción
APP_URL=http://localhost/Comfachoco

# Configuración de Sesión
SESSION_LIFETIME=7200       # Tiempo en segundos (2 horas)
SESSION_SECURE=false        # true si usas HTTPS
SESSION_HTTP_ONLY=true      # Protección contra XSS

# Configuración de Seguridad
APP_KEY=base64:Jx8K9mN3pQ7rT5vW2yZ4bC6dF8gH0jL3nM5oP7qS9tU1
HASH_ALGORITHM=bcrypt

# Configuración de Zona Horaria
TIMEZONE=-05:00              # Colombia UTC-5 (usar formato offset para MySQL)
```

**IMPORTANTE**:
- El archivo `.env` contiene información sensible y NO debe ser versionado en Git
- El archivo `.env.example` es la plantilla y SÍ debe estar en Git
- Cambia `APP_KEY` por un valor único generado aleatoriamente

### 2. Configurar la Base de Datos

```bash
# Acceder a MySQL
mysql -u root -p

# Importar el script SQL
source /opt/lampp/htdocs/Comfachoco/database.sql

# O desde phpMyAdmin:
# - Crear base de datos 'comfachoco'
# - Importar el archivo database.sql
```

### 3. Iniciar el Servidor

#### Con XAMPP/LAMPP:
```bash
sudo /opt/lampp/lampp start
```

#### Con PHP Built-in Server:
```bash
cd /opt/lampp/htdocs/Comfachoco
php -S localhost:8000
```

### 4. Acceder a la Aplicación

Abre tu navegador y visita:
- **Con XAMPP/LAMPP**: `http://localhost/Comfachoco/`
- **Con PHP Built-in**: `http://localhost:8000/`

## Credenciales de Acceso

### Usuario Administrador:
- **Email**: admin@comfachoco.com
- **Contraseña**: admin123

### Otros usuarios de prueba:
Todos los usuarios de prueba tienen la misma contraseña: `admin123`
- carlos.ruiz@comfachoco.com
- ana.mendoza@comfachoco.com
- david.torres@comfachoco.com
- laura.silva@comfachoco.com
- miguel.santos@comfachoco.com

## Características

- ✅ Sistema de autenticación con login/logout
- ✅ Dashboard ejecutivo con KPIs
- ✅ Gestión de solicitudes (vacaciones, permisos, etc.)
- ✅ Calendario de ausencias
- ✅ Feed de actividad reciente
- ✅ Estadísticas y métricas
- ✅ Exportación de datos (CSV, PDF)
- ✅ Diseño responsive
- ✅ Modo oscuro (toggle)
- ✅ Notificaciones
- ✅ Sistema de perfiles de usuario

## Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL
- **Frontend**:
  - HTML5
  - TailwindCSS (CDN)
  - Alpine.js (interactividad)
  - Font Awesome (iconos)
  - FullCalendar.js (calendario)
  - Chart.js (gráficos)

## Estructura de la Base de Datos

### Tabla: usuarios
- Almacena información de los usuarios del sistema
- Roles: admin, gerente, empleado

### Tabla: solicitudes
- Gestiona solicitudes de vacaciones, permisos, etc.
- Estados: pendiente, aprobado, rechazado
- Prioridades: alta, media, baja

### Tabla: actividad
- Registra la actividad reciente del sistema

### Tabla: eventos
- Almacena eventos del calendario

## Desarrollo

### Agregar una nueva página

1. Crear el archivo en la carpeta `pages/`
2. Incluir los archivos de configuración necesarios:
```php
<?php
require_once __DIR__ . '/../config/session.php';
requireLogin(); // Si requiere autenticación

$page_title = 'Título de la Página';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';
?>

<!-- Tu contenido aquí -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

### Agregar estilos CSS

Agregar tus estilos en un nuevo archivo dentro de `assets/css/` e incluirlo en `includes/head.php`

### Agregar JavaScript

Agregar tus scripts en `assets/js/` e incluirlos en `includes/footer.php`

## Seguridad

### Características de Seguridad Implementadas

- ✅ **Variables de entorno (.env)**: Credenciales y configuraciones sensibles fuera del código
- ✅ **Contraseñas hasheadas**: Uso de `password_hash()` con algoritmo bcrypt
- ✅ **Consultas preparadas**: Prepared statements para prevenir SQL injection
- ✅ **Sesiones seguras**:
  - HttpOnly cookies para prevenir XSS
  - Regeneración periódica de ID de sesión
  - Configuración de tiempo de vida de sesión
  - SameSite cookie policy
- ✅ **Validación de autenticación**: Protección de rutas con `requireLogin()`
- ✅ **.gitignore**: Archivo .env excluido del control de versiones
- ✅ **Manejo de errores**: Mensajes diferentes según ambiente (desarrollo/producción)

### Recomendaciones para Producción

⚠️ **IMPORTANTE antes de desplegar a producción**:

1. **Variables de entorno**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   SESSION_SECURE=true  # Solo si usas HTTPS
   ```

2. **Cambiar credenciales**:
   - Cambiar contraseñas por defecto de usuarios
   - Generar nuevo `APP_KEY` único
   - Usar contraseña fuerte para MySQL

3. **HTTPS**:
   - Habilitar SSL/TLS
   - Activar `SESSION_SECURE=true`
   - Forzar HTTPS en Apache/Nginx

4. **Permisos de archivos**:
   ```bash
   chmod 644 .env
   chmod 755 pages/
   chmod 755 config/
   ```

5. **Ocultar información del servidor**:
   - Desactivar `display_errors` en php.ini
   - Ocultar versión de PHP
   - Configurar CSP headers

6. **Backups regulares**:
   - Base de datos
   - Archivos del proyecto
   - Variables de entorno

### Generar APP_KEY Seguro

Puedes generar un APP_KEY seguro con:

```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

O usando OpenSSL:

```bash
openssl rand -base64 32
```

## TODO / Próximas Mejoras

- [ ] Implementar CRUD completo para solicitudes
- [ ] Sistema de permisos granular por rol
- [ ] Notificaciones en tiempo real
- [ ] Reportes avanzados
- [ ] API REST
- [ ] Integración con servicios externos
- [ ] Sistema de mensajería interna
- [ ] Gestión de documentos
- [ ] Módulo de evaluaciones de desempeño
- [ ] App móvil

## Soporte

Para reportar problemas o sugerencias, contacta al equipo de desarrollo.

## Licencia

© 2024 ComfaChoco International. Todos los derechos reservados.
