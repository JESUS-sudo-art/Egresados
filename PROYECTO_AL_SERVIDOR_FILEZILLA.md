# Proyecto al servidor (FileZilla)

## Archivos subidos
- app/Http/Controllers/PerfilController.php
- app/Observers/EgresadoObserver.php
- app/Http/Middleware/CheckPreegresoCompleted.php
- config/database.php
- database/migrations/2026_01_22_052911_add_edad_to_cedula_preegreso_table.php
- resources/js/Pages/modules/PerfilDatos.vue (si se usa build en servidor)
- app/Http/Controllers/QrCodeController.php

## Cambios clave
- PerfilController guarda con SQL crudo y sincroniza pre-egreso (telefono_contacto, edad), crea la cédula si no existe y actualiza observaciones con "Edad: X".
- EgresadoObserver sincroniza teléfono y edad al detectar cambios en teléfono/fecha_nacimiento.
- Middleware permite rutas de perfil en flujo de pre-egreso.
- Config DB con timeout e init command para mayor estabilidad.
- Migración agrega columna edad a cedula_preegreso.
- Código QR ahora apunta a https://egresados.mesitest.com; se puede sobreescribir con QR_TARGET_URL o app.qr_url.

## Comandos ejecutados en servidor
- php artisan migrate --force
- php artisan cache:clear
- php artisan config:clear
- php artisan route:clear
- php artisan view:clear

## Cómo validar
1) En Perfil y Datos, guardar teléfono y fecha de nacimiento.
2) Abrir Encuesta Preegreso: teléfono debe aparecer y edad debe precargarse (guardada en columna edad y en observaciones como "Edad: X").

## Notas
- Si el servidor compila frontend, ejecutar npm ci && npm run build o subir public/build desde local.
- Si se crean nuevos egresados sin cédula, el guardado de perfil ahora genera la cédula automáticamente.
- QR usa dominio de producción; si cambia, ajustar QR_TARGET_URL en .env.

---

## Sesión 22 de Enero de 2026 (Noche)

### Cambios realizados

#### 1. Código QR actualizado
- **Archivo modificado:** `app/Http/Controllers/QrCodeController.php`
- **Cambio:** El QR ahora apunta a `https://egresados.mesitest.com` por defecto
- **Configuración:** Se puede cambiar con variable `QR_TARGET_URL` en .env
- **Método agregado:** `getQrUrl()` que lee config('app.qr_url') o env('QR_TARGET_URL')

#### 2. Configuración de entornos separados (.env)

**Problema identificado:**
- El .env del servidor con `MAIL_MAILER=smtp` causa errores SSL en Docker local
- Copiar el .env del servidor al local generaba error 500 al enviar invitaciones

**Solución implementada:**
- Creado `.env.local` para desarrollo local
- Mantenido `.env` para configuración de servidor
- Creado `.env.README.md` con guía de uso

**Diferencias clave entre archivos:**

| Variable | .env.local (Local) | .env (Servidor) |
|----------|-------------------|-----------------|
| APP_ENV | local | production |
| APP_DEBUG | true | false |
| APP_URL | http://egresados.test | https://egresados.mesitest.com |
| MAIL_MAILER | log | smtp |

**Uso en local:**
```bash
cp .env.local .env
php artisan config:clear
php artisan cache:clear
```

**Uso en servidor:**
- El archivo `.env` ya está listo para producción
- Solo subir cuando se agreguen nuevas variables

#### 3. Flujo de trabajo establecido

**Para cambios de código (controladores, vistas, etc.):**
- Archivos funcionan igual en local y servidor
- Subir solo los archivos PHP/JS/Vue modificados
- NO subir el .env (a menos que agregues nuevas variables)

**Para cambios en variables de entorno:**
- Agregar la variable en ambos archivos: `.env.local` y `.env`
- Mantener valores diferentes donde sea necesario (MAIL_MAILER, APP_URL, etc.)
- En local: `cp .env.local .env && php artisan config:clear`
- En servidor: Subir `.env` y ejecutar `php artisan config:clear`

#### 4. Corrección de errores de invitaciones

**Problema:**
- Error 500 al enviar/reenviar invitaciones en local
- Causado por copiar `.env` del servidor al local

**Causa raíz:**
- Docker local no puede verificar certificados SSL de Gmail
- Error: `SSL operation failed: certificate verify failed`
- Con `MAIL_MAILER=smtp`, Laravel intentaba conectarse a Gmail y fallaba

**Solución:**
- Local usa `MAIL_MAILER=log` (correos se guardan en storage/logs/laravel.log)
- Servidor usa `MAIL_MAILER=smtp` (envía correos realmente)

### Archivos creados/modificados hoy

**Nuevos archivos:**
- `.env.local` (configuración para desarrollo local)
- `.env.README.md` (documentación de uso de .env)

**Archivos modificados:**
- `app/Http/Controllers/QrCodeController.php` (QR apunta a dominio producción)
- `.env` (actualizado para servidor con smtp)

### Comandos útiles

**Cambiar a configuración local:**
```bash
cp .env.local .env
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan cache:clear
```

**Ver logs de correos en local:**
```bash
tail -f storage/logs/laravel.log
```

**Limpiar caché en servidor:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Resumen de aprendizajes

1. **NUNCA copiar .env del servidor al local directamente**
   - Usar `.env.local` como base para desarrollo
   - Mantener `.env` solo para servidor

2. **MAIL_MAILER debe ser diferente:**
   - Local: `log` (sin envío real, solo testing)
   - Servidor: `smtp` (envío real a Gmail)

3. **Variables que deben coincidir en ambos archivos:**
   - Credenciales de base de datos
   - Claves API externas
   - Configuración de sesiones/caché
   - Nuevas features o flags

4. **Variables que deben ser diferentes:**
   - APP_ENV (local vs production)
   - APP_DEBUG (true vs false)
   - APP_URL (dominio local vs producción)
   - MAIL_MAILER (log vs smtp)
