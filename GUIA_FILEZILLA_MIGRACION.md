# Guía: Migrar Cambios a Servidor con Filezilla

## Prerequisitos
- ✅ Cambios testeados localmente
- ✅ Filezilla instalado
- ✅ Credenciales FTP del servidor
- ✅ Acceso SSH al servidor (opcional pero recomendado)

---

## Paso 1: Validar Cambios Localmente

Antes de subir a producción, asegúrate que todo funciona aquí:

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Probar conexión a BD
php test_bd_connection.php
# Deberías ver: ✓ Conexión a BD exitosa

# Ir a http://egresados.test/perfil-datos
# Cambiar algún dato y guardar
# Deberías ver cambios reflejados sin error 500
```

---

## Paso 2: Verificar Archivos Modificados

```bash
# Ver qué archivos cambiaron
git status
```

**Deberías ver estos archivos:**
- `app/Http/Controllers/PerfilController.php` ✏️
- `app/Http/Middleware/CheckPreegresoCompleted.php` ✏️
- `config/database.php` ✏️

---

## Paso 3: Configurar Filezilla

### 3.1 Abrir Filezilla y crear nueva conexión
1. Abre Filezilla
2. Menú: **File → Site Manager** (o Ctrl+Shift+S)
3. Botón: **New Site**
4. Ingresa datos del servidor:
   - **Protocol:** FTP o SFTP (según tu servidor)
   - **Host:** Tu IP o dominio
   - **Port:** 21 (FTP) o 22 (SFTP)
   - **User:** Tu usuario FTP
   - **Password:** Tu contraseña

Ejemplo:
```
Protocol: SFTP - SSH File Transfer Protocol
Host: 69.6.201.239  (o tu IP del servidor)
Port: 22
User: tu_usuario_ftp
Password: tu_contraseña
```

### 3.2 Conectar
- Click en **Connect**
- Deberías ver carpetas del servidor a la derecha

---

## Paso 4: Navegar a la Carpeta del Proyecto

En el panel derecho (Remote site):
1. Busca la carpeta donde está tu proyecto
2. Ejemplo: `/home/miusuario/public_html/egresados` o `/var/www/html`

---

## Paso 5: Subir Archivos Modificados

### 5.1 Crear estructura de carpetas en el servidor (si no existe)
Navega en Filezilla:
```
/
├── app/
│   └── Http/
│       ├── Controllers/
│       └── Middleware/
├── config/
└── (otros archivos)
```

### 5.2 Subir los 3 archivos modificados

**Archivo 1:** `app/Http/Controllers/PerfilController.php`
1. En panel izquierdo (Local): Navega a `app/Http/Controllers/`
2. Busca `PerfilController.php`
3. Click derecho → **Upload**
4. Espera a que termine (verde ✓)

**Archivo 2:** `app/Http/Middleware/CheckPreegresoCompleted.php`
1. En panel izquierdo: Navega a `app/Http/Middleware/`
2. Busca `CheckPreegresoCompleted.php`
3. Click derecho → **Upload**
4. Espera a que termine

**Archivo 3:** `config/database.php`
1. En panel izquierdo: Navega a `config/`
2. Busca `database.php`
3. Click derecho → **Upload**
4. Espera a que termine

### 5.3 Subir scripts de utilidad (opcional pero recomendado)

Si quieres poder correr tests después:
- `sync_to_remote.php`
- `test_bd_connection.php`

Sube estos a la raíz del proyecto.

---

## Paso 6: Ejecutar Comandos en el Servidor

### Opción A: Si tienes acceso SSH (RECOMENDADO)

Abre una terminal SSH:
```bash
ssh usuario@69.6.201.239
# Ingresa contraseña

# Navega al proyecto
cd /ruta/del/proyecto

# Paso 1: Limpiar caché
php artisan cache:clear
php artisan config:clear

# Paso 2: (Opcional) Sincronizar cambios
php sync_to_remote.php

# Paso 3: (Opcional) Probar conexión
php test_bd_connection.php

# Paso 4: Reiniciar servidor web
systemctl restart apache2
# O si usa nginx:
systemctl restart nginx
```

### Opción B: Si NO tienes SSH

Usa el panel de control del hosting (cPanel, Plesk, etc.):
1. Abre File Manager
2. Navega a `/home/usuario/public_html/egresados` (o tu carpeta)
3. Abre una terminal (Terminal, SSH Emulator, etc.)
4. Ejecuta los mismos comandos del Opción A

---

## Paso 7: Verificar que los Cambios Estén Listos

### En el Navegador:
1. Ve a `https://tu-dominio/perfil-datos` (o tu URL)
2. Intenta guardar cambios en Datos Personales
3. Si funciona sin error 500, ¡está listo!

### En el Servidor (SSH):
```bash
# Revisar últimos errores
tail -20 storage/logs/laravel.log

# Debería haber líneas como:
# "Datos guardados correctamente"
# (sin errores de "Prepared statement")
```

---

## Paso 8: Validar que Pre-Egreso Recibe los Datos

1. Ve a la encuesta de pre-egreso
2. Verifica que los datos personales que guardaste están reflejados
3. Si ves los cambios, todo está funcionando ✓

---

## Troubleshooting

### Problema: Filezilla dice "Permission denied"
**Solución:**
- Verifica que tienes permisos FTP correctos
- Intenta con diferentes carpetas padre
- Contacta a tu proveedor de hosting

### Problema: Aún veo error 500 después de subir cambios
**Solución:**
```bash
# Asegúrate de haber limpiado caché en el servidor
ssh usuario@servidor
cd /ruta/del/proyecto
php artisan cache:clear
php artisan config:clear
systemctl restart apache2
```

### Problema: No veo los archivos nuevos en el servidor
**Solución:**
1. En Filezilla, presiona **F5** o Ctrl+R para refrescar
2. Asegúrate de estar en la carpeta correcta
3. Verifica que no hay errores de subida (columna derecha)

### Problema: Error "Could not connect" en Filezilla
**Solución:**
- Verifica IP/dominio del servidor
- Verifica puerto (21 FTP, 22 SFTP)
- Verifica credenciales
- Asegúrate que FTP está habilitado en el servidor

---

## Checklist Final

Antes de considerar listo:

- [ ] Cambios locales testeados y funcionan
- [ ] 3 archivos subidos al servidor
- [ ] Caché limpiado en servidor
- [ ] Servidor web reiniciado
- [ ] Puedo guardar cambios sin error 500
- [ ] Los cambios aparecen en pre-egreso
- [ ] Logs no muestran errores de prepared statement

---

## Después de la Migración

### Monitoreo Recomendado
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Buscar errores específicos
grep "PDOException" storage/logs/laravel.log
grep "Prepared statement" storage/logs/laravel.log

# Si hay errores, ejecutar sincronización
php sync_to_remote.php
```

### Respaldo
```bash
# Antes de cambios grandes, hacer backup
mysqldump -h 69.6.201.239 -u usuario -p base_datos > backup.sql
```

---

## Soporte

Si algo no funciona:
1. Revisa `storage/logs/laravel.log`
2. Ejecuta `php test_bd_connection.php`
3. Verifica permisos de archivos:
   ```bash
   chmod -R 755 app/
   chmod -R 755 config/
   chmod -R 777 storage/
   ```

**¡Los cambios están diseñados para ser compatibles con cualquier servidor!**
