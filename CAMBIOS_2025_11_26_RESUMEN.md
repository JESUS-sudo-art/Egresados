# Resumen de cambios — 2025-11-26

## Guardado de archivos en WSL
- Añadido `/.vscode/settings.json` con `files.saveConflictResolution: "overwriteFileOnDisk"` para evitar el error "The content of the file is newer" al guardar sobre `\\wsl.localhost`.
- Recomendación: abrir el proyecto vía Remote WSL (`code /home/jorte/proyectos/Egresados`).

## Navegación y rutas
- Eliminada la ruta `escritorio` de `routes/web.php` y ajustado el flujo para dirigir egresados/estudiantes al `dashboard`.
- Actualizado `FortifyServiceProvider` para redirigir egresados y estudiantes a `route('dashboard')` (se eliminó la referencia a `escritorio`).

## Módulos Inertia
- `PerfilEgresado.vue`: añadido botón "Ver encuesta" junto a "Completada" que abre respuestas del egresado (admin) usando la nueva ruta.
- `Escritorio.vue`: se añadió sección de encuestas contestadas, pero se dejó fuera de rutas (el flujo principal usa el dashboard).

## Encuestas — creación y actualización
- `AdminUnidadController`:
  - Activado `use AuthorizesRequests` (el trait estaba comentado).
  - Simplificado `storeEncuesta` quitando `authorize()` (los permisos ya se manejan con middleware en rutas).
  - Corregido `updateEncuesta(Request $request, $id)` para obtener la encuesta por ID; eliminado `authorize()` aquí también.
  - `destroyEncuesta`: uso explícito de `\App\Models\Opcion` al eliminar dependencias.
- `EncuestaPolicy`:
  - `create(User $user)`: ahora valida `encuestas.crear` y permite a Admin General y Admin de Unidad.

## Ver respuestas
- `EncuestaController`:
  - `misRespuestas($encuestaId)`: ahora busca respuestas por el `egresado` asociado al usuario (antes usaba `user->id`).
  - Añadido `respuestasDeEgresado($encuestaId, $egresadoId)` para uso administrativo (ver respuestas de un egresado específico).
- `routes/web.php`:
  - Nueva ruta: `encuesta/{encuestaId}/egresado/{egresadoId}/respuestas` con `permission:egresados.ver_perfil`.

## UI de Dashboard
- `Dashboard.vue`: muestra encuestas asignadas con acciones:
  - "Contestar Encuesta" si no ha respondido.
  - "Ver Mis Respuestas" si ya respondió.
- `DashboardGrid.vue`: mantiene módulos de egresado y administrativos.

## Encuestas contestadas en perfiles (2025-11-27)

### Tracking de encuestas específicas en perfil de egresado
- **Problema**: El perfil del egresado solo mostraba encuestas dinámicas (tabla `respuesta`), no mostraba Cédula de Pre-Egreso ni Encuesta Laboral.
- **Solución**:
  - `EgresadoController@show`: Agregadas consultas para `CedulaPreegreso` y `EncuestaLaboral`, se añaden a la colección con IDs especiales (`'preegreso'`, `'laboral'`).
  - `PerfilEgresado.vue`: Actualizado para diferenciar tipos de encuesta:
    - Si `encuesta.id === 'preegreso'` → redirige a `/encuesta-preegreso?egresado_id={id}`
    - Si `encuesta.id === 'laboral'` → redirige a `/encuesta-laboral?egresado_id={id}`
    - Otras encuestas → ruta dinámica `/encuesta/{id}/egresado/{egresadoId}/respuestas`

### Modo solo lectura para administradores
- **Objetivo**: Cuando un admin visualiza encuestas desde el perfil de un egresado, debe verlas en modo lectura sin poder modificar.
- **Cambios en controladores**:
  - `CedulaPreegresoController@index(Request $request)`:
    - Detecta `egresado_id` en query params
    - Si es admin (`Administrador general`, `Administrador de unidad`, `Administrador academico`) y viene con `egresado_id`, carga ese egresado específico
    - Activa `soloLectura = true` cuando es admin viendo otro egresado
  - `EncuestaLaboralController@index(Request $request)`: Misma lógica
  - **Nota**: Se corrigieron los nombres de roles que estaban mal (`Admin General` → `Administrador general`, etc.)

### Catálogo de egresados - Mostrar todas las encuestas
- **Problema**: El catálogo solo mostraba encuestas dinámicas en la columna "Encuestas".
- **Solución**:
  - `EgresadoController@catalogo`: Agregadas verificaciones de `CedulaPreegreso` y `EncuestaLaboral`
  - Se agregan a la colección `encuestas_contestadas` con mismos IDs especiales
  - Ahora el listado muestra correctamente todas las encuestas contestadas (prueba1, prueba2, Cédula de Pre-Egreso, Encuesta Laboral, etc.)

### Corrección de PDF - Acuse de Seguimiento
- **Problema**: El PDF no mostraba matrícula, unidad, carrera ni año de egreso correctamente.
- **Análisis**: 
  - Algunos egresados tienen `carrera_id` directamente en tabla `egresado`
  - Otros tienen relación en tabla pivot `egresado_carrera`
  - El año de egreso está en `egresado.anio_egreso`, no en la fecha de la relación
- **Solución** (`AcusesSeguimientoController@descargarAcuse`):
  - Eager load tanto `carrera` (relación directa) como `carreras` (pivot)
  - Priorizar `carrera_id` si existe, luego buscar en pivot
  - Usar `egresado.anio_egreso` directamente para año de egreso
  - Los campos ahora se llenan correctamente:
    - Matrícula: `egresado.matricula` o 'N/A'
    - Escuela/Facultad: primera unidad asociada a la carrera
    - Licenciatura: nombre de la carrera
    - Año de egreso: `egresado.anio_egreso`

### Debugging
- Agregado `console.log` temporal en `EncuestaPreegreso.vue` para verificar props recibidas (puede eliminarse)
- Scripts de verificación creados:
  - `debug_admin_role.php` - Verificar roles de admin
  - `check_roles_available.php` - Listar todos los roles y usuarios
  - `check_luis_data.php` - Verificar datos específicos de usuario

## Pendiente / Notas
- Considerar reemplazar separadores de roles `role:...,...` por `role:...|...` si se usa Spatie por defecto (actualmente se usan comas de forma personalizada).
- Si hay policies adicionales, verificar su registro y permisos en `AppServiceProvider`.
- Eliminar console.log de debug en `EncuestaPreegreso.vue` cuando se confirme funcionamiento.
- Evaluar si todos los egresados deberían tener matrícula asignada.

## Comandos útiles
```powershell
# Abrir en Remote WSL (recomendado)
wsl -d Ubuntu
code /home/jorte/proyectos/Egresados

# Limpiar caches de Laravel (desde WSL)
wsl -d Ubuntu -e bash -lc 'cd /home/jorte/proyectos/Egresados; php artisan config:clear; php artisan route:clear; php artisan view:clear'
```
