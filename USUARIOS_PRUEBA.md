# 🔐 Usuarios de Prueba - Roles y Permisos

## Credenciales de Acceso

Todos los usuarios tienen la contraseña: **`password`**

### 👑 Administrador General
- **Email:** `jortega8159@gmail.com`
- **Contraseña:** `password`
- **Permisos:** ✅ TODOS (ver, ver_uno, crear, actualizar, eliminar, restaurar, forzar_eliminacion)
- **Accesos:** Panel completo, gestionar permisos, asignar roles

### 🏢 Administrador de Unidad
- **Email:** `admin.unidad@example.com`
- **Contraseña:** `password`
- **Permisos:** ✅ ver, ver_uno, crear, actualizar
- **Accesos:** Gestión de unidad, ver gestores de permisos

### 📚 Administrador Académico
- **Email:** `admin.academico@example.com`
- **Contraseña:** `password`
- **Permisos:** ✅ ver, ver_uno, crear, actualizar
- **Accesos:** Gestión académica, ver gestores de permisos

### 🎓 Egresado
- **Email:** `egresado@example.com`
- **Contraseña:** `password`
- **Permisos:** ✅ ver, ver_uno, actualizar
- **Accesos:** Ver datos, actualizar su perfil

### 📖 Estudiante
- **Email:** `estudiante@example.com`
- **Contraseña:** `password`
- **Permisos:** ✅ ver, ver_uno
- **Accesos:** Solo lectura

### 👥 Comunidad Universitaria
- **Email:** `comunidad@example.com`
- **Contraseña:** `password`
- **Permisos:** ✅ ver
- **Accesos:** Vista básica de listados

---

## 🎯 Cómo Probar los Roles

### Opción 1: Iniciar Sesión con Diferentes Usuarios
1. Cierra sesión de tu cuenta actual
2. Inicia sesión con cualquiera de los emails de arriba
3. Contraseña: `password`
4. Explora las diferencias en permisos y accesos

### Opción 2: Cambiar Roles desde el Panel de Admin
1. Inicia sesión como **Administrador General** (`jortega8159@gmail.com`)
2. Ve al Dashboard
3. Click en **"Asignar roles"** (módulo nuevo en la columna derecha)
4. Selecciona un usuario
5. Click en "Gestionar roles"
6. Marca/desmarca los roles que quieras asignar
7. Guarda cambios

### Opción 3: Usar Artisan Tinker
```bash
php artisan tinker

# Asignar un rol a tu usuario actual
$user = User::where('email', 'jortega8159@gmail.com')->first();
$user->syncRoles(['Estudiantes']); // Cambia el rol

# Verificar roles
$user->roles->pluck('name');

# Volver a Administrador General
$user->syncRoles(['Administrador general']);
```

---

## 📋 Matriz de Permisos por Rol

| Permiso | Admin General | Admin Unidad | Admin Académico | Egresado | Estudiante | Comunidad |
|---------|---------------|--------------|-----------------|----------|------------|-----------|
| ver | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| ver_uno | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| crear | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| actualizar | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| eliminar | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| restaurar | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| forzar_eliminacion | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 🚀 Nuevos Módulos Disponibles (Solo Admins)

En el Dashboard, columna derecha, ahora verás:

1. **Gestor de permisos** 🎨
   - Administrar qué permisos tiene cada rol
   - Solo Administrador General puede modificar

2. **Asignar roles** 👥
   - Ver todos los usuarios del sistema
   - Asignar/remover roles a usuarios
   - Ver qué roles tiene cada usuario

---

## 💡 Comandos Útiles

### Crear un nuevo usuario
```bash
php artisan tinker

User::create([
    'name' => 'Nuevo Usuario',
    'email' => 'nuevo@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);
```

### Asignar rol a un usuario
```bash
php artisan tinker

$user = User::where('email', 'nuevo@example.com')->first();
$user->assignRole('Egresados');
```

### Ver roles de un usuario
```bash
php artisan tinker

$user = User::where('email', 'jortega8159@gmail.com')->first();
$user->getRoleNames();
```

### Ver permisos de un usuario
```bash
php artisan tinker

$user = User::where('email', 'jortega8159@gmail.com')->first();
$user->getAllPermissions()->pluck('name');
```

### Recrear usuarios de prueba
```bash
php artisan db:seed --class=TestUsersSeeder
```

---

## ⚠️ Notas Importantes

- Todos los usuarios tienen email verificado
- La contraseña por defecto es `password` para todos
- Un usuario puede tener múltiples roles
- Los permisos son acumulativos (si tiene 2 roles, suma los permisos de ambos)
- Solo el Administrador General puede modificar permisos de roles
- Los cambios en roles/permisos se reflejan inmediatamente

---

**Fecha de creación:** 6 de noviembre de 2025
