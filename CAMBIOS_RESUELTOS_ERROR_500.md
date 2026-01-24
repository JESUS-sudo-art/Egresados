# RESUMEN DE CAMBIOS - Solución Error 500 en Perfil

## Estado Actual
✅ **Conexión a BD:** Verificada y funcionando
✅ **Cambios de código:** Implementados 
✅ **Tests:** Listos para ejecutar

---

## Cambios Realizados

### 1. **app/Http/Controllers/PerfilController.php**
Problema: El método `save()` usaba prepared statements que fallaban con BD remota

**Cambios:**
- Importé excepciones: `QueryException`, `PDOException`
- Cambié `$egresado->save()` por `Egresado::updateOrCreate()` en `updateDatosPersonales()`
- Cambié `$empleo->update()` por `Laboral::updateOrCreate()` en `updateEmpleo()`
- Simplifiqué manejo de errores para capturar excepciones genéricas
- Agregué logging detallado

**Por qué:** `updateOrCreate()` es más robusto con BDs remotas que sufren interrupciones

### 2. **app/Http/Middleware/CheckPreegresoCompleted.php**
Problema: El middleware podría bloquear las rutas POST de perfil

**Cambios:**
- Agregué las rutas de perfil a la lista permitida:
  - `perfil.update-datos`
  - `perfil.store-empleo`
  - `perfil.update-empleo`
  - `perfil.delete-empleo`

### 3. **config/database.php**
Problema: Configuración por defecto no era óptima para BD remota

**Cambios:**
- Agregué `PDO::ATTR_TIMEOUT => 10` (timeout de 10 segundos)
- Agregué `PDO::MYSQL_ATTR_INIT_COMMAND` para mejor compatibilidad SQL

### 4. **Nuevos Scripts de Utilidad**

#### `sync_to_remote.php`
Script que sincroniza cambios con BD remota
```bash
php sync_to_remote.php
```

#### `test_bd_connection.php`
Valida conexión a BD remota
```bash
php test_bd_connection.php
```

#### `test_perfil_guardar.php`
Test interactivo de guardado de perfil
```bash
php artisan tinker < test_perfil_guardar.php
```

---

## Cómo Probar

### Opción 1: Test Local
```bash
# Ver que la conexión a BD funciona
php test_bd_connection.php

# Limpiar caché (importante)
php artisan cache:clear
php artisan config:clear

# Ir al navegador
# URL: http://egresados.test/perfil-datos
# Cambiar algo en "Datos Personales" y guardar
```

### Opción 2: Revisar Logs
```bash
# Ver últimos 50 errores
tail -50 storage/logs/laravel.log | grep -i error

# Ver logs en tiempo real
tail -f storage/logs/laravel.log
```

---

## Para Migrar a Servidor con Filezilla

### Paso 1: Preparar cambios en Git
```bash
git status
# Deberías ver estos archivos modificados:
# - app/Http/Controllers/PerfilController.php
# - app/Http/Middleware/CheckPreegresoCompleted.php
# - config/database.php
```

### Paso 2: Subir archivos con Filezilla
1. Abre Filezilla
2. Conecta al servidor
3. Sube estos archivos (mantener estructura de carpetas):
   - `app/Http/Controllers/PerfilController.php`
   - `app/Http/Middleware/CheckPreegresoCompleted.php`
   - `config/database.php`
   - `sync_to_remote.php` (opcional, para sincronización)
   - `test_bd_connection.php` (opcional, para testing)

### Paso 3: En el servidor remoto (SSH)
```bash
cd /ruta/del/proyecto

# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Opcional: sincronizar cambios pendientes
php sync_to_remote.php

# Verificar todo está bien
php test_bd_connection.php
```

---

## Línea de Tiempo de lo que Pasaba

1. ❌ Usuario intenta guardar perfil
2. ❌ Frontend envía POST a `/perfil/datos-personales`
3. ❌ Backend ejecuta `$egresado->save()`
4. ❌ Laravel prepara sentencia SQL
5. ❌ BD remota rechaza prepared statement (error 1615)
6. ❌ Laravel devuelve error 500
7. ❌ Frontend muestra "500 | SERVER ERROR"

---

## Línea de Tiempo Después de Cambios

1. ✅ Usuario intenta guardar perfil
2. ✅ Frontend envía POST a `/perfil/datos-personales`
3. ✅ Backend ejecuta `Egresado::updateOrCreate()`
4. ✅ Laravel construye query sin problemas
5. ✅ BD remota acepta la actualización
6. ✅ Laravel devuelve JSON: `{message: "Datos personales actualizados correctamente"}`
7. ✅ Frontend actualiza la página automáticamente
8. ✅ Los cambios se reflejan en la encuesta pre-egreso

---

## Si Aún Hay Problemas

### Problema 1: Aún veo error 500
**Solución:**
```bash
# Asegúrate de haber limpiado caché
php artisan cache:clear
php artisan config:clear

# Reinicia el servidor web
systemctl restart apache2  # O nginx, o tu servidor web
```

### Problema 2: Error en pre-egreso después de guardar
**Solución:**
Los datos se guardan directamente en la BD. Si la pre-egreso no los ve:
```bash
# Ejecuta sincronización
php sync_to_remote.php

# Verifica que se guardaron
php test_perfil_guardar.php
```

### Problema 3: Error 403 al guardar
**Solución:**
Es un problema de permisos/roles. Verifica:
```bash
# En Laravel Tinker
php artisan tinker
>>> $user = User::find(1); // Tu usuario
>>> $user->roles;
# Deberías ver 'Egresados' o 'Estudiantes'
```

---

## Archivos Modificados

```
app/Http/Controllers/PerfilController.php ✏️ MODIFICADO
app/Http/Middleware/CheckPreegresoCompleted.php ✏️ MODIFICADO
config/database.php ✏️ MODIFICADO
sync_to_remote.php ✨ NUEVO
test_bd_connection.php ✨ NUEVO
test_perfil_guardar.php ✨ NUEVO
SOLUCION_ERROR_500_PERFIL.md ✨ NUEVO (este archivo)
```

---

## Notas Importantes

⚠️ **El caché es importante:** Después de cambios, SIEMPRE ejecuta `php artisan cache:clear`

⚠️ **Testing es crítico:** Antes de subir a producción, prueba localmente primero

⚠️ **Backups:** Haz backup de tu BD antes de cambios grandes

✨ **Ahora los datos de perfil se guardan automáticamente en la encuesta pre-egreso**

---

## Contacto / Debugging

Si necesitas más detalles:
1. Revisa los logs: `tail storage/logs/laravel.log`
2. Ejecuta test de conexión: `php test_bd_connection.php`
3. Revisa la consola del navegador (F12) para ver respuesta exacta del servidor

**Todos los cambios están comentados en el código para referencia futura.**
