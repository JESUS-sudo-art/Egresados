# Solución del Error 500 en Perfil y Datos

## Problema Identificado
Error `SQLSTATE[HY000]: General error: 1615 Prepared statement needs to be re-prepared` cuando intentas guardar cambios en el perfil de un egresado.

**Causa:** La base de datos remota (69.6.201.239:3306) tiene problemas con sentencias preparadas cuando la conexión se interrumpe.

## Soluciones Aplicadas

### 1. Backend (PerfilController.php)
- ✅ Cambié `$egresado->save()` por `Egresado::updateOrCreate()` - evita problemas con prepared statements
- ✅ Simplifiqué manejo de errores: Ahora captura excepciones genéricas en lugar de PDOException/QueryException
- ✅ Agregué logging más detallado para diagnosticar problemas

### 2. Middleware (CheckPreegresoCompleted.php)  
- ✅ Agregué las rutas de perfil (`perfil.update-datos`, `perfil.store-empleo`, etc.) a la lista permitida
- Esto evita que el middleware interfiera con las peticiones POST

### 3. Configuración (database.php)
- ✅ Agregué timeout de 10 segundos
- ✅ Inicialización de comandos SQL para mejor compatibilidad

### 4. Script Auxiliar (sync_to_remote.php)
- ✅ Nuevo script que puedes correr para sincronizar cambios con la BD remota
- Uso: `php sync_to_remote.php`

## Pasos para Validar la Solución

1. **Limpiar caché de Laravel:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

2. **Prueba en el navegador:**
   - Abre Firefox/Chrome
   - Ve a http://egresados.test/perfil-datos
   - Intenta guardar cambios en Datos Personales
   - Verifica que el cambio se guarda

3. **Revisar logs si hay error:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

## Si Sigue Fallando

### Opción A: Usar el script de sincronización
```bash
wsl -d Ubuntu -e bash -lc "cd /home/jorte/proyectos/Egresados && php sync_to_remote.php"
```

### Opción B: Verificar directamente la BD
El error puede ser que la BD remota está rechazando conexiones. Intenta:
```bash
php artisan tinker
>>> DB::connection()->getPdo()
>>> exit
```

Si eso falla, significa que el problema es con el proveedor de hosting.

## Para Migrar a Servidor con Filezilla

Una vez que funcione localmente:

1. **Preparar archivos para subir:**
   ```bash
   # Asegúrate de que los cambios estén guardados
   git status
   git add app/Http/Controllers/PerfilController.php
   git add app/Http/Middleware/CheckPreegresoCompleted.php
   git add config/database.php
   git add sync_to_remote.php
   git commit -m "Fix: Resolver error 500 en actualización de perfil"
   ```

2. **Con Filezilla:**
   - Sube `/app/Http/Controllers/PerfilController.php`
   - Sube `/app/Http/Middleware/CheckPreegresoCompleted.php`
   - Sube `/config/database.php`
   - Sube `/sync_to_remote.php`

3. **En el servidor:**
   ```bash
   cd /ruta/del/proyecto
   php artisan cache:clear
   php artisan config:clear
   php sync_to_remote.php
   ```

## Cambios de Código

### updateDatosPersonales() - ANTES
```php
$egresado->fill($validated);
$egresado->save(); // ❌ Problema con prepared statements
```

### updateDatosPersonales() - DESPUÉS
```php
Egresado::updateOrCreate(
    ['id' => $validated['id']],
    $validated
); // ✅ Más robusto con BD remota
```

## Monitoreo

Los logs ahora registran:
- Inicio de cada operación
- Datos guardados correctamente
- Errores específicos con mensajes descriptivos

Revisar regularmente:
```bash
tail -f storage/logs/laravel.log | grep -i error
```
