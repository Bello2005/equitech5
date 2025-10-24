# Guía de Configuración de Variables de Entorno

## ¿Qué son las Variables de Entorno?

Las variables de entorno permiten separar la configuración sensible del código fuente, mejorando la seguridad y facilitando el despliegue en diferentes ambientes (desarrollo, staging, producción).

## Archivos del Sistema

### `.env` (NO versionado en Git)
Contiene los valores reales de configuración. Este archivo es único para cada instalación.

### `.env.example` (SÍ versionado en Git)
Plantilla con todas las variables necesarias pero sin valores sensibles. Sirve como documentación.

## Configuración Inicial

1. **Copiar el archivo de ejemplo**:
   ```bash
   cp .env.example .env
   ```

2. **Editar valores en `.env`**:
   ```bash
   nano .env
   # o usa tu editor favorito
   ```

## Variables Disponibles

### Base de Datos
```env
DB_HOST=localhost          # Host del servidor MySQL
DB_PORT=3306              # Puerto de MySQL
DB_NAME=comfachoco        # Nombre de la base de datos
DB_USER=root              # Usuario de MySQL
DB_PASS=                  # Contraseña de MySQL (vacío para XAMPP local)
```

### Aplicación
```env
APP_NAME="ComfaChoco International"  # Nombre de la aplicación
APP_ENV=development                  # development o production
APP_DEBUG=true                       # Mostrar errores (false en producción)
APP_URL=http://localhost/Comfachoco  # URL base de la aplicación
```

### Sesiones
```env
SESSION_LIFETIME=7200     # Duración de sesión en segundos (7200 = 2 horas)
SESSION_SECURE=false      # true solo si usas HTTPS
SESSION_HTTP_ONLY=true    # Protección XSS (siempre true)
```

### Seguridad
```env
APP_KEY=base64:...        # Clave de encriptación única
HASH_ALGORITHM=bcrypt     # Algoritmo para hashear contraseñas
```

### Zona Horaria
```env
TIMEZONE=-05:00           # Offset de zona horaria (Colombia UTC-5)
                         # Usar formato offset: -05:00, +00:00, etc.
                         # NO usar nombres como 'America/Bogota' (no compatibles con MySQL)
```

## Ambientes Recomendados

### Desarrollo Local
```env
APP_ENV=development
APP_DEBUG=true
SESSION_SECURE=false
DB_HOST=localhost
DB_USER=root
DB_PASS=
```

### Producción
```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE=true
DB_HOST=mysql.tuservidor.com
DB_USER=usuario_db
DB_PASS=contraseña_segura_aqui
```

## Generar APP_KEY

La variable `APP_KEY` debe ser única para cada instalación. Puedes generarla con:

```bash
# Usando PHP
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"

# Usando OpenSSL
openssl rand -base64 32
```

Copia el resultado y pégalo en tu `.env`:
```env
APP_KEY=base64:tu_clave_generada_aqui
```

## Uso en el Código

### Obtener una variable
```php
// Usando la función helper
$dbHost = env('DB_HOST', 'localhost');

// Usando la clase directamente
$appName = Env::get('APP_NAME', 'Default Name');
```

### Verificar si existe
```php
if (env('APP_DEBUG')) {
    // Código de debug
}
```

## Mejores Prácticas

### ✅ HACER
- Usar `.env` para toda configuración sensible
- Mantener `.env.example` actualizado
- Generar `APP_KEY` único para cada instalación
- Usar `APP_DEBUG=false` en producción
- Incluir `.env` en `.gitignore`
- Hacer backup del `.env` de producción

### ❌ NO HACER
- NO versionar el archivo `.env` en Git
- NO compartir tu `.env` con nadie
- NO usar la misma `APP_KEY` en múltiples instalaciones
- NO dejar `APP_DEBUG=true` en producción
- NO hardcodear credenciales en el código

## Solución de Problemas

### Error: "Archivo .env no encontrado"
```bash
# Verificar que existe
ls -la .env

# Si no existe, crearlo desde la plantilla
cp .env.example .env
```

### Error: "Cannot connect to database"
```bash
# Verificar credenciales en .env
cat .env | grep DB_

# Probar conexión directa
mysql -h localhost -u root -p
```

### Variables no se cargan
```php
// Verificar que se carga el archivo env.php
require_once __DIR__ . '/config/env.php';

// Ver variables cargadas
print_r(Env::all());
```

## Seguridad Adicional

### Permisos del archivo .env
```bash
# Solo el owner puede leer/escribir
chmod 600 .env

# Verificar permisos
ls -la .env
# Debería mostrar: -rw------- 1 usuario grupo
```

### Encriptación del .env (Opcional)
Para mayor seguridad en producción, considera encriptar el archivo `.env`:

```bash
# Encriptar
openssl enc -aes-256-cbc -salt -in .env -out .env.enc

# Desencriptar
openssl enc -d -aes-256-cbc -in .env.enc -out .env
```

## Checklist de Despliegue

Antes de desplegar a producción, verifica:

- [ ] Archivo `.env` creado en el servidor
- [ ] Todas las variables configuradas correctamente
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `SESSION_SECURE=true` (si usas HTTPS)
- [ ] `APP_KEY` único generado
- [ ] Credenciales de BD correctas
- [ ] Permisos del archivo `.env` configurados (600)
- [ ] `.env` NO está en el repositorio Git
- [ ] Backup del `.env` guardado en lugar seguro
