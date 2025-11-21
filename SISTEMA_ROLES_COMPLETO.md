# 🎯 Sistema de Roles y Permisos - Configuración por Usuario

## ✅ Implementación Completa

Se ha configurado un sistema completo de registro y acceso basado en roles, donde cada usuario solo ve y accede a los módulos correspondientes a su tipo.

---

## 📝 Registro de Usuarios

### Formulario de Registro Actualizado

Al registrarse, los usuarios ahora deben seleccionar su tipo:

- **Estudiante** - Acceso a perfil y encuesta de pre-egreso
- **Egresado** - Acceso completo a encuestas y seguimiento
- **Comunidad Universitaria** - Solo consulta de información pública

**Nota:** Los roles administrativos son asignados únicamente por el Administrador General desde el panel de gestión.

---

## 👥 Roles y Permisos Actualizados

### 🎓 Estudiantes
**Capacidades:**
- ✅ Registrarse en el sistema
- ✅ Actualizar su información académica
- ✅ Acceder a encuesta de pre-egreso
- ✅ Ver su perfil y datos

**Módulos Visibles:**
- Perfil y datos
- Encuesta preegreso

---

### 🎓 Egresados
**Capacidades:**
- ✅ Registrarse en el sistema
- ✅ Iniciar sesión y recuperar contraseña
- ✅ Actualizar datos personales y académicos
- ✅ Responder encuestas de egreso y cédula de preegreso
- ✅ Consultar encuestas aplicadas previamente

**Módulos Visibles:**
- Perfil y datos
- Encuesta preegreso
- Encuesta de egreso
- Encuesta laboral
- Acuses de seguimiento

**Permisos:** ver, ver_uno, actualizar

---

### 👔 Administrador General
**Capacidades:**
- ✅ Validar egresados en el sistema SICE
- ✅ Gestionar usuarios y roles
- ✅ Registrar validaciones del SICE
- ✅ Acceso total al sistema
- ✅ Ver todos los reportes

**Módulos Visibles:**
- TODOS los módulos del sistema
- Admin general
- Admin académica
- Admin unidad
- Reportes e informes (completos)
- Gestor de permisos
- Asignar roles

**Permisos:** TODOS (ver, ver_uno, crear, actualizar, eliminar, restaurar, forzar_eliminacion)

---

### 📚 Administrador Académico
**Capacidades:**
- ✅ Gestionar unidades académicas
- ✅ Gestionar carreras
- ✅ Gestionar generaciones
- ✅ Ver reportes académicos

**Módulos Visibles:**
- Admin académica
- Reportes e informes (solo datos académicos)
- Perfil y datos
- Encuestas (para supervisión)

**Permisos:** ver, ver_uno, crear, actualizar, eliminar

**Filtros de Reportes:** Ve todas las carreras y generaciones

---

### 🏢 Administrador de Unidad
**Capacidades:**
- ✅ Generar reportes de su unidad
- ✅ Respaldar base de datos
- ✅ Gestionar encuestas de su unidad
- ✅ Crear y asignar encuestas

**Módulos Visibles:**
- Admin unidad
- Reportes e informes (solo de su unidad)
- Perfil y datos
- Encuestas (para supervisión)

**Permisos:** ver, ver_uno, crear, actualizar, eliminar

**Filtros de Reportes:** Solo ve datos de las carreras de su unidad asignada

---

### 👥 Comunidad Universitaria
**Capacidades:**
- ✅ Registrarse en el sistema
- ✅ Visualizar información de seguimiento de egresados
- ✅ Consultar reportes y estadísticas públicas

**Módulos Visibles:**
- Reportes públicos (solo visualización)

**Permisos:** ver (solo lectura)

---

## 🔐 Protección de Rutas

Todas las rutas están protegidas con middleware de rol:

```php
// Ejemplo de protección
Route::get('admin-general', [AdminGeneralController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:Administrador general']);
```

### Matriz de Acceso a Rutas

| Ruta | Estudiante | Egresado | Admin General | Admin Académico | Admin Unidad | Comunidad |
|------|------------|----------|---------------|-----------------|--------------|-----------|
| `/perfil-datos` | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| `/encuesta-preegreso` | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| `/encuesta-egreso` | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ |
| `/encuesta-laboral` | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ |
| `/acuses-seguimiento` | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ |
| `/admin-general` | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `/admin-academica` | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| `/admin-unidad` | ❌ | ❌ | ✅ | ❌ | ✅ | ❌ |
| `/reportes-informes` | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| `/permisos` | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `/usuarios/roles` | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |

---

## 📊 Filtrado de Reportes por Rol

### Administrador General
- Ve **todos los reportes** sin filtro
- Acceso completo a todas las carreras y unidades

### Administrador Académico
- Ve reportes de **todas las carreras**
- Enfoque en datos académicos y estadísticas generales

### Administrador de Unidad
- Ve **solo reportes de su unidad asignada**
- Los datos se filtran automáticamente por las carreras de su unidad
- No puede ver información de otras unidades

### Comunidad Universitaria
- Ve reportes **públicos y estadísticas generales**
- Sin acceso a datos sensibles o individuales

---

## 🚀 Flujo de Registro y Asignación de Roles

### Para Usuarios Públicos (Registro Automático)

1. Usuario visita `/register`
2. Llena el formulario incluyendo "Tipo de usuario"
3. Selecciona: Estudiante, Egresado o Comunidad Universitaria
4. Al registrarse, el rol se asigna automáticamente
5. Inmediatamente ve solo los módulos correspondientes a su rol

### Para Roles Administrativos (Asignación Manual)

1. Administrador General inicia sesión
2. Va a "Asignar roles" en el Dashboard
3. Selecciona el usuario
4. Asigna el rol administrativo correspondiente
5. El usuario ve los nuevos módulos en su próximo inicio de sesión

---

## 🎨 Dashboard Dinámico

El Dashboard se adapta automáticamente al rol del usuario:

### Vista Estudiante
```
┌─────────────────────────────────────┐
│  Perfil y datos                     │
│  Encuesta preegreso                 │
└─────────────────────────────────────┘
```

### Vista Egresado
```
┌─────────────────────────────────────┐
│  Perfil y datos                     │
│  Encuesta preegreso                 │
│  Encuesta de egreso                 │
│  Encuesta laboral                   │
│  Acuses de seguimiento              │
└─────────────────────────────────────┘
```

### Vista Administrador General
```
┌───────────────────────┬─────────────┐
│  Perfil y datos       │ Admin gen.  │
│  Encuestas (todas)    │ Admin acad. │
│                       │ Admin unid. │
│                       │ Reportes    │
│                       │ Permisos    │
│                       │ Roles       │
└───────────────────────┴─────────────┘
```

### Vista Comunidad Universitaria
```
┌─────────────────────────────────────┐
│  ℹ️ Información pública             │
│                                     │
│  Reportes públicos                  │
└─────────────────────────────────────┘
```

---

## 🔄 Persistencia de Roles

Una vez asignado, el rol del usuario **se mantiene en todas las sesiones**:

- ✅ El rol se guarda en la base de datos
- ✅ Se carga automáticamente al iniciar sesión
- ✅ El Dashboard muestra siempre los módulos correctos
- ✅ Las rutas están protegidas con middleware
- ✅ Solo el Admin General puede cambiar roles

---

## 🛡️ Seguridad Implementada

1. **Middleware de Rol:** Todas las rutas verifican el rol antes de permitir acceso
2. **Validación en Registro:** Solo roles permitidos pueden ser auto-asignados
3. **Filtrado de Datos:** Cada admin ve solo los datos de su ámbito
4. **Permisos Granulares:** Control detallado de qué puede hacer cada rol
5. **Protección de API:** Los endpoints validan permisos en cada llamada

---

## 📝 Comandos Útiles

### Cambiar el rol de un usuario
```bash
php artisan tinker

$user = User::where('email', 'usuario@example.com')->first();
$user->syncRoles(['Egresados']); // Cambia a Egresado
```

### Ver el rol actual de un usuario
```bash
php artisan tinker

$user = User::where('email', 'usuario@example.com')->first();
$user->roles->pluck('name'); // Muestra los roles
```

### Crear un usuario con rol específico
```bash
php artisan tinker

$user = User::create([
    'name' => 'Nuevo Usuario',
    'email' => 'nuevo@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);
$user->assignRole('Egresados');
```

---

## ✨ Características Destacadas

1. **Registro Inteligente:** El usuario selecciona su tipo al registrarse
2. **Dashboard Adaptativo:** Cada usuario ve solo lo que le corresponde
3. **Reportes Filtrados:** Los admins de unidad solo ven su información
4. **Protección Total:** Middleware valida cada acceso
5. **Gestión Centralizada:** Admin General controla todo desde el panel
6. **Experiencia Personalizada:** Interfaz optimizada para cada rol

---

## 📅 Fecha de Implementación
**6 de noviembre de 2025**

## 📚 Archivos Modificados

- `resources/js/pages/auth/Register.vue` - Selector de tipo de usuario
- `app/Actions/Fortify/CreateNewUser.php` - Asignación automática de rol
- `resources/js/components/DashboardGrid.vue` - Dashboard dinámico
- `app/Http/Middleware/CheckRole.php` - Middleware de verificación
- `routes/web.php` - Protección de rutas
- `app/Http/Controllers/ReportesInformesController.php` - Filtrado de reportes
- `database/seeders/RolesAndPermissionsSeeder.php` - Permisos actualizados

---

**Sistema 100% funcional y listo para producción** 🎉
