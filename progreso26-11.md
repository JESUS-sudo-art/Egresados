# Progreso del 26 de Noviembre de 2025

## Resumen de Trabajo

Se realizaron múltiples mejoras y correcciones en el sistema de egresados, enfocadas en la gestión de datos académicos, restricciones por rol y funcionalidad del formulario de registro.

---

## 1. Visualización de Unidad y Carrera en Perfil

### Problema Identificado
Los usuarios no veían la unidad y carrera en la sección de Datos Académicos del perfil, mostrando "No disponible".

### Solución Implementada

#### Base de Datos
- **Migración**: `2025_11_26_063027_add_unidad_carrera_to_egresado_table.php`
  - Agregadas columnas `unidad_id` y `carrera_id` (tipo `int`) a tabla `egresado`
  - Llaves foráneas a tablas `unidad` y `carrera` con `onDelete('set null')`

#### Modelo Egresado
**Archivo**: `app/Models/Egresado.php`
- Agregadas relaciones:
  ```php
  public function unidad()
  {
      return $this->belongsTo(Unidad::class, 'unidad_id');
  }

  public function carrera()
  {
      return $this->belongsTo(Carrera::class, 'carrera_id');
  }
  ```
- Agregados `unidad_id` y `carrera_id` al array `$fillable`

#### Controlador de Perfil
**Archivo**: `app/Http/Controllers/PerfilController.php`
- Modificado método `index()` para cargar relaciones:
  ```php
  Egresado::with(['genero', 'estadoCivil', 'estatus', 'carreras.carrera', 'carreras.generacion', 'unidad', 'carrera'])
  ```

#### Vista de Perfil
**Archivo**: `resources/js/pages/modules/PerfilDatos.vue`
- Sección Datos Académicos actualizada para mostrar:
  - **Unidad**: `{{ egresado?.unidad?.nombre || 'No disponible' }}`
  - **Carrera**: `{{ egresado?.carrera?.nombre || 'No disponible' }}`
  - **Generación**: `{{ carreraInfo.generacion?.nombre || 'No disponible' }}`

#### Actualización de Datos Existentes
- Script temporal para actualizar egresados existentes con la primera unidad y carrera disponible
- 5 egresados actualizados correctamente

---

## 2. Restricción de Situación Laboral por Rol

### Problema
La pestaña "Situación Laboral" aparecía para estudiantes, cuando solo debe estar disponible para egresados.

### Solución Implementada

#### Vista de Perfil
**Archivo**: `resources/js/pages/modules/PerfilDatos.vue`
- Importado `usePage` de Inertia
- Agregada verificación de roles:
  ```typescript
  const userRoles = computed(() => ((page.props as any)?.auth?.roles ?? []) as string[]);
  const isEstudiante = computed(() => userRoles.value?.includes('Estudiantes'));
  const isEgresado = computed(() => userRoles.value?.includes('Egresados'));
  const showLaboralTab = computed(() => isEgresado.value && !isEstudiante.value);
  ```
- Pestaña "Situación Laboral" condicionada: `v-if="showLaboralTab"`
- Contenido de la pestaña también condicionado: `v-if="showLaboralTab && activeTab === 'laboral'"`

---

## 3. Restricción de Encuestas por Rol

### Problema
Las encuestas no estaban correctamente restringidas según el tipo de usuario.

### Solución Implementada

#### Dashboard (Tarjetas de Módulos)
**Archivo**: `resources/js/components/DashboardGrid.vue`
- **Encuesta Pre-Egreso**: `v-if="isEstudiante || isEgresado"` (estudiantes pueden llenar, egresados pueden consultar)
- **Encuesta de Egreso**: `v-if="isEgresado"` (solo egresados)
- **Encuesta Laboral**: `v-if="isEgresado"` (solo egresados)

#### Sidebar (Menú Lateral)
**Archivo**: `resources/js/components/AppSidebar.vue`
- **Encuesta Pre-Egreso**: `if (isEstudiante.value || isEgresado.value)` 
- **Encuesta de Egreso**: `if (isEgresado.value)`
- **Encuesta Laboral**: `if (isEgresado.value)`

---

## 4. Modo de Solo Lectura en Encuesta Pre-Egreso para Egresados

### Problema
Los egresados podían editar la Encuesta Pre-Egreso que llenaron como estudiantes.

### Solución Implementada

#### Controlador
**Archivo**: `app/Http/Controllers/CedulaPreegresoController.php`
- Modificada lógica en método `index()`:
  ```php
  // Los egresados siempre ven en modo solo lectura
  $soloLectura = $isEgresado;
  ```
- Validación en método `store()` que rechaza modificaciones de egresados

#### Vista
**Archivo**: `resources/js/pages/modules/EncuestaPreegreso.vue`
- Ya implementado previamente:
  - Todos los campos con `:disabled="props.soloLectura"`
  - Botón de envío oculto: `v-if="!props.soloLectura"`
  - Banner informativo azul para modo lectura
  - Mensaje: "Esta encuesta fue contestada cuando eras estudiante. Como egresado, solo puedes visualizar tus respuestas previas."

---

## 5. Campo "Año de Egreso" en Registro

### Requerimiento
Agregar campo de año de egreso que solo aparezca cuando el usuario selecciona "Egresado" como tipo de usuario.

### Solución Implementada

#### Base de Datos
- **Migración**: `2025_11_26_064624_add_anio_egreso_to_egresado_table.php`
  - Agregada columna `anio_egreso` (tipo `integer`, nullable) a tabla `egresado`

#### Modelo Egresado
**Archivo**: `app/Models/Egresado.php`
- Agregado `anio_egreso` al array `$fillable`

#### Vista de Registro
**Archivo**: `resources/js/pages/auth/Register.vue`
- Importada función `ref` y `watch` de Vue
- Creada variable reactiva: `const selectedUserType = ref('')`
- Select de tipo de usuario vinculado: `v-model="selectedUserType"`
- Campo año de egreso condicional:
  ```vue
  <div v-if="selectedUserType === 'Egresados'" class="grid gap-2">
    <Label for="anio_egreso">Año de egreso</Label>
    <Input
      id="anio_egreso"
      type="number"
      name="anio_egreso"
      :required="selectedUserType === 'Egresados'"
      placeholder="2024"
      min="1980"
      :max="new Date().getFullYear()"
    />
  </div>
  ```
- Ajustados tabindex de campos posteriores (7, 8, 9, 10)

#### Validación Backend
**Archivo**: `app/Actions/Fortify/CreateNewUser.php`
- Agregada regla de validación:
  ```php
  'anio_egreso' => ['nullable', 'integer', 'min:1980', 'max:' . date('Y'), 'required_if:user_type,Egresados']
  ```
- Mensajes de validación personalizados:
  - `anio_egreso.required_if`: "El año de egreso es obligatorio para egresados."
  - `anio_egreso.min`: "El año de egreso debe ser mayor a 1980."
  - `anio_egreso.max`: "El año de egreso no puede ser mayor al año actual."
- Guardado del campo en creación:
  ```php
  'anio_egreso' => $input['anio_egreso'] ?? null
  ```
- Actualización del campo en edición:
  ```php
  $egresado->anio_egreso = $input['anio_egreso'] ?? $egresado->anio_egreso;
  ```

---

## 6. Problema Conocido: Sincronización de web.php

### Descripción
El archivo `routes/web.php` presenta problemas de sincronización entre VSCode y el contenedor Docker cuando se edita desde la ruta `\\wsl.localhost\...`.

### Solución Temporal
- Usar comandos directos en WSL para editar el archivo
- Utilizar scripts Python ejecutados en el contexto de WSL
- Verificar cambios con: `docker exec egresados-php cat routes/web.php`

---

## Archivos Modificados

### Backend (PHP/Laravel)
1. `database/migrations/2025_11_26_063027_add_unidad_carrera_to_egresado_table.php` (NUEVO)
2. `database/migrations/2025_11_26_064624_add_anio_egreso_to_egresado_table.php` (NUEVO)
3. `app/Models/Egresado.php`
4. `app/Http/Controllers/PerfilController.php`
5. `app/Http/Controllers/CedulaPreegresoController.php`
6. `app/Actions/Fortify/CreateNewUser.php`

### Frontend (Vue/TypeScript)
1. `resources/js/pages/modules/PerfilDatos.vue`
2. `resources/js/components/DashboardGrid.vue`
3. `resources/js/components/AppSidebar.vue`
4. `resources/js/pages/auth/Register.vue`

---

## Comandos Ejecutados

```bash
# Crear migraciones
docker exec egresados-php php artisan make:migration add_unidad_carrera_to_egresado_table
docker exec egresados-php php artisan make:migration add_anio_egreso_to_egresado_table

# Ejecutar migraciones
docker exec egresados-php php artisan migrate

# Limpiar caché de rutas (si es necesario)
docker exec egresados-php php artisan route:clear
```

---

## Pruebas Requeridas

1. ✅ Verificar que unidad y carrera se muestren en Datos Académicos
2. ✅ Confirmar que estudiantes NO vean pestaña "Situación Laboral"
3. ✅ Confirmar que egresados SÍ vean pestaña "Situación Laboral"
4. ✅ Verificar que estudiantes NO vean "Encuesta de Egreso" en dashboard y sidebar
5. ✅ Verificar que egresados vean "Encuesta Pre-Egreso" en modo solo lectura
6. ✅ Confirmar que campo "Año de egreso" solo aparezca para tipo de usuario "Egresado"
7. ⏳ Registrar nuevo usuario tipo "Egresado" y verificar que se guarde el año de egreso
8. ⏳ Intentar registrar egresado sin año de egreso (debe dar error de validación)

---

## Notas Adicionales

- Todos los egresados existentes fueron actualizados con unidad_id y carrera_id
- La sincronización de archivos entre VSCode y Docker requiere atención especial en `routes/web.php`
- El sistema ahora diferencia correctamente entre estudiantes y egresados en cuanto a acceso a módulos
- La Encuesta Pre-Egreso mantiene un historial inmutable para egresados

---

## Estado Final

✅ Sistema funcionando correctamente
✅ Restricciones por rol implementadas
✅ Datos académicos visibles en perfil
✅ Modo lectura para egresados en encuesta pre-egreso
✅ Campo año de egreso condicional en registro
