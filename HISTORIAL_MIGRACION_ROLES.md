# Historial de Migración y Automatización de Roles

## 1. Encuesta de Pre-Egreso Solo Lectura para Egresados
- Modificado el controlador `CedulaPreegresoController.php` para que los egresados solo puedan ver sus respuestas, no editarlas.
- En el componente Vue `EncuestaPreegreso.vue`, todos los campos se deshabilitan si el usuario tiene el rol "Egresados".
- Se muestra un banner azul indicando que la encuesta es solo de consulta.

## 2. Automatización de Cambio de Roles
- Se implementó el patrón Observer en `app/Observers/EgresadoObserver.php` para cambiar el rol de "Estudiantes" a "Egresados" automáticamente cuando el campo `validado_sice` cambia a `true`.
- El observer se registró en `AppServiceProvider.php`.
- Se creó el comando `ActualizarRolesEgresados` para actualizar roles en lote y se programó su ejecución diaria a las 2:00 AM en `routes/console.php`.
- Se crearon comandos de prueba: `TestObserver` y `TestComandoRoles` para verificar el funcionamiento.

## 3. Migración de Datos desde Base Antigua (bdwvexa)
- Se analizó el esquema SQL de la base antigua (MySQL/MariaDB).
- Se creó el comando `MigrarDatosAntiguos` con 12 métodos para migrar todos los datos relevantes (catálogos, generaciones, ciclos, unidades, carreras, egresados, encuestas, dimensiones, preguntas, opciones, respuestas, laborales).
- El comando soporta modo `--dry-run` para simular la migración sin afectar datos.
- Se agregó la conexión `bdwvexa` en `config/database.php` usando variables de entorno `DB_OLD_*`.
- Se documentó el proceso en `MIGRACION_DATOS.md`.

## 4. Respaldo de Base de Datos
- Se creó el comando `BackupDatabase` que soporta tanto SQLite como MySQL.
- El comando detecta el tipo de base y realiza copia directa (SQLite) o `mysqldump` (MySQL).
- Se generan instrucciones de restauración según el tipo de base.
- Se probó el respaldo y se generó el archivo `backup_database.sqlite_YYYY-MM-DD_HHMMSS.sqlite`.

## 5. Credenciales para Migración
- Para migrar desde la base antigua, se requieren las siguientes variables en `.env`:
  ```env
  DB_OLD_CONNECTION=mysql
  DB_OLD_HOST=127.0.0.1
  DB_OLD_PORT=3306
  DB_OLD_DATABASE=bdwvexa
  DB_OLD_USERNAME=root
  DB_OLD_PASSWORD=
  ```
- Si la base está en un servidor externo, pedir a la empresa:
  - Host/IP
  - Puerto
  - Usuario
  - Contraseña
  - Permiso de acceso remoto o dump completo para importar localmente

## 6. Próximos Pasos
- Esperar credenciales o dump completo de la empresa.
- Configurar `.env` con los datos recibidos.
- Probar migración con `php artisan migrar:datos-antiguos --dry-run`.
- Ejecutar migración real y verificar datos.

---

**Este archivo sirve como historial y referencia para continuar el trabajo de migración y automatización de roles.**
