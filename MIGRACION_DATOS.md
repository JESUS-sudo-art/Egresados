# 🔄 Migración de Datos - Base de Datos Antigua a Nueva

Este documento explica cómo migrar los datos de la base de datos antigua (`bdwvexa`) a la nueva estructura de Laravel.

## 📋 Pre-requisitos

1. **Tener ambas bases de datos disponibles:**
   - Base de datos nueva (Laravel) - funcionando actual
   - Base de datos antigua (`bdwvexa`) - accesible

2. **Configurar la conexión a la BD antigua** en `.env`:

```env
# Configuración de la BD antigua para migración
DB_OLD_HOST=127.0.0.1
DB_OLD_PORT=3306
DB_OLD_DATABASE=bdwvexa
DB_OLD_USERNAME=root
DB_OLD_PASSWORD=tu_password_aqui
```

## 🚀 Cómo Ejecutar la Migración

### **Paso 1: Probar sin hacer cambios (DRY-RUN)**
```bash
php artisan migrar:datos-antiguos --dry-run
```

Esto simula la migración y muestra cuántos registros se migrarían sin guardar nada.

### **Paso 2: Migrar todo**
```bash
php artisan migrar:datos-antiguos
```

Migra todas las tablas en el orden correcto.

### **Paso 3: Migrar solo una tabla específica**
```bash
# Ver tablas disponibles
php artisan migrar:datos-antiguos --tabla=catalogos
php artisan migrar:datos-antiguos --tabla=generaciones
php artisan migrar:datos-antiguos --tabla=ciclos
php artisan migrar:datos-antiguos --tabla=unidades
php artisan migrar:datos-antiguos --tabla=carreras
php artisan migrar:datos-antiguos --tabla=egresados
php artisan migrar:datos-antiguos --tabla=encuestas
php artisan migrar:datos-antiguos --tabla=dimensiones
php artisan migrar:datos-antiguos --tabla=preguntas
php artisan migrar:datos-antiguos --tabla=opciones
php artisan migrar:datos-antiguos --tabla=respuestas
php artisan migrar:datos-antiguos --tabla=laborales
```

### **Paso 4: Limpiar y migrar desde cero**
```bash
php artisan migrar:datos-antiguos --limpiar
```

⚠️ **CUIDADO:** Esto eliminará todos los datos existentes antes de migrar.

## 📊 Orden de Migración

El script migra en este orden (respetando dependencias):

1. ✅ **Catálogos básicos** - Géneros, Estados Civiles, Estatus, Tipos de Pregunta
2. ✅ **Generaciones** - Generaciones de egresados
3. ✅ **Ciclos** - Ciclos escolares
4. ✅ **Unidades** - Escuelas → Unidades
5. ✅ **Carreras** - Carreras y relación Unidad-Carrera
6. ✅ **Egresados** - Egresados + Usuarios + Roles
7. ✅ **Encuestas** - Encuestas y configuración
8. ✅ **Dimensiones** - Dimensiones de encuestas
9. ✅ **Preguntas** - Preguntas de encuestas
10. ✅ **Opciones** - Opciones de respuesta
11. ✅ **Respuestas** - Unifica `intrespuestas` y `txtrespuestas`
12. ✅ **Datos Laborales** - Historial laboral

## 🔄 Mapeos y Transformaciones

### **Tabla `egresados` → `egresado`**

| Campo Antiguo | Campo Nuevo | Transformación |
|--------------|-------------|----------------|
| `genero` (char) | `genero_id` (int) | M→1, F→2, Otro→3 |
| `edocivil` (char) | `estado_civil_id` (int) | S→1, C→2, D→3, V→4, U→5 |
| `estatus` (char) | `estatus_id` (int) | A→1, I→2 |
| `activo` (char) | `validado_sice` (bool) | A→true, I→false |
| `escuelas_id` | [eliminado] | Ahora en `egresado_carrera` |
| `carreras_id` | [eliminado] | Ahora en `egresado_carrera` |
| `clave` | `token` | Renombrado |

**Además se crea:**
- Usuario en tabla `users` con email del egresado
- Rol asignado: `Egresados` si validado, `Estudiantes` si no
- Registro en `egresado_carrera` con la carrera y generación

### **Tabla `escuelas` → `unidad`**

| Campo Antiguo | Campo Nuevo |
|--------------|-------------|
| `nomcto` | `nombre_corto` |
| `web` | `sitio_web` |

### **Tabla `escucarreras` → `unidad_carrera`**

Se migra la relación muchos a muchos entre unidades y carreras.

### **Tabla `encuestas` → `encuesta`**

| Campo Antiguo | Campo Nuevo | Transformación |
|--------------|-------------|----------------|
| `ciclos_id` | `ciclo_id` | Directo |
| `dirigidas_id` | `tipo_cuestionario` | Se busca descripción en tabla `dirigidas` |
| `fecini` | `fecha_inicio` | Renombrado |
| `fecfin` | `fecha_fin` | Renombrado |

### **Tabla `txtrespuestas` + `intrespuestas` → `respuesta`**

Se unifican dos tablas en una sola:

- **intrespuestas**: Respuestas de opción múltiple
  - Se busca la `opcion_id` correspondiente al valor
  - Campo `texto` = NULL
  
- **txtrespuestas**: Respuestas de texto libre
  - Campo `opcion_id` = NULL
  - Campo `texto` = respuesta del usuario

### **Tabla `laborales` → `laboral`**

| Campo Antiguo | Campo Nuevo | Transformación |
|--------------|-------------|----------------|
| `anioinicio` | `fecha_inicio` | Año → Fecha completa (YYYY-01-01) |
| `aniofin` | `fecha_fin` | Si aniofin=0 → NULL (trabaja actualmente) |
| - | `actualmente_trabaja` | aniofin=0 → true, else → false |

## ⚠️ Consideraciones Importantes

### **1. Respuestas (Más Complejo)**

El mapeo de respuestas es el más complejo porque:
- `bitencuestas_id` en la BD antigua NO es directamente `egresado_id`
- Necesitas validar la relación a través de `bitencuestas` → `egresados_id`
- **Ajuste necesario:** Si tienes muchas respuestas, este mapeo debe refinarse

### **2. Subdimensiones**

La BD antigua tiene `subdimensiones` pero la nueva NO la implementa aún.
- Se ignoran en la migración actual
- Si las necesitas, debes crear la migración y modelo primero

### **3. Columnas**

La tabla `columnas` no tiene equivalente directo. Se usa para reportes dinámicos.
- No se migra actualmente
- Evaluar si es necesaria

### **4. Usuarios**

Se crean automáticamente usuarios de Laravel para cada egresado:
- Email del egresado = email del usuario
- Password default: el campo `clave` de la BD antigua (hasheado)
- Si `clave` está vacío, usa "password" por defecto

## 📈 Ejemplo de Ejecución

```bash
# 1. Probar primero
php artisan migrar:datos-antiguos --dry-run

# Salida esperada:
# 🚀 MIGRACIÓN DE DATOS DE BASE DE DATOS ANTIGUA
# ==============================================
# ✅ Conexión exitosa a la base de datos antigua: bdwvexa
#
# 📋 Iniciando migración completa...
# ▶️  1. Catálogos básicos
#    ✓ Catálogos migrados
# ▶️  2. Generaciones
#    ✓ 15 generaciones migradas
# ▶️  3. Ciclos
#    ✓ 8 ciclos migrados
# ...
# 📊 RESUMEN DE MIGRACIÓN
# =======================
# +----------------------+---------------------+
# | Tabla                | Registros Migrados  |
# +----------------------+---------------------+
# | Generaciones         | 15                  |
# | Ciclos               | 8                   |
# | Unidades             | 12                  |
# | Carreras             | 45                  |
# | Egresados            | 1,250               |
# | Encuestas            | 23                  |
# | Preguntas            | 450                 |
# | Respuestas           | 18,750              |
# +----------------------+---------------------+

# 2. Si todo se ve bien, ejecutar
php artisan migrar:datos-antiguos
```

## 🔍 Verificación Post-Migración

```bash
# Verificar conteos
php artisan tinker

# Contar registros migrados
>>> \App\Models\Egresado::count()
>>> \App\Models\Carrera::count()
>>> \App\Models\Encuesta::count()
>>> \App\Models\Pregunta::count()
>>> \App\Models\Respuesta::count()

# Verificar usuarios creados
>>> \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'Egresados'))->count()

# Verificar un egresado específico
>>> $egresado = \App\Models\Egresado::first()
>>> $egresado->user // Ver usuario asociado
>>> $egresado->carreras // Ver carreras
>>> $egresado->empleos // Ver historial laboral
```

## 🐛 Solución de Problemas

### **Error: No se puede conectar a bdwvexa**
- Verifica que las credenciales en `.env` sean correctas
- Asegúrate de que la BD antigua esté accesible
- Prueba la conexión con un cliente MySQL

### **Error: Violación de clave foránea**
- Ejecuta en orden: `catalogos` → `generaciones` → `ciclos` → etc.
- No uses `--limpiar` parcialmente, hazlo completo

### **Respuestas no se migran correctamente**
- Este es el mapeo más complejo
- Revisa la tabla `bitencuestas` en la BD antigua
- Puede necesitar ajuste manual según tu estructura específica

## 📝 Notas Finales

- **Backup primero:** Haz respaldo de ambas bases de datos antes de migrar
- **Prueba en desarrollo:** Ejecuta la migración en un ambiente de prueba primero
- **Tiempo estimado:** Dependiendo del volumen, puede tardar de 5 minutos a 2 horas
- **Memoria:** Si tienes muchos registros (>100k respuestas), considera migrar por lotes

---

✅ **Script creado y listo para usar**
