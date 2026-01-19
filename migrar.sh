#!/bin/bash

# Script bash para migración de egresados

echo "=== Iniciando migración de egresados ==="
echo ""

# 1. Obtener datos de egresados antiguos
echo "1. Exportando egresados de BD antigua..."
docker exec egresados-db mysql -u root -proot bdwvexa -e \
"SELECT e.id, e.matricula, e.nombre, e.apellidos, e.email, e.genero, e.fecnac, 
        e.lugarnac, e.edocivil, e.estatus, e.carreras_id, e.escuelas_id, 
        e.fechaingreso, e.ultimoingreso, e.token, e.activo
 FROM egresados e 
 LIMIT 10" | head -20

echo ""
echo "2. Creando script de inserción..."

# Crear script SQL con manejo de fechas
cat > /tmp/migrate_egresados.sql << 'EOF'
-- Migración de egresados con manejo robusto de datos
USE egresados_db;

-- Deshabilitar checks temporalmente
SET FOREIGN_KEY_CHECKS=0;

-- Crear función para convertir fecha válida
DELIMITER //
CREATE FUNCTION IF NOT EXISTS ValidateDate(dateStr VARCHAR(10)) 
RETURNS DATE DETERMINISTIC
READS SQL DATA
BEGIN
  IF dateStr IS NULL OR dateStr = '' OR dateStr = '0000-00-00' THEN
    RETURN NULL;
  END IF;
  IF STR_TO_DATE(dateStr, '%Y-%m-%d') IS NOT NULL THEN
    RETURN STR_TO_DATE(dateStr, '%Y-%m-%d');
  END IF;
  RETURN NULL;
END //
DELIMITER ;

-- Tabla temporal para almacenar datos
CREATE TEMPORARY TABLE temp_egresados AS
SELECT 
  e.id,
  e.matricula,
  e.nombre,
  e.apellidos,
  CASE e.genero WHEN 'M' THEN 1 WHEN 'F' THEN 2 ELSE NULL END as genero_id,
  (SELECT id FROM carrera WHERE deleted_at IS NULL LIMIT 1) as carrera_id,
  (SELECT id FROM unidad WHERE deleted_at IS NULL LIMIT 1) as unidad_id,
  CASE e.estatus 
    WHEN 'I' THEN (SELECT id FROM cat_estatus WHERE nombre = 'Inactivo' LIMIT 1)
    WHEN 'A' THEN (SELECT id FROM cat_estatus WHERE nombre = 'Activo' LIMIT 1)
    WHEN 'E' THEN (SELECT id FROM cat_estatus WHERE nombre = 'Egresado' LIMIT 1)
    ELSE 2
  END as estatus_id,
  CASE e.edocivil
    WHEN 'S' THEN 1 WHEN 'C' THEN 2 WHEN 'V' THEN 3 WHEN 'D' THEN 4
    ELSE NULL
  END as estado_civil_id,
  e.lugarnac,
  e.email,
  e.fechaingreso,
  e.ultimoingreso,
  e.activo,
  e.token
FROM bdwvexa.egresados e
WHERE NOT EXISTS (
  SELECT 1 FROM egresado WHERE 
    (matricula = e.matricula AND e.matricula IS NOT NULL)
    OR (email = e.email)
);

-- Insertar datos
INSERT INTO egresado 
  (matricula, nombre, apellidos, genero_id, genero_id, estado_civil_id,
   lugar_nacimiento, email, estatus_id, carrera_id, unidad_id, 
   fecha_ingreso, ultimo_ingreso, activo, token, created_at)
SELECT 
  te.matricula,
  te.nombre,
  te.apellidos,
  te.genero_id,
  te.estado_civil_id,
  te.lugar_nacimiento,
  te.email,
  te.estatus_id,
  te.carrera_id,
  te.unidad_id,
  te.fecha_ingreso,
  te.ultimo_ingreso,
  te.activo,
  COALESCE(te.token, MD5(UUID())),
  NOW()
FROM temp_egresados te;

-- Re-habilitar checks
SET FOREIGN_KEY_CHECKS=1;

SELECT CONCAT('Total egresados migrados: ', FOUND_ROWS()) as resultado;
SELECT COUNT(*) as total_actual FROM egresado WHERE deleted_at IS NULL;
EOF

echo "3. Ejecutando migración en Docker..."
docker exec -i egresados-db mysql -u root -proot < /tmp/migrate_egresados.sql

echo ""
echo "=== Migración completada ==="
