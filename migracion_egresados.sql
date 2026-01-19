-- Script de migración de egresados desde bdwvexa a egresados_db

-- 1. Crear tabla temporal de mapeo de carreras
CREATE TEMPORARY TABLE carrera_mapeo AS
SELECT 
  vc.id as carrera_vieja,
  vc.nombre as carrera_nombre_vieja,
  nc.id as carrera_nueva
FROM bdwvexa.carreras vc
LEFT JOIN egresados_db.carrera nc 
  ON UPPER(nc.nombre) LIKE CONCAT('%', UPPER(SUBSTRING(vc.nombre, 1, 20)), '%')
WHERE nc.deleted_at IS NULL;

-- 2. Crear tabla temporal de mapeo de escuelas/unidades
CREATE TEMPORARY TABLE unidad_mapeo AS
SELECT 
  ve.id as escuela_vieja,
  ve.nombre as escuela_nombre_vieja,
  nu.id as unidad_nueva
FROM bdwvexa.escuelas ve
LEFT JOIN egresados_db.unidad nu 
  ON UPPER(nu.nombre) LIKE CONCAT('%', UPPER(SUBSTRING(ve.nombre, 1, 20)), '%')
WHERE nu.deleted_at IS NULL;

-- 3. Crear tabla temporal de mapeo de estatus
CREATE TEMPORARY TABLE estatus_mapeo AS
SELECT 
  'I' as estatus_viejo,
  'Inactivo' as estatus_nombre,
  (SELECT id FROM egresados_db.cat_estatus WHERE nombre = 'Inactivo' LIMIT 1) as estatus_id
UNION ALL
SELECT 'A', 'Activo', (SELECT id FROM egresados_db.cat_estatus WHERE nombre = 'Activo' LIMIT 1)
UNION ALL
SELECT 'E', 'Egresado', (SELECT id FROM egresados_db.cat_estatus WHERE nombre = 'Egresado' LIMIT 1);

-- 4. Migrar egresados (evitando duplicados)
INSERT INTO egresados_db.egresado 
  (matricula, nombre, apellidos, genero_id, fecha_nacimiento, 
   lugar_nacimiento, email, estado_civil_id, estatus_id, 
   unidad_id, carrera_id, fecha_ingreso, ultimo_ingreso, activo, token, created_at)
SELECT 
  e.matricula,
  e.nombre,
  e.apellidos,
  CASE e.genero 
    WHEN 'M' THEN 1
    WHEN 'F' THEN 2
    ELSE NULL
  END as genero_id,
  NULLIF(CASE WHEN e.fecnac = '0000-00-00' OR e.fecnac IS NULL THEN NULL ELSE e.fecnac END, '0000-00-00') as fecha_nacimiento,
  e.lugarnac,
  e.email,
  CASE e.edocivil
    WHEN 'S' THEN 1  -- Soltero
    WHEN 'C' THEN 2  -- Casado
    WHEN 'V' THEN 3  -- Viudo
    WHEN 'D' THEN 4  -- Divorciado
    ELSE NULL
  END as estado_civil_id,
  COALESCE(em.estatus_id, (SELECT id FROM egresados_db.cat_estatus WHERE nombre = 'Activo' LIMIT 1)) as estatus_id,
  COALESCE(um.unidad_nueva, 1) as unidad_id,
  COALESCE(cm.carrera_nueva, 1) as carrera_id,
  e.fechaingreso,
  e.ultimoingreso,
  e.activo,
  COALESCE(e.token, MD5(CONCAT(e.id, NOW()))) as token,
  NOW() as created_at
FROM bdwvexa.egresados e
LEFT JOIN carrera_mapeo cm ON e.carreras_id = cm.carrera_vieja
LEFT JOIN unidad_mapeo um ON e.escuelas_id = um.escuela_vieja
LEFT JOIN estatus_mapeo em ON e.estatus = em.estatus_viejo
WHERE NOT EXISTS (
  SELECT 1 FROM egresados_db.egresado eg 
  WHERE (eg.matricula = e.matricula AND e.matricula IS NOT NULL)
    OR (eg.email = e.email)
);

-- 5. Mostrar resultados
SELECT CONCAT('Total egresados insertados: ', ROW_COUNT()) as resultado;
SELECT COUNT(*) as total_egresados FROM egresados_db.egresado WHERE deleted_at IS NULL;
