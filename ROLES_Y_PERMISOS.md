# Sistema de Roles y Permisos - Documentación

## Instalación Completada

Se ha instalado y configurado exitosamente el sistema de roles y permisos usando **Spatie Laravel Permission**.

## Componentes Instalados

### 1. Paquete Base
- **spatie/laravel-permission** v6.23.0
- Migraciones ejecutadas correctamente
- Configuración publicada en `config/permission.php`

### 2. Roles Creados

El sistema incluye 6 roles predefinidos:

1. **Estudiantes** - Permisos de solo lectura básica
2. **Egresados** - Permisos de lectura y actualización de sus datos
3. **Administrador general** - Acceso total a todos los permisos
4. **Administrador de unidad** - Permisos de administración limitada
5. **Administrador academico** - Permisos de administración académica
6. **Comunidad universitaria** - Permisos básicos de visualización

### 3. Permisos Definidos

Se han creado 7 permisos básicos:

- `ver` - Ver listados
- `ver_uno` - Ver detalle individual
- `crear` - Crear nuevos registros
- `actualizar` - Actualizar registros existentes
- `eliminar` - Eliminar registros (soft delete)
- `restaurar` - Restaurar registros eliminados
- `forzar_eliminacion` - Eliminar permanentemente

### 4. Asignación de Permisos por Rol

#### Administrador general
- ✅ Todos los permisos

#### Administrador de unidad
- ✅ ver, ver_uno, crear, actualizar

#### Administrador academico
- ✅ ver, ver_uno, crear, actualizar

#### Egresados
- ✅ ver, ver_uno, actualizar

#### Estudiantes
- ✅ ver, ver_uno

#### Comunidad universitaria
- ✅ ver

## Archivos Creados/Modificados

### Backend (Laravel)

1. **app/Models/User.php**
   - Agregado trait `HasRoles` de Spatie
   - Ahora el modelo User puede manejar roles y permisos

2. **app/Policies/UnidadPolicy.php**
   - Policy creada para el modelo Unidad
   - Implementa verificación de permisos para todas las acciones CRUD

3. **app/Providers/AppServiceProvider.php**
   - Registrada la política de Unidad

4. **app/Http/Controllers/PermissionController.php**
   - Controlador para gestionar roles y permisos
   - Métodos:
     - `index()` - Muestra la interfaz de gestión
     - `updateRolePermissions()` - Actualiza permisos de un rol
     - `getRolesWithPermissions()` - API para obtener roles y permisos
     - `assignRoleToUser()` - Asigna un rol a un usuario

5. **app/Http/Middleware/HandleInertiaRequests.php**
   - Compartido roles y permisos del usuario con el frontend

6. **database/seeders/RolesAndPermissionsSeeder.php**
   - Seeder para crear roles y permisos iniciales

7. **routes/web.php**
   - Agregadas rutas para gestión de permisos:
     - `GET /permisos` - Interfaz de gestión
     - `POST /permisos/roles/{role}` - Actualizar permisos
     - `GET /permisos/api/roles` - API de roles
     - `POST /permisos/asignar-rol` - Asignar rol

### Frontend (Vue)

1. **resources/js/components/PermissionManager.vue**
   - Componente Vue con interfaz de checkboxes
   - Permite administrar permisos de cada rol
   - Incluye botones para seleccionar/deseleccionar todos

2. **resources/js/pages/Permissions/Manager.vue**
   - Página Inertia para el gestor de permisos
   - Usa el layout estándar de la aplicación

3. **resources/js/components/DashboardGrid.vue**
   - Agregado enlace al "Gestor de permisos"
   - Solo visible para usuarios con roles administrativos

## Uso del Sistema

### 1. Verificar Roles de un Usuario

```php
// En cualquier parte del código backend
$user = auth()->user();

// Verificar si tiene un rol específico
if ($user->hasRole('Administrador general')) {
    // código
}

// Verificar si tiene alguno de varios roles
if ($user->hasAnyRole(['Administrador general', 'Administrador de unidad'])) {
    // código
}
```

### 2. Verificar Permisos

```php
// Verificar un permiso específico
if ($user->hasPermissionTo('crear')) {
    // código
}

// Verificar múltiples permisos
if ($user->hasAllPermissions(['ver', 'crear', 'actualizar'])) {
    // código
}
```

### 3. Asignar Roles a Usuarios

```php
// Asignar un rol
$user->assignRole('Egresados');

// Asignar múltiples roles
$user->assignRole(['Egresados', 'Estudiantes']);

// Sincronizar roles (reemplaza todos los roles existentes)
$user->syncRoles(['Administrador general']);
```

### 4. Usar Policies en Controladores

```php
// En un controlador
public function index()
{
    $this->authorize('viewAny', Unidad::class);
    // código
}

public function store(Request $request)
{
    $this->authorize('create', Unidad::class);
    // código
}

public function update(Request $request, Unidad $unidad)
{
    $this->authorize('update', $unidad);
    // código
}
```

### 5. Usar en Blade/Inertia

```php
// En controladores o middlewares
@can('ver')
    <!-- código -->
@endcan

@role('Administrador general')
    <!-- código -->
@endrole
```

### 6. Acceder desde el Frontend (Vue)

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const userRoles = page.props.auth.roles;
const userPermissions = page.props.auth.permissions;

// Verificar rol
const isAdmin = userRoles.includes('Administrador general');
</script>

<template>
  <div v-if="isAdmin">
    <!-- contenido solo para admin -->
  </div>
</template>
```

## Acceso al Gestor de Permisos

1. Inicia sesión con un usuario que tenga rol administrativo
2. Accede al Dashboard
3. En la columna derecha, verás el módulo "Gestor de permisos"
4. Click en el módulo para acceder a la interfaz de gestión
5. Marca/desmarca permisos para cada rol
6. Click en "Guardar cambios" para aplicar los cambios

## Restricciones de Seguridad

- Solo usuarios con roles administrativos pueden ver el gestor de permisos
- Solo el **Administrador general** puede modificar permisos de roles
- Todos los cambios se registran en la base de datos
- Los permisos se cachean automáticamente para mejor rendimiento

## Seeder de Prueba

Para ejecutar el seeder y crear/actualizar roles y permisos:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

## Usuario de Prueba

El usuario de prueba (jortega8159@gmail.com) ha sido asignado automáticamente al rol **Administrador general**, por lo que tiene acceso completo al sistema.

## Próximos Pasos Recomendados

1. **Crear más Policies** para otros modelos importantes
2. **Personalizar permisos** según las necesidades específicas del negocio
3. **Agregar middleware** a rutas que requieran permisos específicos
4. **Implementar log de auditoría** para cambios en roles y permisos
5. **Crear formularios** para asignar roles a usuarios desde la interfaz

## Comandos Útiles

```bash
# Limpiar caché de permisos
php artisan permission:cache-reset

# Crear un nuevo permiso
php artisan tinker
>>> Permission::create(['name' => 'nuevo_permiso']);

# Crear un nuevo rol
>>> Role::create(['name' => 'Nuevo Rol']);

# Asignar permiso a rol
>>> $rol = Role::findByName('Nombre del Rol');
>>> $rol->givePermissionTo('nombre_permiso');
```

## Documentación Adicional

Para más información sobre las capacidades del paquete Spatie:
https://spatie.be/docs/laravel-permission/v6/introduction

---

**Instalación completada el:** 6 de noviembre de 2025
**Versión del paquete:** spatie/laravel-permission v6.23.0
