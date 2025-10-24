# 🚀 Cómo Probar los Dashboards Separados

## ✅ Sistema Implementado

El sistema ahora tiene **dos dashboards diferentes** según el rol del usuario:

1. **Dashboard Administrador/Gerente** - Vista ejecutiva completa
2. **Dashboard Empleado** - Chatbot de gestión de permisos

---

## 🔐 Credenciales de Prueba

### Para probar el Dashboard de EMPLEADO (Chatbot):

Puedes usar cualquiera de estos usuarios:

| Email | Contraseña | Nombre |
|-------|------------|--------|
| `carlos.ruiz@comfachoco.com` | `admin123` | Carlos Ruiz |
| `ana.mendoza@comfachoco.com` | `admin123` | Ana Mendoza |
| `david.torres@comfachoco.com` | `admin123` | David Torres |
| `miguel.santos@comfachoco.com` | `admin123` | Miguel Santos |

### Para probar el Dashboard de ADMINISTRADOR:

| Email | Contraseña | Nombre |
|-------|------------|--------|
| `admin@comfachoco.com` | `admin123` | Bello González |

---

## 📝 Pasos para Probar

### 1️⃣ Probar Dashboard de Empleado

1. Abre tu navegador en: `http://localhost/Comfachoco/pages/login.php`

2. Ingresa credenciales de empleado:
   - **Email:** `carlos.ruiz@comfachoco.com`
   - **Contraseña:** `admin123`

3. Click en "Iniciar Sesión"

4. **Serás redirigido automáticamente** a `empleado_dashboard.php`

5. Verás:
   - 🤖 **Chatbot inteligente** de permisos
   - 📊 Estadísticas personales (días disponibles, aprobadas, pendientes)
   - 📋 Actividad reciente
   - 💡 Tarjeta de ayuda

### 2️⃣ Probar el Chatbot

Una vez en el dashboard de empleado, prueba estos mensajes:

**Mensaje 1:** "Quiero solicitar un permiso"
- El bot te mostrará los tipos de permisos disponibles

**Mensaje 2:** "¿Cuántos días tengo disponibles?"
- El bot te mostrará tu saldo de vacaciones y permisos

**Mensaje 3:** "Estado de mis solicitudes"
- El bot te mostrará tus solicitudes aprobadas, pendientes y rechazadas

**Mensaje 4:** "Políticas de permisos"
- El bot te explicará las reglas y requisitos

**O usa los botones de acciones rápidas:**
- 📅 Solicitar permiso
- ❓ Ver permisos disponibles
- ✅ Mis solicitudes

### 3️⃣ Probar Dashboard de Administrador

1. **Cierra sesión** (click en tu avatar → Cerrar Sesión)

2. Vuelve al login: `http://localhost/Comfachoco/pages/login.php`

3. Ingresa credenciales de administrador:
   - **Email:** `admin@comfachoco.com`
   - **Contraseña:** `admin123`

4. Click en "Iniciar Sesión"

5. Verás el **dashboard ejecutivo** con:
   - 📊 Métricas y KPIs
   - 📈 Gráficos de rendimiento
   - 📅 Calendario de eventos
   - 👥 Gestión de equipo

---

## 🎯 Características del Chatbot de Empleados

### ✨ Funcionalidades Implementadas:

1. **Solicitar Permisos**
   - Vacaciones
   - Permiso médico
   - Permiso personal
   - Calamidad doméstica

2. **Consultar Información**
   - Saldo de días disponibles
   - Estado de solicitudes
   - Políticas de la empresa

3. **Interfaz Inteligente**
   - Respuestas contextuales
   - Botones de acciones rápidas
   - Indicador de escritura
   - Scroll automático
   - Resetear conversación

### 💡 Ejemplos de Conversación:

```
Usuario: "Hola"
Bot: ¡Hola! 👋 Soy tu asistente virtual...

Usuario: "Quiero pedir vacaciones"
Bot: 🏖️ Solicitud de Vacaciones. Tienes 12 días disponibles...

Usuario: "Del 15 al 20 de diciembre"
Bot: [Procesaría la solicitud]
```

---

## 🔄 Sistema de Redirección Automática

El sistema redirige automáticamente según el rol:

- **Empleados** → `empleado_dashboard.php` (Chatbot)
- **Administradores/Gerentes** → `dashboard.php` (Ejecutivo)

Esto se hace en [dashboard.php:12-15](pages/dashboard.php#L12-L15):

```php
// Redirigir empleados a su dashboard específico
if ($usuario['rol'] === 'empleado') {
    header('Location: empleado_dashboard.php');
    exit;
}
```

---

## 🎨 Diferencias Visuales

### Dashboard Empleado:
- ✅ Diseño simplificado y enfocado
- 🤖 Chatbot como elemento principal
- 📊 Estadísticas personales
- 🎯 Acciones rápidas

### Dashboard Administrador:
- ✅ Vista completa del sistema
- 📈 Gráficos y analytics
- 👥 Gestión de equipo
- 📅 Calendario compartido

---

## ⚡ Prueba Rápida (Copiar y Pegar)

### Login como Empleado:
```
URL: http://localhost/Comfachoco/pages/login.php
Email: carlos.ruiz@comfachoco.com
Password: admin123
```

### Login como Admin:
```
URL: http://localhost/Comfachoco/pages/login.php
Email: admin@comfachoco.com
Password: admin123
```

---

## 🐛 Solución de Problemas

### Problema: "No redirige al dashboard de empleado"
**Solución:** Verifica que el usuario tenga rol 'empleado' en la base de datos

### Problema: "Error al cargar empleado_dashboard.php"
**Solución:** Verifica que el archivo existe en `/opt/lampp/htdocs/Comfachoco/pages/`

### Problema: "El chatbot no responde"
**Solución:** Verifica que Alpine.js esté cargado correctamente (ver consola del navegador)

---

## 📁 Archivos Relacionados

- `pages/dashboard.php` - Dashboard administrador + redirección
- `pages/empleado_dashboard.php` - Dashboard empleado con chatbot
- `database.sql` - Estructura de BD con usuarios
- `add_empleado.sql` - Script para agregar usuario empleado adicional
- `CREDENCIALES_USUARIOS.md` - Lista completa de credenciales

---

## ✅ Checklist de Prueba

- [ ] Login como empleado
- [ ] Ver dashboard con chatbot
- [ ] Enviar mensaje al chatbot
- [ ] Probar acciones rápidas
- [ ] Ver estadísticas personales
- [ ] Cerrar sesión
- [ ] Login como administrador
- [ ] Ver dashboard ejecutivo
- [ ] Verificar que empleados no pueden acceder al dashboard admin

---

¡Todo listo para probar! 🎉
