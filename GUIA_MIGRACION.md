# Guía de Migración - Base de Datos Antigua

## 📋 Resumen

Este documento describe el proceso completo para migrar la base de datos antigua del Sistema de Egresados de la UABJO a la nueva estructura Laravel.

## 🎯 Objetivo

Migrar ~8,400 egresados, ~230,000 respuestas de encuestas, y todos los catálogos del sistema antiguo al nuevo sistema.

## 📁 Archivos Creados

### Migraciones (database/migrations/)
1. `2025_12_08_000001_create_academico_table.php` - Relación académica de egresados
2. `2025_12_08_000002_create_bitacora_egresado_table.php` - Sesiones de egresados
3. `2025_12_08_000003_create_bitacora_encuesta_table.php` - Encuestas contestadas
4. `2025_12_08_000004_create_respuesta_int_table.php` - Respuestas numéricas
5. `2025_12_08_000005_create_respuesta_txt_table.php` - Respuestas de texto
6. `2025_12_08_000006_create_subdimension_table.php` - Subdimensiones
7. `2025_12_08_000007_create_columna_encuesta_table.php` - Columnas de matriz
8. `2025_12_08_000008_create_cat_dirigida_table.php` - Tipo de público objetivo
9. `2025_12_08_000009_create_empresa_table.php` - Catálogo de empresas
10. `2025_12_08_000010_add_foreign_keys_to_academico_table.php` - FK académico
11. `2025_12_08_000011_add_foreign_keys_to_new_tables.php` - FK tablas nuevas
12. `2025_12_08_000012_add_campos_antiguos_to_egresado.php` - Campos adicionales egresado
13. `2025_12_08_000013_add_campos_antiguos_to_encuesta.php` - Campos adicionales encuesta
14. `2025_12_08_000014_add_campos_antiguos_to_pregunta.php` - Campos adicionales pregunta
15. `2025_12_08_000015_add_foreign_keys_to_extended_tables.php` - FK extendidas

### Modelos (app/Models/)
1. `Academico.php` - Modelo para relación académica
2. `BitacoraEgresado.php` - Modelo para sesiones
3. `BitacoraEncuesta.php` - Modelo para encuestas contestadas
4. `RespuestaInt.php` - Modelo para respuestas numéricas
5. `RespuestaTxt.php` - Modelo para respuestas texto
6. `Subdimension.php` - Modelo para subdimensiones
7. `ColumnaEncuesta.php` - Modelo para columnas
8. `CatDirigida.php` - Modelo para dirigida
9. `Empresa.php` - Modelo para empresas

### Seeders (database/seeders/)
1. `CatDirigidaSeeder.php` - Catálogo de tipos de dirigida

### Scripts
1. `importar_bd_antigua.php` - Script principal de importación
2. `PLAN_MIGRACION_BD_ANTIGUA.md` - Documento de análisis
3. `GUIA_MIGRACION.md` - Este documento

## 🚀 Proceso de Migración

### Paso 1: Preparar el Entorno

```bash
# 1. Asegurarse de estar en el directorio del proyecto
cd /home/jorte/proyectos/Egresados

# 2. Hacer backup de la base de datos actual
php artisan db:backup
```

### Paso 2: Ejecutar las Migraciones

```bash
# 1. Ejecutar las nuevas migraciones
php artisan migrate

# 2. Verificar que no haya errores
php artisan migrate:status
```

### Paso 3: Ejecutar el Seeder de Catálogos

```bash
# Insertar catálogo de dirigidas
php artisan db:seed --class=CatDirigidaSeeder
```

### Paso 4: Copiar el Archivo SQL

```bash
# Copiar el archivo desde Windows a WSL
# En PowerShell (Windows):
# cp "C:\Users\jorte\Downloads\bdwvexa_backup_260825 (1).sql" \\wsl.localhost\Ubuntu\home\jorte\proyectos\Egresados\

# O en WSL:
cp /mnt/c/Users/jorte/Downloads/bdwvexa_backup_260825\ \(1\).sql ~/proyectos/Egresados/bdwvexa_backup.sql
```

### Paso 5: Ejecutar la Importación

⚠️ **ADVERTENCIA**: Este proceso puede tardar entre 1-3 horas dependiendo del hardware.

```bash
# Ejecutar el script de importación
php importar_bd_antigua.php bdwvexa_backup.sql
```

El script mostrará el progreso en tiempo real:

```
=== IMPORTADOR DE BASE DE DATOS ANTIGUA ===
Archivo: bdwvexa_backup.sql
===========================================

=== FASE 1: CATÁLOGOS ===
Insertando catálogo de dirigidas...
  ✓ 6 dirigidas insertadas
Migrando generaciones...
  ✓ 34 generaciones migradas
Migrando ciclos...
  ✓ 15 ciclos migrados
...
```

### Paso 6: Validar la Migración

```bash
# Verificar conteo de registros
php artisan tinker

# En tinker:
>>> DB::table('egresado')->count()
>>> DB::table('respuesta_int')->count()
>>> DB::table('respuesta_txt')->count()
>>> DB::table('bitacora_encuesta')->count()
```

## 📊 Datos Esperados

| Tabla | Registros Esperados |
|-------|---------------------|
| egresado | ~8,400 |
| academico | ~8,400 |
| generacion | 34 |
| ciclo | 15 |
| unidad | 27 |
| carrera | 50 |
| encuesta | 28 |
| dimension | 80 |
| subdimension | 17 |
| pregunta | 572 |
| opcion | 1,586 |
| bitacora_encuesta | ~8,000 |
| respuesta_int | ~137,000 |
| respuesta_txt | ~92,000 |

## 🔍 Verificaciones Post-Migración

### 1. Verificar Integridad Referencial

```sql
-- Verificar que todos los egresados tienen relación académica
SELECT COUNT(*) FROM egresado e 
LEFT JOIN academico a ON e.id = a.egresado_id 
WHERE a.id IS NULL;
-- Debe retornar 0

-- Verificar que todas las respuestas tienen bitácora
SELECT COUNT(*) FROM respuesta_int r 
LEFT JOIN bitacora_encuesta b ON r.bitacora_encuesta_id = b.id 
WHERE b.id IS NULL;
-- Debe retornar 0
```

### 2. Verificar Datos de Ejemplo

```sql
-- Ver un egresado completo
SELECT e.*, a.* 
FROM egresado e 
JOIN academico a ON e.id = a.egresado_id 
LIMIT 1;

-- Ver una encuesta con sus dimensiones
SELECT enc.nombre, d.nombre as dimension, COUNT(p.id) as preguntas
FROM encuesta enc
JOIN dimension d ON d.encuesta_id = enc.id
JOIN pregunta p ON p.dimension_id = d.id
GROUP BY enc.id, d.id;
```

### 3. Verificar Respuestas de Encuestas

```sql
-- Ver respuestas de un egresado
SELECT 
    e.nombre,
    enc.nombre as encuesta,
    p.texto as pregunta,
    COALESCE(ri.respuesta, rt.respuesta) as respuesta
FROM egresado e
JOIN bitacora_encuesta be ON be.egresado_id = e.id
JOIN encuesta enc ON enc.id = be.encuesta_id
JOIN pregunta p ON p.encuesta_id = enc.id
LEFT JOIN respuesta_int ri ON ri.bitacora_encuesta_id = be.id AND ri.pregunta_id = p.id
LEFT JOIN respuesta_txt rt ON rt.bitacora_encuesta_id = be.id AND rt.pregunta_id = p.id
WHERE e.id = 1
LIMIT 10;
```

## 🔧 Solución de Problemas

### Error: "SQLSTATE[23000]: Integrity constraint violation"

**Causa**: Violación de integridad referencial.

**Solución**: 
```bash
# Desactivar temporalmente las foreign keys
php artisan tinker
>>> DB::statement('SET FOREIGN_KEY_CHECKS=0;');
# Ejecutar importación
>>> DB::statement('SET FOREIGN_KEY_CHECKS=1;');
```

### Error: "Memory limit exceeded"

**Causa**: El script requiere mucha memoria para procesar las respuestas.

**Solución**: Aumentar el límite de memoria en `php.ini`:
```ini
memory_limit = 512M
```

O ejecutar con:
```bash
php -d memory_limit=512M importar_bd_antigua.php bdwvexa_backup.sql
```

### El script se detiene sin mensaje de error

**Causa**: Timeout de ejecución.

**Solución**: Aumentar el tiempo máximo en `php.ini`:
```ini
max_execution_time = 0
```

O ejecutar con:
```bash
php -d max_execution_time=0 importar_bd_antigua.php bdwvexa_backup.sql
```

### Proceso muy lento

**Solución**: Desactivar índices temporalmente:

```sql
-- Antes de la importación
ALTER TABLE respuesta_int DISABLE KEYS;
ALTER TABLE respuesta_txt DISABLE KEYS;

-- Después de la importación
ALTER TABLE respuesta_int ENABLE KEYS;
ALTER TABLE respuesta_txt ENABLE KEYS;
```

## 📝 Notas Importantes

1. **Backup Obligatorio**: SIEMPRE hacer backup antes de ejecutar la migración
2. **Tiempo Estimado**: 1-3 horas dependiendo del hardware
3. **Espacio en Disco**: Requerido ~2GB libres
4. **Memoria RAM**: Recomendado 4GB+ disponibles
5. **Contraseñas**: Los usuarios migrados tendrán contraseña temporal "password"
6. **Validación**: Verificar datos después de cada fase

## 🎓 Siguiente Paso

Después de la migración exitosa:

1. Actualizar modelos Eloquent existentes para agregar las nuevas relaciones
2. Crear seeders para roles y permisos según usuarios antiguos
3. Implementar autenticación para egresados
4. Crear vistas para visualizar encuestas antiguas
5. Migrar archivos adjuntos (logos, documentos) si existen

## 📞 Soporte

Si encuentras problemas durante la migración:

1. Revisar logs: `storage/logs/laravel.log`
2. Verificar configuración de base de datos en `.env`
3. Consultar la documentación de Laravel
4. Revisar el archivo `PLAN_MIGRACION_BD_ANTIGUA.md` para detalles técnicos
