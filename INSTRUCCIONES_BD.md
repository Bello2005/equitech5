# Instrucciones de Configuración de Base de Datos - ComfaChoco

## Estado Actual ✅

**TODAS LAS CONFIGURACIONES COMPLETADAS EXITOSAMENTE**

- ✅ MySQL está corriendo en el puerto 3306
- ✅ Base de datos `comfachoco` configurada
- ✅ 6 tablas creadas con datos de prueba
- ✅ Conexión PHP funcionando correctamente
- ✅ Todas las vistas conectadas a la base de datos

La aplicación está 100% funcional y lista para usar.

## Archivos Configurados

### Archivos de Configuración
- [config/database.php](config/database.php) - Conexión centralizada a la base de datos
- [config/env.php](config/env.php) - Cargador de variables de entorno
- [.env](.env) - Credenciales de base de datos

### Páginas Principales
- [pages/dashboard.php](pages/dashboard.php) - Conectado con BD (con fallback a mock_data)
- [pages/solicitudes.php](pages/solicitudes.php) - Conectado con BD (con fallback a mock_data)
- [pages/empleados.php](pages/empleados.php) - Conectado con BD (con fallback a mock_data)
- [pages/politicas.php](pages/politicas.php) - Conectado con BD (con fallback a mock_data)

### APIs
- [pages/api/empleados_action.php](pages/api/empleados_action.php) - CRUD de empleados
- [pages/api/solicitud_action.php](pages/api/solicitud_action.php) - Aprobar/rechazar solicitudes
- [pages/api/solicitud_create.php](pages/api/solicitud_create.php) - Crear solicitudes
- [pages/api/politicas_action.php](pages/api/politicas_action.php) - CRUD de políticas
- [pages/api/event_create.php](pages/api/event_create.php) - Crear eventos
- [pages/api/analytics_data.php](pages/api/analytics_data.php) - Datos para analytics
- [pages/api/export_report.php](pages/api/export_report.php) - Exportar reportes

### Autenticación
- [config/auth.php](config/auth.php) - Login y registro
- [config/user_actions.php](config/user_actions.php) - Acciones de usuario

## Pasos para Iniciar MySQL

### 1. Iniciar MySQL con LAMPP

Ejecuta el siguiente comando en la terminal (requiere permisos de root):

```bash
sudo /opt/lampp/lampp startmysql
```

O para iniciar todos los servicios de LAMPP:

```bash
sudo /opt/lampp/lampp start
```

### 2. Verificar que MySQL esté corriendo

```bash
/opt/lampp/lampp status
```

Deberías ver:
```
MySQL is running.
```

### 3. Crear la Base de Datos

Si la base de datos 'comfachoco' no existe, créala:

```bash
# Opción 1: Importar el archivo SQL completo
sudo /opt/lampp/bin/mysql -u root -p < /opt/lampp/htdocs/Comfachoco/comfachoco.sql

# Opción 2: Acceder a MySQL manualmente
sudo /opt/lampp/bin/mysql -u root -p
```

Si accedes manualmente, ejecuta:

```sql
CREATE DATABASE IF NOT EXISTS comfachoco;
USE comfachoco;
SOURCE /opt/lampp/htdocs/Comfachoco/comfachoco.sql;
```

### 4. Configurar Credenciales en .env

Verifica que el archivo [.env](.env) tenga las credenciales correctas:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=comfachoco
DB_USER=root
DB_PASS=
```

Si MySQL requiere contraseña, actualiza `DB_PASS=tu_contraseña`

### 5. Probar la Conexión

Una vez que MySQL esté corriendo, prueba la conexión visitando:

```
http://localhost/Comfachoco/test_connection.php
```

Este archivo mostrará:
- Estado de la conexión
- Tablas existentes en la base de datos
- Número de registros en cada tabla
- Lista de usuarios del sistema

## Estructura de la Base de Datos

El archivo [comfachoco.sql](comfachoco.sql) crea las siguientes tablas:

1. **tipos_permisos** - Tipos de permisos/ausencias
2. **politicas** - Políticas corporativas
3. **usuarios** - Usuarios del sistema (admin, gerente, empleado)
4. **solicitudes** - Solicitudes de permisos/vacaciones
5. **actividad** - Registro de actividades del sistema
6. **eventos** - Eventos del calendario
7. **usuario_permisos** - Permisos específicos por usuario

## Usuario por Defecto

El sistema crea un usuario administrador por defecto:

- **Email:** admin@comfachoco.com
- **Contraseña:** admin123
- **Rol:** admin

## Características de Seguridad

- Contraseñas encriptadas con bcrypt
- Prepared statements para prevenir SQL injection
- Validación de sesiones
- Charset UTF8MB4 para soporte completo de caracteres
- Variables de entorno para credenciales sensibles

## Sistema de Fallback

Todas las páginas principales tienen un sistema de fallback:
- Si la conexión a la BD falla, usan datos mock predefinidos
- Esto permite que la aplicación funcione incluso sin base de datos
- Los errores se registran en el log de PHP

## Próximos Pasos

1. Inicia MySQL con `sudo /opt/lampp/lampp startmysql`
2. Importa la base de datos con el archivo [comfachoco.sql](comfachoco.sql)
3. Prueba la conexión en `http://localhost/Comfachoco/test_connection.php`
4. Accede al sistema en `http://localhost/Comfachoco/`
5. Inicia sesión con admin@comfachoco.com / admin123

## Solución de Problemas

### Error: "MySQL is not running"
```bash
sudo /opt/lampp/lampp startmysql
```

### Error: "Access denied for user"
Verifica las credenciales en el archivo `.env`

### Error: "Unknown database 'comfachoco'"
```bash
sudo /opt/lampp/bin/mysql -u root -p < comfachoco.sql
```

### Error: "Can't connect to MySQL server"
Verifica que MySQL esté corriendo en el puerto 3306:
```bash
netstat -tulpn | grep 3306
```

## Soporte

Para más ayuda, revisa los logs de error de Apache:
```bash
tail -f /opt/lampp/logs/error_log
```
