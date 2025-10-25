# ComfaChoco - Sistema de Gestión de Recursos Humanos

Sistema integral de gestión de recursos humanos para ComfaChoco, desarrollado con PHP 8.2, MySQL y TailwindCSS.
Gestiona empleados, vacaciones, permisos y políticas organizacionales de manera eficiente y segura.

## Características Principales

- ✅ Gestión completa de empleados (CRUD)
- 🔐 Sistema de autenticación y roles (admin, empleado, gerente)
- 📅 Gestión de vacaciones y permisos
- 📋 Políticas organizacionales configurables
- 📊 Dashboards informativos
- 🎨 Interfaz moderna con TailwindCSS y AlpineJS

## Requisitos del Sistema

- PHP 8.2 o superior
- MariaDB 10.4 o superior / MySQL 5.7+
- Apache 2.4 o superior
- Composer (para dependencias)
- Node.js y npm (para assets)

### Extensiones PHP Requeridas

- mysqli
- session
- json
- mbstring
- fileinfo

## Estructura del Proyecto

```bash
Comfachoco/
├── api/                  # APIs para operaciones CRUD
├── assets/              # Recursos estáticos
│   ├── css/            # Estilos (TailwindCSS)
│   ├── js/             # Scripts (AlpineJS)
│   └── images/         # Imágenes
├── config/             # Configuraciones
├── includes/           # Componentes PHP reutilizables
├── lib/               # Librerías de terceros
├── pages/             # Páginas del sistema
│   └── api/           # Endpoints de la API
├── scripts/           # Scripts de utilidad
└── uploads/           # Archivos subidos
    └── documentos/    # Documentos de empleados
```

## Instalación Rápida

1. **Clonar el repositorio:**

```bash
git clone https://github.com/Bello2005/Comfachoco.git
cd Comfachoco
```

2. **Configurar el servidor web:**

- Copiar el proyecto a tu directorio web (ej: /opt/lampp/htdocs/ para XAMPP)
- Configurar el virtual host (opcional pero recomendado)

3. **Configurar la base de datos:**

```bash
# Acceder a MySQL
mysql -u root -p

# Crear la base de datos
CREATE DATABASE comfachoco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Importar el esquema base
mysql -u root -p comfachoco < comfachoco.sql
```

4. **Configurar el entorno:**

```bash
# Copiar el archivo de configuración
cp config/env.example.php config/env.php

# Editar config/env.php con tu configuración
```

5. **Compilar los assets (si se modifican los estilos):**

```bash
# Instalar dependencias
npm install

# Compilar CSS
npm run build:css
```

## Configuración del Entorno de Desarrollo

### Base de Datos

El archivo `config/env.php` debe contener:

```php
<?php
return [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'comfachoco',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'TIMEZONE' => 'America/Bogota'
];
```

### Virtual Host (Apache)

Ejemplo de configuración:

```apache
<VirtualHost *:80>
    ServerName comfachoco.local
    DocumentRoot "/opt/lampp/htdocs/Comfachoco"

    <Directory "/opt/lampp/htdocs/Comfachoco">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/comfachoco-error.log"
    CustomLog "logs/comfachoco-access.log" combined
</VirtualHost>
```

source /opt/lampp/htdocs/Comfachoco/database.sql

# O desde phpMyAdmin:

# - Crear base de datos 'comfachoco'

# - Importar el archivo database.sql

````

### 3. Iniciar el Servidor

#### Con XAMPP/LAMPP:
```bash
sudo /opt/lampp/lampp start
````

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

## Problemas Comunes y Soluciones

### 1. Problemas con Rutas

El sistema es sensible a mayúsculas/minúsculas y rutas exactas. Aquí algunos puntos importantes:

#### Rutas del Sistema

- La carpeta debe llamarse exactamente `Comfachoco` (con C mayúscula)
- Todos los archivos y carpetas deben mantener exactamente el mismo nombre que en el repositorio
- En Linux/Mac las rutas son case-sensitive (distinguen mayúsculas/minúsculas)

#### URLs y Acceso

```bash
# ✅ Correcto
http://localhost/Comfachoco
http://localhost/Comfachoco/pages/login.php

# ❌ Incorrecto
http://localhost/comfachoco         # 'c' minúscula
http://localhost/COMFACHOCO         # todo mayúsculas
http://localhost/Comfachoco/Login   # 'Login' con mayúscula
```

#### Solución de Problemas de Rutas

1. **Error 404**: Verificar que la ruta sea exactamente igual (mayúsculas/minúsculas)
2. **Archivos no encontrados**:
   - Usar `__DIR__` para rutas relativas
   - Verificar permisos de carpetas
3. **Assets no cargan**:
   - Comprobar la variable `APP_URL` en configuración
   - Verificar rutas en includes/head.php

### 2. Permisos de Archivos

En Linux/Mac, establecer los permisos correctos:

```bash
# Carpetas
chmod 755 /opt/lampp/htdocs/Comfachoco
chmod 755 /opt/lampp/htdocs/Comfachoco/uploads
chmod 755 /opt/lampp/htdocs/Comfachoco/config

# Archivos
chmod 644 /opt/lampp/htdocs/Comfachoco/config/env.php
chmod 644 /opt/lampp/htdocs/Comfachoco/uploads/*
```

### 3. Problemas de Base de Datos

- Verificar que el nombre de la base de datos sea exactamente `comfachoco`
- Las tablas y columnas son case-sensitive en algunos sistemas
- Usar siempre minúsculas para nombres de tablas y columnas

## Soporte

Para reportar problemas o solicitar ayuda:

1. Abrir un issue en GitHub
2. Detallar el problema y pasos para reproducirlo
3. Incluir logs relevantes
4. Especificar versión de PHP y MySQL
5. Incluir capturas de pantalla si es necesario
6. Mencionar el sistema operativo y entorno

## Licencia

© 2024 ComfaChoco. Todos los derechos reservados.
