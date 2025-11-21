# Sistema Automático de Cambio de Roles Estudiantes → Egresados

Este sistema cambia automáticamente el rol de usuarios de **Estudiantes** a **Egresados** cuando se validan en SICE.

## 🎯 Componentes Implementados

### 1. **Observer de Egresado** (`app/Observers/EgresadoObserver.php`)
Detecta automáticamente cuando un registro de egresado se actualiza o crea con `validado_sice = true`.

**¿Cuándo se ejecuta?**
- ✅ Cuando se actualiza un egresado y `validado_sice` cambia a `true`
- ✅ Cuando se crea un egresado con `validado_sice = true`

**Qué hace:**
- Busca el usuario asociado por email
- Verifica si tiene rol "Estudiantes"
- Cambia el rol a "Egresados" con `syncRoles(['Egresados'])`
- Registra la acción en logs

### 2. **Comando Artisan** (`app/Console/Commands/ActualizarRolesEgresados.php`)
Comando manual/programado que busca y actualiza estudiantes que ya están validados en SICE.

**Uso manual:**
```bash
# Ver quiénes serían actualizados (sin hacer cambios)
php artisan egresados:actualizar-roles --dry-run

# Actualizar con confirmación
php artisan egresados:actualizar-roles

# Actualizar sin confirmación (forzado)
php artisan egresados:actualizar-roles --force
```

**Características:**
- 📊 Muestra tabla con usuarios a actualizar
- 🧪 Modo dry-run para probar sin cambios
- ✅ Confirmación antes de actualizar
- 📝 Contador de actualizaciones y errores

### 3. **Scheduler** (programado en `routes/console.php`)
El comando se ejecuta automáticamente todos los días a las 2:00 AM.

```php
Schedule::command('egresados:actualizar-roles --force')
    ->dailyAt('02:00')
    ->timezone('America/Mexico_City')
    ->withoutOverlapping()
    ->runInBackground();
```

**Para que funcione en producción**, asegúrate de tener el cron configurado:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🔄 Flujo de Cambio de Rol

### **Escenario 1: Actualización en Tiempo Real**
```
1. Admin actualiza egresado en sistema
2. Marca validado_sice = 1
3. EgresadoObserver detecta el cambio
4. Busca usuario por email
5. Cambia rol de Estudiantes → Egresados
6. Usuario ve dashboard de egresado al refrescar
```

### **Escenario 2: Actualización Programada**
```
1. Egresado se valida en SICE (externo)
2. Validación se sincroniza a tu BD
3. Comando ejecuta diariamente a las 2 AM
4. Encuentra estudiantes validados
5. Cambia roles automáticamente
6. Genera reporte en logs
```

## 🔧 Configuración Adicional

### **Relación Usuario-Egresado**
Se agregó en `app/Models/Egresado.php`:
```php
public function user()
{
    return $this->hasOne(User::class, 'email', 'email');
}
```

### **Registro del Observer**
En `app/Providers/AppServiceProvider.php`:
```php
use App\Models\Egresado;
use App\Observers\EgresadoObserver;

public function boot(): void
{
    Egresado::observe(EgresadoObserver::class);
}
```

## 📋 Logs

Todos los cambios de rol se registran en `storage/logs/laravel.log`:
```
[timestamp] INFO: Usuario estudiante@example.com cambió de rol Estudiantes a Egresados (validado en SICE)
```

## ⚠️ Consideraciones

1. **Email como vínculo**: El sistema vincula usuarios y egresados por email. Asegúrate de que coincidan.

2. **Validación SICE**: El campo `validado_sice` debe actualizarse cuando SICE confirme la graduación.

3. **Estudiantes sin usuario**: Si un egresado no tiene usuario asociado, se registra una advertencia en logs.

4. **Reversión**: Si necesitas revertir, usa:
   ```php
   $user->syncRoles(['Estudiantes']);
   ```

## 🧪 Pruebas

### **Probar el Observer:**
```php
$egresado = Egresado::find(1);
$egresado->validado_sice = true;
$egresado->save(); // Observer se ejecuta automáticamente
```

### **Probar el Comando:**
```bash
# Modo prueba
php artisan egresados:actualizar-roles --dry-run

# Ejecutar realmente
php artisan egresados:actualizar-roles
```

## 📊 Monitoreo

Revisa regularmente:
- Logs en `storage/logs/laravel.log`
- Usuarios con roles incorrectos
- Egresados validados sin usuario asociado

```bash
# Ver logs recientes
tail -f storage/logs/laravel.log | grep "cambió de rol"

# Listar comando en scheduler
php artisan schedule:list
```

---

✅ **Sistema completamente funcional y listo para producción**
