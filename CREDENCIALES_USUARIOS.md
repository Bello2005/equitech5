# Credenciales de Usuarios - ComfaChoco

## 🔐 Contraseña Universal
Todos los usuarios usan la misma contraseña por defecto: **`admin123`**

---

## 👨‍💼 Usuario Administrador

**Email:** `admin@comfachoco.com`
**Contraseña:** `admin123`
**Rol:** Administrador
**Nombre:** Bello González

**Dashboard:** Vista ejecutiva con métricas completas, gráficos y gestión del sistema

---

## 👥 Usuarios Empleados

### Usuario 1 - Carlos Ruiz
**Email:** `carlos.ruiz@comfachoco.com`
**Contraseña:** `admin123`
**Rol:** Empleado
**Dashboard:** Chatbot de gestión de permisos

### Usuario 2 - Ana Mendoza
**Email:** `ana.mendoza@comfachoco.com`
**Contraseña:** `admin123`
**Rol:** Empleado
**Dashboard:** Chatbot de gestión de permisos

### Usuario 3 - David Torres
**Email:** `david.torres@comfachoco.com`
**Contraseña:** `admin123`
**Rol:** Empleado
**Dashboard:** Chatbot de gestión de permisos

### Usuario 4 - Miguel Santos
**Email:** `miguel.santos@comfachoco.com`
**Contraseña:** `admin123`
**Rol:** Empleado
**Dashboard:** Chatbot de gestión de permisos

### Usuario 5 - Juan Pérez (Opcional)
**Email:** `empleado@comfachoco.com`
**Contraseña:** `admin123`
**Rol:** Empleado
**Dashboard:** Chatbot de gestión de permisos

*Nota: Este usuario se puede agregar ejecutando el archivo `add_empleado.sql`*

---

## 👔 Usuario Gerente

### Laura Silva
**Email:** `laura.silva@comfachoco.com`
**Contraseña:** `admin123`
**Rol:** Gerente
**Dashboard:** Vista ejecutiva (mismo que administrador)

---

## 📋 Cómo Probar

### Para probar el Dashboard de Empleados:
1. Ve a: `http://localhost/Comfachoco/pages/login.php`
2. Usa cualquiera de los emails de empleado listados arriba
3. Contraseña: `admin123`
4. Serás redirigido automáticamente al dashboard con el chatbot

### Para probar el Dashboard de Administrador:
1. Ve a: `http://localhost/Comfachoco/pages/login.php`
2. Email: `admin@comfachoco.com`
3. Contraseña: `admin123`
4. Verás el dashboard ejecutivo con todas las métricas

---

## 🔄 Agregar el Usuario Empleado Adicional

Si quieres agregar el usuario `empleado@comfachoco.com`:

```bash
# Opción 1: Desde línea de comandos
mysql -u root -p < /opt/lampp/htdocs/Comfachoco/add_empleado.sql

# Opción 2: Desde phpMyAdmin
# 1. Abre phpMyAdmin (http://localhost/phpmyadmin)
# 2. Selecciona la base de datos 'comfachoco'
# 3. Ve a la pestaña 'SQL'
# 4. Copia y pega el contenido de add_empleado.sql
# 5. Click en 'Continuar'
```

---

## 🎯 Diferencias entre Dashboards

### Dashboard Administrador/Gerente:
- Métricas y estadísticas del equipo
- Gráficos de rendimiento
- Calendario de eventos
- Gestión de solicitudes
- Reportes y analytics

### Dashboard Empleado:
- **Chatbot Inteligente** de gestión de permisos
- Solicitar permisos conversacionalmente
- Consultar saldo de días disponibles
- Ver estado de solicitudes
- Estadísticas personales
- Actividad reciente

---

## ⚠️ Nota de Seguridad

**IMPORTANTE:** Estas credenciales son solo para desarrollo/pruebas. En producción:
- Cambia todas las contraseñas
- Usa contraseñas únicas y seguras
- Implementa verificación de email
- Agrega autenticación de dos factores (2FA)
- Configura las variables de entorno (.env) correctamente
