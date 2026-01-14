# INSTRUCCIONES FINALES - Migración de Base de Datos

## Estado Actual ✅

Se han completado las siguientes tareas:
- ✅ 9 tablas nuevas creadas (academico, bitacora_egresado, bitacora_encuesta, respuesta_int, respuesta_txt, subdimension, columna_encuesta, cat_dirigida, empresa)
- ✅ Catálogo de dirigidas insertado
- ✅ Archivo SQL copiado a: `/home/jorte/proyectos/Egresados/bdwvexa_backup.sql`
- ⚠️  La importación encontró un error con la tabla `ciclo`

## Problema Detectado

El script de importación intentó insertar en la tabla `ciclo` pero hay un conflicto de nombres o la base de datos no es la correcta.

## Pasos a Seguir (EJECUTAR MANUALMENTE)

### 1. Verificar la Base de Datos

Abre WSL y ejecuta:

```bash
cd /home/jorte/proyectos/Egresados

# Verificar el nombre de la base de datos en .env
grep DB_DATABASE .env

# Conectar a la base de datos
docker-compose exec -T db mysql -u root -proot -e "SHOW DATABASES;"
```

### 2. Verificar Tablas Existentes

```bash
# Ver qué tablas existen
docker-compose exec db mysql -u root -proot egresados_db -e "SHOW TABLES;"

# O si la base de datos se llama 'egresados':
docker-compose exec db mysql -u root -proot egresados -e "SHOW TABLES;"
```

### 3. Corregir Nombre de Base de Datos

Si la base de datos se llama `egresados` en lugar de `egresados_db`, actualiza el archivo `.env`:

```bash
# Editar .env
nano .env

# Cambiar:
DB_DATABASE=egresados_db
# Por:
DB_DATABASE=egresados
```

### 4. Modificar Script de Importación

El script `importar_bd_antigua.php` necesita ser modificado para usar las tablas con timestamps de Laravel. Abre el archivo y realiza los siguientes cambios:

```bash
nano importar_bd_antigua.php
```

En las líneas donde se insertan los catálogos, asegúrate de usar los nombres correctos de columnas:
- Usar `created_at` y `updated_at` en lugar de `creado_en` y `actualizado_en`

O más fácil, ejecuta este comando para actualizar automáticamente:

```bash
sed -i 's/creado_en/created_at/g' importar_bd_antigua.php
sed -i 's/actualizado_en/updated_at/g' importar_bd_antigua.php
```

### 5. Re-ejecutar la Importación

```bash
cd /home/jorte/proyectos/Egresados

# Ejecutar dentro del contenedor PHP
docker-compose exec php php importar_bd_antigua.php bdwvexa_backup.sql
```

## ALTERNATIVA MÁS SIMPLE: Importar SQL Directo

Si el script PHP sigue teniendo problemas, puedes importar directamente el SQL:

### Opción A: Importar todo el dump

```bash
# Importar todo el archivo SQL directamente a MySQL
docker-compose exec -T db mysql -u root -proot egresados_db < bdwvexa_backup.sql
```

⚠️ **ADVERTENCIA**: Esto sobreescribirá TODA la base de datos actual.

### Opción B: Usar un script SQL personalizado

Crear un script que extraiga solo los datos específicos:

```bash
# Ver las primeras líneas del archivo para entender la estructura
head -n 100 bdwvexa_backup.sql
```

## Verificación Post-Migración

Después de que la importación finalice exitosamente:

```bash
# Conectarse a la base de datos
docker-compose exec db mysql -u root -proot egresados_db

# Ejecutar en MySQL:
USE egresados_db;

-- Verificar conteo de registros
SELECT COUNT(*) as egresados FROM egresado;
SELECT COUNT(*) as respuestas_int FROM respuesta_int;
SELECT COUNT(*) as respuestas_txt FROM respuesta_txt;
SELECT COUNT(*) as ciclos FROM ciclo;
SELECT COUNT(*) as carreras FROM carrera;
SELECT COUNT(*) as encuestas FROM encuesta;

-- Salir
EXIT;
```

## Números Esperados

| Tabla | Cantidad Esperada |
|-------|-------------------|
| egresado | ~8,400 |
| carrera | ~50 |
| unidad | ~27 |
| generacion | ~34 |
| ciclo | ~15 |
| encuesta | ~28 |
| respuesta_int | ~137,000 |
| respuesta_txt | ~92,000 |

## Si Todo Falla

Opción de último recurso - migrar manualmente tabla por tabla:

```bash
# Extraer los INSERT de una tabla específica del dump
grep "INSERT INTO \`egresados\`" bdwvexa_backup.sql > egresados_inserts.sql

# Importar solo esa tabla
docker-compose exec -T db mysql -u root -proot egresados_db < egresados_inserts.sql
```

## Soporte

Si encuentras errores:
1. Revisa los logs: `storage/logs/laravel.log`
2. Verifica conexión a DB: `docker-compose ps`
3. Revisa configuración: `cat .env | grep DB_`

---

**Siguiente Paso**: Una vez que la importación funcione, documenta qué solución funcionó para referencia futura.
