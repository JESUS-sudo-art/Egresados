# Plan de Migración - Base de Datos Antigua a Nueva

## Análisis de Compatibilidad

### TABLAS QUE YA EXISTEN (Compatibles)

| Tabla Antigua | Tabla Actual | Estado | Observaciones |
|---------------|--------------|--------|---------------|
| `egresados` | `egresado` | ✅ Compatible | Ajustar campos adicionales |
| `escuelas` | `unidad` | ✅ Compatible | Mapeo directo |
| `carreras` | `carrera` | ✅ Compatible | Mapeo directo |
| `generaciones` | `generacion` | ✅ Compatible | Mapeo directo |
| `encuestas` | `encuesta` | ✅ Compatible | Ajustar campos |
| `preguntas` | `pregunta` | ✅ Compatible | Mapeo directo |
| `dimensiones` | `dimension` | ✅ Compatible | Mapeo directo |
| `opciones` | `opcion` | ✅ Compatible | Mapeo directo |
| `ciclos` | `ciclo` | ✅ Compatible | Ya existe |
| `usuarios` | `users` | ✅ Compatible | Sistema de auth Laravel |
| `bitacoras` | `bitacora` | ✅ Compatible | Ya existe |
| `laborales` | `laboral` | ✅ Compatible | Ya existe |

### TABLAS NUEVAS A CREAR

| Tabla Antigua | Necesita Migración | Propósito |
|---------------|-------------------|-----------|
| `academicos` | ✅ SÍ | Relación egresado-escuela-carrera-generación |
| `asignadas` | ✅ SÍ | Asignación de encuestas |
| `bitegresados` | ✅ SÍ | Bitácora de accesos de egresados |
| `bitencuestas` | ✅ SÍ | Bitácora de encuestas contestadas |
| `intrespuestas` | ✅ SÍ | Respuestas numéricas/opción múltiple |
| `txtrespuestas` | ✅ SÍ | Respuestas de texto abierto |
| `columnas` | ✅ SÍ | Configuración de matrices en encuestas |
| `subdimensiones` | ✅ SÍ | Subdivisión de dimensiones |
| `escucarreras` | ⚠️ MAPEAR | Ya existe como `unidad_carrera` |
| `nivestudios` | ⚠️ MAPEAR | Ya existe como `nivel_estudio` |
| `tipos` | ⚠️ MAPEAR | Ya existe como `tipo_pregunta` |
| `dirigidas` | ✅ SÍ | Catálogo de tipos de público objetivo |
| `niveles` | ⚠️ ROLES | Mapear a sistema de roles de Laravel |
| `empresas` | ✅ SÍ | Catálogo de empresas (bolsa de trabajo) |
| `servicios` | ⚠️ EVALUAR | Servicio social (si se necesita) |
| `accesos` | ❌ NO | Redundante con bitacoras |
| `menus` | ❌ NO | No se usa |

### CATÁLOGOS IMPORTANTES A MIGRAR

1. **cat_genero** - Ya existe ✅
2. **cat_estado_civil** - Ya existe ✅
3. **cat_estatus** - Ya existe ✅
4. **nivel_estudio** - Ya existe, mapear desde `nivestudios` ✅
5. **tipo_pregunta** - Ya existe, mapear desde `tipos` ✅
6. **dirigidas** - CREAR nueva ⚠️

## Ajustes Necesarios

### 1. Tabla `egresado` - Campos adicionales

```php
// Campos de la BD antigua que faltan en la nueva:
- matricula (INT) -> Ya existe (STRING 50)
- extension (VARCHAR 5) -> AGREGAR
- activo (CHAR 1) -> AGREGAR (mapear a estatus)
- fechaingreso (DATETIME) -> AGREGAR
- ultimoingreso (DATETIME) -> AGREGAR
- repite (VARCHAR 40) -> Hash de verificación (opcional)
```

### 2. Tabla `encuesta` - Campos adicionales

```php
// Campos de la BD antigua que faltan:
- nomcto (VARCHAR 30) -> nombre_corto
- dirigidas_id (INT) -> tipo_dirigida_id
```

### 3. Tabla `pregunta` - Campos adicionales

```php
// Campos de la BD antigua que faltan:
- subdimensiones_id (INT) -> AGREGAR
- padre (INT) -> pregunta_padre_id (preguntas anidadas)
```

### 4. Tabla `unidad` (escuelas)

```php
// Campos de la BD antigua que faltan:
- clave (VARCHAR 40) -> Ya existe
- repite (VARCHAR 40) -> Hash (opcional, no necesario)
```

## Estrategia de Migración

### Fase 1: Crear Migraciones (Nuevas Tablas)
1. ✅ `academico` - Relación académica de egresados
2. ✅ `encuesta_asignada` vs `asignadas` - Revisar si ya existe
3. ✅ `bitacora_egresado` - Sesiones de egresados
4. ✅ `bitacora_encuesta` - Encuestas contestadas
5. ✅ `respuesta_int` - Respuestas numéricas
6. ✅ `respuesta_txt` - Respuestas texto
7. ✅ `subdimension` - Subdimensiones
8. ✅ `columna_encuesta` - Configuración de columnas
9. ✅ `cat_dirigida` - Catálogo de público objetivo
10. ✅ `empresa` - Catálogo de empresas

### Fase 2: Modificar Migraciones Existentes
1. Agregar campos faltantes a `egresado`
2. Agregar campos faltantes a `encuesta`
3. Agregar campos faltantes a `pregunta`
4. Agregar relación `unidad_carrera` (si no existe)

### Fase 3: Script de Migración de Datos
1. Extraer datos del dump SQL
2. Transformar datos al nuevo formato
3. Insertar en orden de dependencias

### Fase 4: Seeders para Catálogos
1. Generaciones (1990-2023)
2. Ciclos escolares (2015-2024)
3. Unidades académicas (27 escuelas/facultades)
4. Carreras (50 programas)
5. Tipos de dirigida
6. Niveles de estudio

## Orden de Ejecución

```bash
# 1. Crear nuevas migraciones
php artisan migrate

# 2. Ejecutar seeders de catálogos
php artisan db:seed --class=CatalogosAntiguosSeeder

# 3. Ejecutar script de migración de datos
php artisan migrate:datos-antiguos

# 4. Validar integridad
php artisan migrate:validar-datos
```

## Datos a Migrar (Estimado)

- **Egresados**: ~8,417 registros
- **Respuestas Int**: ~137,117 registros
- **Respuestas Txt**: ~92,616 registros
- **Preguntas**: ~572 registros
- **Encuestas**: ~28 registros
- **Usuarios**: ~46 administradores
- **Carreras**: ~50 programas
- **Unidades**: ~27 escuelas/facultades
- **Dimensiones**: ~80 dimensiones
- **Opciones**: ~1,586 opciones

## Próximos Pasos

1. ✅ Crear migraciones para tablas nuevas
2. ✅ Crear script de extracción de datos del dump SQL
3. ✅ Crear seeders para catálogos
4. ✅ Crear comando Artisan para migración de datos
5. ✅ Ejecutar y validar migración
