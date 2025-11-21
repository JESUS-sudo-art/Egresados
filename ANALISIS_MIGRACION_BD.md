# Análisis de Migración de Base de Datos Antigua (bdwvexa) a Nueva BD

## 📊 RESUMEN EJECUTIVO

**¿Se pueden migrar los datos?** ✅ **SÍ, CON AJUSTES**

La estructura antigua es compatible en un 80% con la nueva. Se requieren ajustes en mapeo de campos y normalización de datos.

---

## 🔄 MAPEO DE TABLAS

### ✅ TABLAS DIRECTAMENTE COMPATIBLES

| BD Antigua | BD Nueva | Compatibilidad | Ajustes Necesarios |
|------------|----------|----------------|-------------------|
| `ciclos` | `ciclo` | 95% | Mapeo simple de estatus |
| `generaciones` | `generacion` | 100% | Ninguno |
| `nivestudios` | Sin equivalente directo | - | Se usa campo `nivel` en `carrera` |
| `tipos` (preguntas) | `tipo_pregunta` | 95% | Mapeo simple |
| `dirigidas` | Sin equivalente | - | Se mapea a `tipo_cuestionario` |

### ⚠️ TABLAS CON AJUSTES MODERADOS

#### 1. **escuelas → unidad**

**BD Antigua:**
```sql
CREATE TABLE escuelas (
  id int(11),
  nombre varchar(80),
  nomcto varchar(20),
  domicilio varchar(250),
  web varchar(100),
  email varchar(150),
  clave varchar(40),
  estatus char(1),
  repite varchar(40)
)
```

**BD Nueva:**
```sql
CREATE TABLE unidad (
  id int,
  nombre varchar(150),
  clave varchar(50),
  tipo enum('Facultad','Instituto','Escuela','Centro'),
  estatus char(1)
)
```

**Ajustes:**
- ✅ `nombre` → `nombre`
- ✅ `nomcto` → `clave`
- ❌ `domicilio, web, email` → **No se migran** (no hay campos en nueva)
- ⚠️ Asignar `tipo` = 'Escuela' por defecto
- ✅ `estatus` → `estatus`

---

#### 2. **carreras → carrera**

**BD Antigua:**
```sql
CREATE TABLE carreras (
  id int(11),
  nombre varchar(80),
  nomcto varchar(20),
  nivestudios_id int(11),
  estatus char(1)
)
```

**BD Nueva:**
```sql
CREATE TABLE carrera (
  id int,
  nombre varchar(150),
  nivel enum('Licenciatura','Maestría','Doctorado','Especialidad'),
  tipo_programa enum('Escolarizado','No Escolarizado','Mixto'),
  estatus char(1)
)
```

**Ajustes:**
- ✅ `nombre` → `nombre`
- ⚠️ `nivestudios_id` → Mapear a `nivel` (requiere tabla auxiliar)
- ❌ `nomcto` → No se migra
- ⚠️ Asignar `tipo_programa` = 'Escolarizado' por defecto
- ✅ `estatus` → `estatus`

**Mapeo de nivestudios:**
```php
$mapeoNiveles = [
    1 => 'Licenciatura',
    2 => 'Maestría',
    3 => 'Doctorado',
    4 => 'Especialidad'
];
```

---

#### 3. **egresados → egresado**

**BD Antigua:**
```sql
CREATE TABLE egresados (
  id int(11),
  matricula int(11),
  clave varchar(40),      -- CONTRASEÑA
  nombre varchar(50),
  apellidos varchar(50),
  genero char(1),         -- 'H'/'M'
  fecnac date,
  lugarnac varchar(100),
  domicilio varchar(250),
  email varchar(150),
  edocivil char(1),       -- 'S'/'C'/'D'/'V'
  fechaingreso datetime,
  ultimoingreso datetime,
  estatus char(1),
  token varchar(100),
  escuelas_id int(11),
  carreras_id int(11),
  repite varchar(40),     -- CURP?
  generaciones_id int(11),
  activo char(1),
  extension varchar(5)
)
```

**BD Nueva:**
```sql
CREATE TABLE egresado (
  id int,
  matricula varchar(50),
  curp varchar(18),
  nombre varchar(150),
  apellidos varchar(200),
  genero_id int,              -- FK a cat_genero
  fecha_nacimiento date,
  lugar_nacimiento varchar,
  domicilio text,
  domicilio_actual text,
  email varchar(150),
  estado_civil_id int,        -- FK a cat_estado_civil
  tiene_hijos boolean,
  habla_lengua_indigena boolean,
  habla_segundo_idioma boolean,
  pertenece_grupo_etnico boolean,
  facebook_url varchar,
  tipo_estudiante char(1),
  validado_sice char(1),
  token varchar,
  estatus_id int              -- FK a cat_estatus
)
```

**Ajustes CRÍTICOS:**
- ✅ `matricula` → `matricula` (convertir a string)
- ⚠️ `repite` → `curp` (si contiene CURP válido)
- ✅ `nombre` → `nombre`
- ✅ `apellidos` → `apellidos`
- ⚠️ `genero` → Mapear a `genero_id`:
  ```php
  'H' => 1, // Hombre
  'M' => 2, // Mujer
  ```
- ✅ `fecnac` → `fecha_nacimiento`
- ✅ `lugarnac` → `lugar_nacimiento`
- ✅ `domicilio` → `domicilio`
- ✅ `email` → `email`
- ⚠️ `edocivil` → Mapear a `estado_civil_id`:
  ```php
  'S' => 1, // Soltero/a
  'C' => 2, // Casado/a
  'D' => 3, // Divorciado/a
  'V' => 4, // Viudo/a
  'U' => 5  // Unión libre
  ```
- ⚠️ `estatus` → Mapear a `estatus_id`
- ✅ `token` → `token`
- ❌ `clave` (contraseña) → **NO SE MIGRA** (usar token para reset)
- ❌ `fechaingreso, ultimoingreso` → No hay campo directo
- ⚠️ `escuelas_id, carreras_id, generaciones_id` → Migrar a `egresado_carrera`
- ❌ `activo, extension` → No se migran

**Nueva tabla de relación:**
```sql
CREATE TABLE egresado_carrera (
  egresado_id int,
  carrera_id int,
  generacion_id int,
  fecha_ingreso date,
  fecha_egreso date,
  tipo_egreso varchar(50)
)
```

---

#### 4. **encuestas → encuesta**

**BD Antigua:**
```sql
CREATE TABLE encuestas (
  id int(11) UNSIGNED,
  ciclos_id int(11) UNSIGNED,
  nombre varchar(50),
  nomcto varchar(30),
  dirigidas_id int(11),       -- Tipo de encuesta
  fecini date,
  fecfin date,
  estatus char(1),
  descripcion text,
  instrucciones text
)
```

**BD Nueva:**
```sql
CREATE TABLE encuesta (
  id int,
  unidad_id int,
  carrera_id int,
  ciclo_id int,
  nombre varchar,
  tipo_cuestionario varchar(100),
  fecha_inicio date,
  fecha_fin date,
  descripcion text,
  instrucciones text,
  estatus char(1)
)
```

**Ajustes:**
- ✅ `ciclos_id` → `ciclo_id`
- ✅ `nombre` → `nombre`
- ⚠️ `dirigidas_id` → Mapear a `tipo_cuestionario`:
  ```php
  1 => 'Pre-Egreso',
  2 => 'Egreso',
  3 => 'Seguimiento Laboral'
  ```
- ✅ `fecini` → `fecha_inicio`
- ✅ `fecfin` → `fecha_fin`
- ✅ `descripcion` → `descripcion`
- ✅ `instrucciones` → `instrucciones`
- ✅ `estatus` → `estatus`
- ❌ `nomcto` → No se migra
- ⚠️ `unidad_id, carrera_id` → Inferir desde `asignadas`

---

#### 5. **dimensiones → dimension**

**BD Antigua:**
```sql
CREATE TABLE dimensiones (
  id int(11) UNSIGNED,
  nombre varchar(50),
  descripcion varchar(255),
  orden int(2),
  encuestas_id int(11)
)
```

**BD Nueva:**
```sql
CREATE TABLE dimension (
  id int,
  encuesta_id int,
  nombre varchar(100),
  descripcion text,
  orden int
)
```

**Ajustes:**
- ✅ `encuestas_id` → `encuesta_id`
- ✅ Mapeo directo de campos

---

#### 6. **preguntas → pregunta**

**BD Antigua:**
```sql
CREATE TABLE preguntas (
  id int(11) UNSIGNED,
  encuestas_id int(11) UNSIGNED,
  pregunta varchar(170),
  dimensiones_id int(11) UNSIGNED,
  subdimensiones_id int(11),
  tipos_id int(11),           -- Tipo de pregunta
  tamanio int(11),
  orden int(3),
  presentacion varchar(10),
  orientacion varchar(10),
  padre int(11),
  tips varchar(255),
  instruccion varchar(255)
)
```

**BD Nueva:**
```sql
CREATE TABLE pregunta (
  id int,
  encuesta_id int,
  dimension_id int,
  tipo_pregunta_id int,
  texto_pregunta text,
  orden int,
  requerida boolean,
  etiqueta varchar(100)
)
```

**Ajustes:**
- ✅ `encuestas_id` → `encuesta_id`
- ✅ `pregunta` → `texto_pregunta`
- ✅ `dimensiones_id` → `dimension_id`
- ⚠️ `tipos_id` → `tipo_pregunta_id` (mapear tipos)
- ✅ `orden` → `orden`
- ❌ `subdimensiones_id, tamanio, presentacion, orientacion, padre, tips, instruccion` → No se migran (o guardar en `etiqueta`)

---

#### 7. **opciones → opcion**

**BD Antigua:**
```sql
CREATE TABLE opciones (
  id int(11) UNSIGNED,
  preguntas_id int(11) UNSIGNED,
  valor int(11) UNSIGNED,
  orden int(11) UNSIGNED,
  opcion varchar(50)
)
```

**BD Nueva:**
```sql
CREATE TABLE opcion (
  id int,
  pregunta_id int,
  texto varchar,
  valor int,
  orden int
)
```

**Ajustes:**
- ✅ `preguntas_id` → `pregunta_id`
- ✅ `opcion` → `texto`
- ✅ `valor` → `valor`
- ✅ `orden` → `orden`

---

#### 8. **intrespuestas + txtrespuestas → respuesta**

**BD Antigua (2 tablas):**
```sql
-- Respuestas con opción
CREATE TABLE intrespuestas (
  id int(11) UNSIGNED,
  bitencuestas_id int(11) UNSIGNED,
  respuesta varchar(50),          -- ID de opción
  preguntas_id int(11)
)

-- Respuestas de texto
CREATE TABLE txtrespuestas (
  id int(11) UNSIGNED,
  bitencuestas_id int(11) UNSIGNED,
  respuesta text,                 -- Texto libre
  preguntas_id int(11)
)
```

**BD Nueva (1 tabla):**
```sql
CREATE TABLE respuesta (
  id int,
  egresado_id int,
  pregunta_id int,
  opcion_id int,        -- Puede ser NULL
  texto_respuesta text  -- Puede ser NULL
)
```

**Ajustes:**
- ⚠️ **UNIFICAR** ambas tablas en una sola
- ⚠️ `bitencuestas_id` → Buscar `egresado_id` desde `bitencuestas`
- ✅ `preguntas_id` → `pregunta_id`
- ⚠️ `respuesta` (int) → `opcion_id`
- ⚠️ `respuesta` (text) → `texto_respuesta`

---

#### 9. **laborales → laboral**

**BD Antigua:**
```sql
CREATE TABLE laborales (
  id int(11),
  egresados_id int(11),
  empresa varchar(60),
  puesto varchar(60),
  anioinicio int(4),
  aniofin int(4)
)
```

**BD Nueva:**
```sql
CREATE TABLE laboral (
  id int,
  egresado_id int,
  empresa varchar,
  puesto varchar,
  sector varchar,
  fecha_inicio date,
  fecha_fin date,
  actualmente_laborando boolean
)
```

**Ajustes:**
- ✅ `egresados_id` → `egresado_id`
- ✅ `empresa` → `empresa`
- ✅ `puesto` → `puesto`
- ⚠️ `anioinicio` → `fecha_inicio` (convertir a fecha: 01/01/año)
- ⚠️ `aniofin` → `fecha_fin` (convertir a fecha: 31/12/año)
- ⚠️ Si `aniofin` = 0 o NULL → `actualmente_laborando` = true
- ❌ `sector` → No hay dato (asignar NULL)

---

### ❌ TABLAS NO COMPATIBLES (NO SE MIGRAN)

| Tabla Antigua | Motivo |
|---------------|--------|
| `academicos` | Sin equivalente, datos redundantes con `egresados` |
| `accesos` | Bitácora antigua, usar nueva tabla `bitacora` |
| `asignadas` | Se reemplaza por `encuesta_asignada` con estructura diferente |
| `bitacoras` | Bitácora antigua, no compatible |
| `bitegresados` | Bitácora antigua de egresados |
| `bitencuestas` | Se reemplaza por relación directa en `respuesta` |
| `columnas` | Sin equivalente, funcionalidad no implementada |
| `empresas` | Sin equivalente, datos de empresas externas |
| `escucarreras` | Se reemplaza por `unidad_carrera` |
| `menus` | Sin equivalente, no se usa en nuevo sistema |
| `niveles` (usuarios) | Sistema de roles diferente (usa Spatie) |
| `servicios` | Se unifica en `laboral` |
| `subdimensiones` | Sin equivalente directo |
| `usuarios` | Sistema de autenticación diferente (usa Laravel Fortify) |

---

## 🔧 RECOMENDACIONES DE MIGRACIÓN

### 1. **Orden de Migración** (Respetar dependencias)

```
1. Catálogos base
   - cat_genero
   - cat_estado_civil
   - cat_estatus
   
2. Estructuras académicas
   - ciclo (desde ciclos)
   - generacion (desde generaciones)
   - unidad (desde escuelas)
   - carrera (desde carreras)
   - unidad_carrera (desde escucarreras)
   - tipo_pregunta (desde tipos)
   
3. Egresados y relaciones
   - egresado (desde egresados)
   - egresado_carrera (desde egresados + academicos)
   - laboral (desde laborales + servicios)
   
4. Encuestas y estructura
   - encuesta (desde encuestas)
   - dimension (desde dimensiones)
   - pregunta (desde preguntas)
   - opcion (desde opciones)
   
5. Respuestas
   - respuesta (desde intrespuestas + txtrespuestas)
```

### 2. **Datos a Crear Manualmente**

Antes de migrar, crear catálogos base:

```sql
-- cat_genero
INSERT INTO cat_genero VALUES (1, 'Hombre', 'A');
INSERT INTO cat_genero VALUES (2, 'Mujer', 'A');
INSERT INTO cat_genero VALUES (3, 'Otro', 'A');

-- cat_estado_civil
INSERT INTO cat_estado_civil VALUES (1, 'Soltero/a', 'A');
INSERT INTO cat_estado_civil VALUES (2, 'Casado/a', 'A');
INSERT INTO cat_estado_civil VALUES (3, 'Divorciado/a', 'A');
INSERT INTO cat_estado_civil VALUES (4, 'Viudo/a', 'A');
INSERT INTO cat_estado_civil VALUES (5, 'Unión libre', 'A');

-- cat_estatus
INSERT INTO cat_estatus VALUES (1, 'Activo', 'A');
INSERT INTO cat_estatus VALUES (2, 'Inactivo', 'I');
```

### 3. **Script de Migración**

Ya tienes el comando `MigrarDatosAntiguos`. Debes actualizarlo con estos mapeos.

### 4. **Validaciones POST-Migración**

```sql
-- Verificar conteos
SELECT COUNT(*) FROM egresado;
SELECT COUNT(*) FROM carrera;
SELECT COUNT(*) FROM encuesta;
SELECT COUNT(*) FROM respuesta;

-- Verificar integridad referencial
SELECT * FROM egresado WHERE genero_id NOT IN (SELECT id FROM cat_genero);
SELECT * FROM egresado WHERE estado_civil_id NOT IN (SELECT id FROM cat_estado_civil);
```

---

## ⚠️ RIESGOS Y PÉRDIDAS DE DATOS

### Datos que SE PERDERÁN en la migración:

1. **Contraseñas de egresados** (`egresados.clave`)
   - Solución: Enviar email con token de restablecimiento

2. **Datos de contacto de escuelas** (`escuelas.domicilio, web, email`)
   - Solución: Guardar en archivo separado si se necesitan

3. **Nombres cortos** (`nomcto` de varias tablas)
   - Solución: Usar como `clave` donde aplique

4. **Bitácoras antiguas** (todas las tablas `bit*`)
   - Solución: Exportar a CSV para historial

5. **Subdimensiones de preguntas**
   - Solución: Simplificar a dimensiones únicas

6. **Sistema de usuarios/administradores antiguo**
   - Solución: Crear nuevos usuarios con Spatie Permissions

---

## ✅ CONCLUSIÓN

**La migración ES VIABLE** con los siguientes requisitos:

1. ✅ Actualizar comando `MigrarDatosAntiguos` con mapeos correctos
2. ✅ Crear catálogos base antes de migrar
3. ✅ Ejecutar en modo `--dry-run` primero
4. ✅ Validar datos migrados
5. ⚠️ Notificar a egresados para restablecer contraseñas
6. ⚠️ Revisar manualmente casos especiales (ej: CURP faltantes)

**Estimación:** 80-85% de datos útiles se migrarán correctamente.
